<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Membaca Chapter ID: {{ $chapterId }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 text-center">
                    @forelse ($pageUrls as $url)
                        <img src="{{ $url }}" alt="Halaman Komik" class="mx-auto my-4 shadow-lg">
                        <hr class="my-6">
                    @empty
                        <p>Gambar halaman tidak dapat dimuat atau chapter ini kosong.</p>
                    @endforelse
                    
                    {{-- Tambahkan navigasi (kembali ke daftar chapter, chapter selanjutnya) --}}
                    <div class="mt-8">
                        <a href="{{ url()->previous() }}" class="text-blue-500 hover:text-blue-700">← Kembali ke Daftar Chapter</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>