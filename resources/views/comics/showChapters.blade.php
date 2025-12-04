<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Chapter untuk Manga ID: {{ $mangaId }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @forelse ($chapters as $chapter)
                        @php
                            $chapterNumber = $chapter['attributes']['chapter'] ?? 'TBD';
                            $chapterTitle = $chapter['attributes']['title'] ?? 'Tanpa Judul';
                        @endphp
                        <a href="{{ route('comics.readChapter', ['chapterId' => $chapter['id']]) }}" 
                           class="block border-b p-3 hover:bg-gray-50">
                            **Chapter {{ $chapterNumber }}**: {{ $chapterTitle }}
                        </a>
                    @empty
                        <p>Tidak ada chapter yang tersedia.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>