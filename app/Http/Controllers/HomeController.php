<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MangadexService;

class HomeController extends Controller
{
    protected MangadexService $mangadexService;

    public function __construct(MangadexService $mangadexService)
    {
        $this->mangadexService = $mangadexService;
    }

    /**
     * Beranda publik yang menampilkan daftar komik terbaru dan rekomendasi.
     */
    public function index(Request $request)
    {
        $perPage = 12;
        $currentPage = max((int) $request->query('page', 1), 1);
        $offset = ($currentPage - 1) * $perPage;

        $latestResponse = $this->mangadexService->getLatestManga($perPage, $offset);
        $latestManga = $latestResponse['data'] ?? [];
        $latestIncluded = $latestResponse['included'] ?? [];
        $latestTotal = $latestResponse['total'] ?? 0;
        $lastPage = max(1, (int) ceil(($latestTotal ?: $perPage) / $perPage));

        $recommendedResponse = $this->mangadexService->getRecommendedManga(8);
        $recommendedManga = $recommendedResponse['data'] ?? [];
        $recommendedIncluded = $recommendedResponse['included'] ?? [];

        // Process cover images untuk latest manga
        foreach ($latestManga as &$manga) {
            $manga['coverUrl'] = $this->mangadexService->getCoverImageUrl($manga, $latestIncluded, '256');
        }
        unset($manga);

        // Process cover images untuk recommended manga
        foreach ($recommendedManga as &$manga) {
            $manga['coverUrl'] = $this->mangadexService->getCoverImageUrl($manga, $recommendedIncluded, '512');
        }
        unset($manga);

        $pagination = [
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total' => $latestTotal,
            'last_page' => $lastPage,
            'has_more' => $currentPage < $lastPage,
            'has_previous' => $currentPage > 1,
        ];

        return view('welcome', [
            'latestManga' => $latestManga,
            'recommendedManga' => $recommendedManga,
            'pagination' => $pagination,
        ]);
    }
}

