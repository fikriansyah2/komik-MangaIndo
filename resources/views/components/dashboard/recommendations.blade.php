<!-- Recommendations Section -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">✨ Rekomendasi</h3>
        <a href="{{ route('home') }}" class="text-sm text-purple-600 dark:text-purple-400 hover:underline">
            Lihat Semua
        </a>
    </div>

    @if(count($recommendations) > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($recommendations as $manga)
                <a href="{{ route('comics.showChapters', ['mangaId' => $manga['id']]) }}" 
                   class="group block h-full">
                    <article class="bg-gray-50 dark:bg-gray-700 rounded-lg overflow-hidden shadow hover:shadow-lg transition-all h-full">
                        <!-- Cover -->
                        <div class="relative aspect-[2/3] overflow-hidden bg-gray-200 dark:bg-gray-600">
                            @if($manga['coverUrl'])
                                <img src="{{ $manga['coverUrl'] }}" 
                                     alt="{{ $manga['title'] }}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                                     data-manga-cover
                                     loading="lazy"
                                     onerror="this.onerror=null; this.src='/images/no-cover.svg'">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <span class="text-gray-400 text-xs">No Cover</span>
                                </div>
                            @endif

                            <!-- Status Badge -->
                            <div class="absolute top-2 left-2">
                                @php
                                    $statusClass = match(strtolower($manga['status'])) {
                                        'ongoing' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
                                        'completed' => 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
                                        'hiatus' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
                                        'cancelled' => 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
                                        default => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
                                    };
                                    $statusText = match(strtolower($manga['status'])) {
                                        'ongoing' => 'Ongoing',
                                        'completed' => 'Selesai',
                                        'hiatus' => 'Hiatus',
                                        'cancelled' => 'Batal',
                                        default => ucfirst($manga['status']),
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </div>

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <button class="bg-white text-gray-900 px-3 py-2 rounded font-semibold text-sm hover:bg-gray-100">
                                    Baca
                                </button>
                            </div>
                        </div>

                        <!-- Title -->
                        <div class="p-3">
                            <h4 class="font-semibold text-sm text-gray-900 dark:text-gray-100 line-clamp-2 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                                {{ Str::limit($manga['title'], 40) }}
                            </h4>
                        </div>
                    </article>
                </a>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-600 dark:text-gray-400">Tidak ada rekomendasi saat ini</p>
        </div>
    @endif
</div>
