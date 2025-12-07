<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $mangaTitle ?? 'Manga Details' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Manga Header Section (mirip MangaDex) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row gap-6">
                        {{-- Cover Image --}}
                        <div class="flex-shrink-0">
                            @if(isset($coverUrl) && $coverUrl)
                                @php
                                    $proxyCoverUrl = route('comics.coverProxy', ['url' => $coverUrl]);
                                @endphp
                                  <img src="{{ $proxyCoverUrl }}" 
                                      alt="{{ $mangaTitle ?? 'Cover' }}" 
                                      class="w-48 h-72 object-cover rounded-lg shadow-lg border border-gray-200"
                                     onerror="this.onerror=null; this.src='{{ asset('images/no-cover.svg') }}'">
                            @else
                                <div class="w-48 h-72 bg-gray-200 rounded-lg flex items-center justify-center border border-gray-300">
                                    <span class="text-gray-400 text-sm">No Cover</span>
                                </div>
                            @endif
                        </div>

                        {{-- Manga Info --}}
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                                {{ $mangaTitle ?? 'Manga Title' }}
                            </h1>
                            
                            <div class="space-y-2 mb-4">
                                @if(isset($total) && $total > 0)
                                    <p class="text-sm text-gray-600">
                                        <span class="font-semibold text-gray-900">{{ $total }}</span> chapter tersedia
                                    </p>
                                @endif
                                <p class="text-xs text-gray-500">Manga ID: {{ $mangaId }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chapters List Section --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Chapters</h2>
                    
                    @if(isset($total) && $total > 0 && count($chapters) == 0)
                        <div class="p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded">
                            <p class="font-bold">⚠️ Total chapter: {{ $total }}, tetapi tidak ada yang ditampilkan.</p>
                            <p class="text-sm mt-2">Mungkin ada masalah dengan filter atau format data.</p>
                        </div>
                    @elseif(count($chapters) > 0)
                        <div class="mb-4 text-sm text-gray-600">
                            Menampilkan <span class="font-semibold">{{ count($chapters) }}</span> dari <span class="font-semibold">{{ $total ?? count($chapters) }}</span> chapter
                        </div>
                        
                        <div class="divide-y divide-gray-200">
                            @foreach ($chapters as $chapter)
                                @php
                                    $chapterNumber = $chapter['attributes']['chapter'] ?? 'TBD';
                                    $chapterTitle = $chapter['attributes']['title'] ?? 'Tanpa Judul';
                                    $volume = $chapter['attributes']['volume'] ?? null;
                                    $pages = $chapter['attributes']['pages'] ?? null;
                                    $publishedAt = $chapter['attributes']['publishAt'] ?? null;
                                @endphp
                                <a href="{{ route('comics.readChapter', ['chapterId' => $chapter['id']]) }}" 
                                   class="block p-4 hover:bg-gray-50 transition-colors duration-150">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                @if($volume)
                                                    <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded">Vol. {{ $volume }}</span>
                                                @endif
                                                <span class="font-semibold text-gray-900">Chapter {{ $chapterNumber }}</span>
                                                @if($chapterTitle && $chapterTitle !== 'Tanpa Judul')
                                                    <span class="text-gray-500">-</span>
                                                    <span class="text-gray-700">{{ $chapterTitle }}</span>
                                                @endif
                                            </div>
                                            @if($publishedAt)
                                                <p class="text-xs text-gray-500">
                                                    Published: {{ \Carbon\Carbon::parse($publishedAt)->format('M d, Y') }}
                                                </p>
                                            @endif
                                        </div>
                                        @if($pages)
                                            <div class="text-sm text-gray-500">
                                                {{ $pages }} pages
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <p class="font-bold">⚠️ Tidak ada chapter yang tersedia untuk manga ini.</p>
                            <p class="text-sm mt-2">Manga ID: {{ $mangaId }}</p>
                            <p class="text-sm">Silakan cek log Laravel untuk detail lebih lanjut.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
