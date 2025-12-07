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
        $this->middleware('auth')->except('index'); // Izinkan 'index' diakses tanpa login
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
     * Proxy untuk serve cover image dari MangaDex CDN
     * Menghindari masalah CORS
     */
    public function proxyCoverImage(Request $request)
    {
        $coverUrl = $request->query('url');
        if (!$coverUrl) {
            abort(400, 'URL parameter is required');
        }

        // Defensive: decode and validate incoming URL to avoid double-encoding and SSRF
        $coverUrl = urldecode($coverUrl);
        Log::info('Proxy cover requested', ['url' => $coverUrl]);

        // Only allow Mangadex uploads domain for security
        $allowedPrefix = 'https://uploads.mangadex.org/covers/';
        if (stripos($coverUrl, $allowedPrefix) !== 0) {
            Log::warning('Rejected proxy cover URL (not allowed)', ['url' => $coverUrl]);
            // Serve local placeholder instead of aborting
            return response(file_get_contents(public_path('images/no-cover.svg')), 200)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Cache-Control', 'public, max-age=86400');
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Referer' => 'https://mangadex.org/',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])
                ->get($coverUrl);

            Log::info('Proxy cover response', ['url' => $coverUrl, 'status' => $response->status()]);

            if ($response->successful()) {
                return response($response->body(), 200)
                    ->header('Content-Type', $response->header('Content-Type') ?? 'image/jpeg')
                    ->header('Cache-Control', 'public, max-age=86400');
            }

            Log::warning('Cover image not found; serving local placeholder', ['url' => $coverUrl, 'status' => $response->status()]);
            return response(file_get_contents(public_path('images/no-cover.svg')), 200)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Cache-Control', 'public, max-age=86400');

        } catch (\Exception $e) {
            Log::error("Failed to proxy cover image: " . $e->getMessage(), ['url' => $coverUrl]);
            return response(file_get_contents(public_path('images/no-cover.svg')), 200)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Cache-Control', 'public, max-age=86400');
        }
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

        $imageUrl = urldecode($imageUrl);
        Log::info('Proxy chapter image requested', ['url' => $imageUrl]);

        // Only allow Mangadex at-home or uploads domain
        $allowedPrefixes = [
            'https://uploads.mangadex.org/',
            'https://uploads.mangadex.org/covers/',
            'https://uploads.mangadex.org/data/',
        ];
        $allowed = false;
        foreach ($allowedPrefixes as $p) {
            if (stripos($imageUrl, $p) === 0) { $allowed = true; break; }
        }
        if (! $allowed) {
            Log::warning('Rejected proxy chapter URL (not allowed)', ['url' => $imageUrl]);
            abort(403, 'URL not allowed');
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Referer' => 'https://mangadex.org/',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])
                ->get($imageUrl);

            Log::info('Proxy chapter response', ['url' => $imageUrl, 'status' => $response->status()]);

            if ($response->successful()) {
                return response($response->body(), 200)
                    ->header('Content-Type', $response->header('Content-Type') ?? 'image/png')
                    ->header('Cache-Control', 'public, max-age=3600');
            }

            Log::warning('Chapter image not found', ['url' => $imageUrl, 'status' => $response->status()]);
            abort(404, 'Image not found');
        } catch (\Exception $e) {
            Log::error("Failed to proxy chapter image: " . $e->getMessage(), ['url' => $imageUrl]);
            abort(500, 'Failed to load image');
        }
    }
}