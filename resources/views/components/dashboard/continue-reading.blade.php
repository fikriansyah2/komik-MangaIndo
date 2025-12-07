<!-- Continue Reading - Hero section with first recent read -->
@if($recentReads->first())
    @php
        $first = $recentReads->first();
        $progress = $first->total_pages > 0 ? round(($first->page / $first->total_pages) * 100) : 0;
    @endphp
    <div id="continue" class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg shadow-lg overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
            <!-- Cover Image -->
            @if($first->cover_url)
                <div class="flex justify-center md:justify-start">
                    <img src="{{ $first->cover_url }}" 
                         alt="{{ $first->manga_title }}" 
                         class="w-32 h-48 object-cover rounded-lg shadow-lg"
                         data-manga-cover
                         onerror="this.onerror=null; this.src='/images/no-cover.svg'">
                </div>
            @endif

            <!-- Content -->
            <div class="md:col-span-2 text-white flex flex-col justify-center">
                <div class="text-sm font-semibold text-purple-100 mb-2">LANJUTKAN MEMBACA</div>
                <h3 class="text-2xl font-bold mb-2">{{ Str::limit($first->manga_title, 60) }}</h3>
                <p class="text-purple-100 mb-4">
                    {{ $first->chapter_title ? "Chapter {$first->chapter_number}: {$first->chapter_title}" : "Chapter {$first->chapter_number}" }}
                </p>
                
                <!-- Progress Bar -->
                @if($first->total_pages > 0)
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm">Halaman {{ $first->page }} dari {{ $first->total_pages }}</span>
                            <span class="text-sm font-semibold">{{ $progress }}%</span>
                        </div>
                        <div class="w-full bg-purple-300 rounded-full h-2">
                            <div class="bg-yellow-300 h-2 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                @endif

                <div class="flex gap-3">
                    <a href="{{ route('comics.readChapter', ['chapterId' => $first->chapter_id]) }}" 
                       class="bg-white text-purple-600 px-6 py-2 rounded-lg font-semibold hover:bg-purple-50 transition-colors">
                        Lanjutkan
                    </a>
                    <a href="{{ route('comics.showChapters', ['mangaId' => $first->manga_id]) }}" 
                       class="border-2 border-white text-white px-6 py-2 rounded-lg font-semibold hover:bg-white/10 transition-colors">
                        Lihat Semua Chapter
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
