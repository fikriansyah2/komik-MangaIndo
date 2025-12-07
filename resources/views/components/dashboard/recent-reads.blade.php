<!-- Recent Reads Grid -->
<div id="recent" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">📖 Bacaan Terakhir</h3>
        <a href="#" class="text-sm text-purple-600 dark:text-purple-400 hover:underline">Lihat Semua</a>
    </div>

    @if($recentReads->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($recentReads as $read)
                @php
                    $progress = $read->total_pages > 0 ? round(($read->page / $read->total_pages) * 100) : 0;
                @endphp
                <a href="{{ route('comics.readChapter', ['chapterId' => $read->chapter_id]) }}" 
                   class="group block h-full">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg overflow-hidden shadow hover:shadow-lg transition-all">
                        <!-- Cover Image Container -->
                        <div class="relative aspect-[2/3] overflow-hidden bg-gray-200 dark:bg-gray-600">
                            @if($read->cover_url)
                                <img src="{{ $read->cover_url }}" 
                                     alt="{{ $read->manga_title }}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                                     data-manga-cover
                                     loading="lazy"
                                     onerror="this.onerror=null; this.src='/images/no-cover.svg'">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <span class="text-gray-400 text-xs">No Cover</span>
                                </div>
                            @endif

                            <!-- Progress Badge -->
                            @if($progress > 0 && $progress < 100)
                                <div class="absolute top-2 right-2 bg-black/70 text-white text-xs px-2 py-1 rounded">
                                    {{ $progress }}%
                                </div>
                            @elseif($progress >= 100)
                                <div class="absolute top-2 right-2 bg-green-500/90 text-white text-xs px-2 py-1 rounded flex items-center gap-1">
                                    <span>✓</span> Selesai
                                </div>
                            @endif

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <button class="bg-white text-gray-900 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100">
                                    Lanjutkan
                                </button>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="p-3">
                            <h4 class="font-semibold text-sm text-gray-900 dark:text-gray-100 line-clamp-2 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                                {{ Str::limit($read->manga_title, 30) }}
                            </h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                Ch. {{ $read->chapter_number }}
                            </p>
                            @if($read->total_pages > 0)
                                <div class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                                    Hal. {{ $read->page }}/{{ $read->total_pages }}
                                </div>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-4xl mb-3">📚</div>
            <p class="text-gray-600 dark:text-gray-400 mb-2">Belum ada bacaan terakhir</p>
            <p class="text-sm text-gray-500 dark:text-gray-500 mb-4">Kunjungi halaman komik untuk mulai membaca, maka riwayat akan tercatat otomatis.</p>
            <a href="{{ route('home') }}" class="text-purple-600 dark:text-purple-400 hover:underline font-semibold">
                Jelajahi komik sekarang →
            </a>
        </div>
    @endif
</div>
