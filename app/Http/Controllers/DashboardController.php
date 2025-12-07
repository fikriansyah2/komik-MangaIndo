<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\UserBookmark;
use App\Models\UserReadingProgress;
use App\Services\MangadexService;

class DashboardController extends Controller
{
    protected $mangadexService;

    public function __construct(MangadexService $mangadexService)
    {
        $this->mangadexService = $mangadexService;
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;

        // Fetch recent reads (limit 8) — cached per user for 1 hour
        $recentReads = Cache::remember(
            "user.recent_reads.{$userId}",
            3600,
            fn() => UserReadingProgress::where('user_id', $userId)
                ->orderBy('updated_at', 'desc')
                ->limit(8)
                ->get()
        );

        // Fetch bookmarks/watchlist (limit 12) — cached per user for 1 hour
        $bookmarks = Cache::remember(
            "user.bookmarks.{$userId}",
            3600,
            fn() => UserBookmark::where('user_id', $userId)
                ->where('is_reading', true)
                ->orderBy('created_at', 'desc')
                ->limit(12)
                ->get()
        );

        // Fetch recommendations — cached for all users for 6 hours
        $recommendations = Cache::remember(
            'manga.recommendations',
            21600,
            fn() => $this->mangadexService->getRecommendedManga(6)
        );

        // Process recommended manga to include cover URLs
        $recommendedList = [];
        if (isset($recommendations['data']) && is_array($recommendations['data'])) {
            $included = $recommendations['included'] ?? [];
            foreach ($recommendations['data'] as $manga) {
                $coverUrl = $this->mangadexService->getCoverImageUrl($manga, $included, '256');
                $titles = $manga['attributes']['title'] ?? [];
                $firstTitle = reset($titles);
                $recommendedList[] = [
                    'id' => $manga['id'],
                    'title' => $titles['en'] 
                        ?? $titles['ja'] 
                        ?? ($firstTitle ?: 'Unknown Title'),
                    'coverUrl' => $coverUrl,
                    'status' => $manga['attributes']['status'] ?? 'ongoing',
                ];
            }
        }

        // Count total bookmarks
        $totalBookmarks = Cache::remember(
            "user.bookmarks.count.{$userId}",
            3600,
            fn() => UserBookmark::where('user_id', $userId)->count()
        );

        // Count total reading progress entries
        $totalReads = Cache::remember(
            "user.reads.count.{$userId}",
            3600,
            fn() => UserReadingProgress::where('user_id', $userId)->count()
        );

        return view('dashboard', [
            'recentReads' => $recentReads,
            'bookmarks' => $bookmarks,
            'recommendations' => $recommendedList,
            'totalBookmarks' => $totalBookmarks,
            'totalReads' => $totalReads,
            'user' => $user,
        ]);
    }

    /**
     * Add bookmark / save to watchlist
     */
    public function storeBookmark(Request $request)
    {
        $request->validate([
            'manga_id' => 'required|string|max:36',
            'manga_title' => 'required|string|max:500',
            'cover_url' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:500',
        ]);

        $userId = $request->user()->id;

        // Check if already bookmarked
        $existing = UserBookmark::where('user_id', $userId)
            ->where('manga_id', $request->manga_id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Already bookmarked'], 409);
        }

        $bookmark = UserBookmark::create([
            'user_id' => $userId,
            'manga_id' => $request->manga_id,
            'manga_title' => $request->manga_title,
            'cover_url' => $request->cover_url,
            'notes' => $request->notes,
            'is_reading' => true,
        ]);

        // Clear cache
        Cache::forget("user.bookmarks.{$userId}");
        Cache::forget("user.bookmarks.count.{$userId}");

        return response()->json(['message' => 'Bookmark added', 'bookmark' => $bookmark], 201);
    }

    /**
     * Remove bookmark
     */
    public function destroyBookmark(Request $request, $bookmarkId)
    {
        $bookmark = UserBookmark::where('id', $bookmarkId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $bookmark->delete();

        // Clear cache
        Cache::forget("user.bookmarks.{$request->user()->id}");
        Cache::forget("user.bookmarks.count.{$request->user()->id}");

        return response()->json(['message' => 'Bookmark removed'], 200);
    }

    /**
     * Update reading progress
     */
    public function updateProgress(Request $request)
    {
        $request->validate([
            'manga_id' => 'required|string|max:36',
            'chapter_id' => 'required|string|max:36',
            'chapter_number' => 'nullable|string|max:20',
            'page' => 'required|integer|min:0',
            'total_pages' => 'required|integer|min:0',
            'manga_title' => 'required|string|max:500',
            'chapter_title' => 'nullable|string|max:500',
            'cover_url' => 'nullable|string|max:1000',
        ]);

        $userId = $request->user()->id;

        $progress = UserReadingProgress::updateOrCreate(
            ['user_id' => $userId, 'manga_id' => $request->manga_id],
            [
                'chapter_id' => $request->chapter_id,
                'chapter_number' => $request->chapter_number,
                'page' => $request->page,
                'total_pages' => $request->total_pages,
                'manga_title' => $request->manga_title,
                'chapter_title' => $request->chapter_title,
                'cover_url' => $request->cover_url,
            ]
        );

        // Clear cache
        Cache::forget("user.recent_reads.{$userId}");
        Cache::forget("user.reads.count.{$userId}");

        return response()->json(['message' => 'Progress updated', 'progress' => $progress], 200);
    }
}
