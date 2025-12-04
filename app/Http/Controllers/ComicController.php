<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\MangadexService;

class ComicController extends Controller
{
    protected $mangadexService;

    public function __construct(MangadexService $mangadexService)
    {
        $this->mangadexService = $mangadexService;
        // Izinkan 'index' dan 'showChapters' diakses tanpa login (halaman detail publik)
        $this->middleware('auth')->except(['index', 'showChapters']);
    }

    private function processMangaList(array $data)
    {
        $mangaList = $data['data'] ?? [];
        $included = $data['included'] ?? [];
        $pagination = [
            'total' => $data['total'] ?? 0,
            'limit' => $data['limit'] ?? 12,
            'offset' => $data['offset'] ?? 0,
        ];

        // Log untuk debugging
        Log::info("Processing manga list:", [
            'manga_count' => count($mangaList),
            'included_count' => count($included),
        ]);

        // Process cover images for each manga
        $coverFound = 0;
        foreach ($mangaList as &$manga) {
            $manga['coverUrl'] = $this->mangadexService->getCoverImageUrl($manga, $included, '256');
            if ($manga['coverUrl']) {
                $coverFound++;
            }
        }
        unset($manga); // Break reference

        Log::info("Cover URLs found: {$coverFound} of " . count($mangaList));

        return compact('mangaList', 'included', 'pagination');
    }

    public function index(Request $request)
    {
        $page = max(1, (int) $request->get('page', 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $latestData = $this->mangadexService->getLatestManga($limit, $offset);
        $recommendedData = $this->mangadexService->getRecommendedManga(8);

        $latest = $this->processMangaList($latestData);
        $recommended = $this->processMangaList($recommendedData);

        return view('comics.index', [
            'latestManga' => $latest['mangaList'],
            'latestIncluded' => $latest['included'],
            'latestPagination' => $latest['pagination'],
            'recommendedManga' => $recommended['mangaList'],
            'recommendedIncluded' => $recommended['included'],
        ]);
    }
    
    public function showChapters(string $mangaId)
    {
        $chaptersData = $this->mangadexService->getMangaChapters($mangaId);
        $mangaResponse = $this->mangadexService->getMangaDetails($mangaId);
        
        $mangaDetails = $mangaResponse['data'] ?? null;
        $mangaIncluded = $mangaResponse['included'] ?? null;

        $coverUrl = null;
        $mangaTitle = 'Manga Tidak Ditemukan';

        if ($mangaDetails) {
            $coverUrl = $this->mangadexService->getCoverImageUrl($mangaDetails, $mangaIncluded, '512');
            
            // Logika sederhana untuk mendapatkan judul
            $titles = $mangaDetails['attributes']['title'] ?? [];
            $mangaTitle = $titles['en'] ?? $titles['ja'] ?? ($titles[array_key_first($titles)] ?? 'Manga');
        }

        return view('comics.chapters', [
            'mangaId' => $mangaId,
            'chapters' => $chaptersData['data'] ?? [],
            'total' => $chaptersData['total'] ?? 0,
            'coverUrl' => $coverUrl,
            'mangaTitle' => $mangaTitle,
        ]);
    }

    /**
     * Menampilkan halaman-halaman dari chapter tertentu.
     */
    public function readChapter(string $chapterId)
    {
        $pageUrls = $this->mangadexService->getChapterImages($chapterId);

        return view('comics.reader', [
            'chapterId' => $chapterId,
            'pageUrls' => $pageUrls,
        ]);
    }

    /**
     * Proxy untuk serve gambar chapter dari MangaDex CDN
     * Menghindari masalah CORS dan referrer policy
     */
    public function proxyChapterImage(Request $request)
    {
        $imageUrl = $request->query('url');
        
        if (!$imageUrl) {
            abort(400, 'URL parameter is required');
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Referer' => 'https://mangadex.org/',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])
                ->get($imageUrl);

            if ($response->successful()) {
                return response($response->body(), 200)
                    ->header('Content-Type', $response->header('Content-Type') ?? 'image/png')
                    ->header('Cache-Control', 'public, max-age=3600');
            }

            abort(404, 'Image not found');
        } catch (\Exception $e) {
            Log::error("Failed to proxy chapter image: " . $e->getMessage());
            abort(500, 'Failed to load image');
        }
    }
}