<!-- Bookmarks / Watchlist Sidebar -->
<div id="bookmarks" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-gray-900 dark:text-gray-100">⭐ Watchlist</h3>
        <span class="text-xs bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 px-2 py-1 rounded">
            {{ $totalBookmarks }}
        </span>
    </div>

    @if($bookmarks->count() > 0)
        <ul class="space-y-3">
            @foreach($bookmarks as $bookmark)
                <li class="flex items-start gap-3 pb-3 border-b dark:border-gray-700 last:border-0">
                    <!-- Cover Thumbnail -->
                    @if($bookmark->cover_url)
                        <img src="{{ $bookmark->cover_url }}" 
                             alt="{{ $bookmark->manga_title }}" 
                             class="w-10 h-14 object-cover rounded flex-shrink-0"
                             data-manga-cover
                             loading="lazy"
                             onerror="this.onerror=null; this.src='/images/no-cover.svg'">
                    @else
                        <div class="w-10 h-14 bg-gray-200 dark:bg-gray-700 rounded flex-shrink-0 flex items-center justify-center">
                            <span class="text-gray-400 text-xs">📚</span>
                        </div>
                    @endif

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('comics.showChapters', ['mangaId' => $bookmark->manga_id]) }}" 
                           class="text-sm font-semibold text-gray-900 dark:text-gray-100 hover:text-purple-600 dark:hover:text-purple-400 transition-colors line-clamp-2">
                            {{ $bookmark->manga_title }}
                        </a>
                        @if($bookmark->notes)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-1">
                                {{ $bookmark->notes }}
                            </p>
                        @endif
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $bookmark->created_at->diffForHumans() }}
                        </div>
                    </div>

                    <!-- Actions (hidden on hover reveal) -->
                    <button onclick="removeBookmark({{ $bookmark->id }})" 
                            aria-label="Hapus dari watchlist"
                            class="text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors flex-shrink-0 opacity-0 group-hover:opacity-100">
                        ✕
                    </button>
                </li>
            @endforeach
        </ul>

        @if($totalBookmarks > count($bookmarks))
            <a href="#" class="text-xs text-purple-600 dark:text-purple-400 hover:underline mt-4 inline-block">
                Lihat {{ $totalBookmarks - count($bookmarks) }} watchlist lainnya →
            </a>
        @endif
    @else
        <div class="text-center py-6">
            <div class="text-3xl mb-2">🔖</div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Belum ada watchlist</p>
            <a href="{{ route('home') }}" class="text-xs text-purple-600 dark:text-purple-400 hover:underline font-semibold">
                Cari & bookmark komik
            </a>
        </div>
    @endif
</div>

<script>
    function removeBookmark(bookmarkId) {
        if (!confirm('Hapus dari watchlist?')) return;
        
        fetch(`/dashboard/bookmarks/${bookmarkId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => {
            if (response.ok) {
                location.reload();
                announceToScreenReader('Bookmark dihapus dari watchlist');
            }
        })
        .catch(err => console.error('Error:', err));
    }
</script>
