@php
    use Illuminate\Support\Str;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard
            </h2>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ auth()->user()->name }}
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Quick Actions Bar -->
            <div class="mb-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('home') }}" class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-4 rounded-lg shadow hover:shadow-lg transition-all">
                    <div class="font-semibold">Cari Komik</div>
                    <div class="text-sm text-purple-100">Temukan komik baru</div>
                </a>
                <a href="#bookmarks" class="bg-gradient-to-r from-blue-600 to-cyan-600 text-white p-4 rounded-lg shadow hover:shadow-lg transition-all">
                    <div class="font-semibold">{{ $totalBookmarks }} Watchlist</div>
                    <div class="text-sm text-blue-100">Komik yang di-follow</div>
                </a>
                <a href="#recent" class="bg-gradient-to-r from-green-600 to-emerald-600 text-white p-4 rounded-lg shadow hover:shadow-lg transition-all">
                    <div class="font-semibold">{{ $totalReads }} Sedang Dibaca</div>
                    <div class="text-sm text-green-100">Bacaan terakhir</div>
                </a>
                <a href="{{ route('profile.edit') }}" class="bg-gradient-to-r from-orange-600 to-red-600 text-white p-4 rounded-lg shadow hover:shadow-lg transition-all">
                    <div class="font-semibold">Pengaturan</div>
                    <div class="text-sm text-orange-100">Edit profil & preferensi</div>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content Area (2/3) -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Continue Reading Section -->
                    @if($recentReads->count() > 0)
                        <x-dashboard.continue-reading :recentReads="$recentReads" />
                    @endif

                    <!-- Recent Reads Grid -->
                    <x-dashboard.recent-reads :recentReads="$recentReads" />

                    <!-- Recommendations Section -->
                    @if(count($recommendations) > 0)
                        <x-dashboard.recommendations :recommendations="$recommendations" />
                    @endif
                </div>

                <!-- Sidebar (1/3) -->
                <aside class="space-y-6">
                    <!-- Bookmarks/Watchlist Card -->
                    <x-dashboard.bookmarks :bookmarks="$bookmarks" :totalBookmarks="$totalBookmarks" />

                    <!-- Account Summary Card -->
                    <x-dashboard.account-summary :user="$user" />

                    <!-- Quick Tips -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">💡 Tips</h3>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                            <li>📌 Gunakan tombol <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">/</kbd> untuk cari cepat</li>
                            <li>⭐ Bookmark komik favorit untuk akses cepat</li>
                            <li>📖 Bacaan otomatis disimpan saat Anda membaca</li>
                            <li>🔔 Aktifkan notifikasi untuk update chapter</li>
                        </ul>
                    </div>

                    <!-- Stats Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">📊 Statistik</h3>
                        <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <div class="flex justify-between">
                                <span>Total Bookmark:</span>
                                <span class="font-semibold">{{ $totalBookmarks }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Sedang Dibaca:</span>
                                <span class="font-semibold">{{ $totalReads }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Member Sejak:</span>
                                <span class="font-semibold">{{ $user->created_at->format('d M Y') }}</span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    <!-- Keyboard Shortcuts & Accessibility Script -->
    <script>
        (function() {
            // Forward slash (/) to focus search
            document.addEventListener('keydown', (e) => {
                if ((e.key === '/' || e.key === '?') && !e.ctrlKey && !e.metaKey && !e.altKey) {
                    const searchInput = document.querySelector('[data-role="search-input"]');
                    if (searchInput) {
                        e.preventDefault();
                        searchInput.focus();
                    }
                }
            });

            // Mark images with aria-label for screen readers
            document.querySelectorAll('img[data-manga-cover]').forEach(img => {
                if (!img.getAttribute('aria-label')) {
                    img.setAttribute('aria-label', img.getAttribute('alt') || 'Manga cover');
                }
            });

            // Announce dynamic content changes for screen readers
            const announcer = document.createElement('div');
            announcer.setAttribute('aria-live', 'polite');
            announcer.setAttribute('aria-atomic', 'true');
            announcer.setAttribute('class', 'sr-only');
            document.body.appendChild(announcer);

            window.announceToScreenReader = (message) => {
                announcer.textContent = message;
            };
        })();
    </script>
</x-app-layout>
