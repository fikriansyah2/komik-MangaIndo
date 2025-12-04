<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Membaca Chapter
        </h2>
    </x-slot>
    
    @push('head')
    <meta name="referrer" content="no-referrer">
    @endpush

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    @if(count($pageUrls) > 0)
                        <div class="mb-4 flex items-center justify-between">
                            <p class="text-sm text-gray-600">Total: {{ count($pageUrls) }} halaman</p>
                            <a href="{{ url()->previous() }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                ← Kembali ke Daftar Chapter
                            </a>
                        </div>
                        
                        <div class="space-y-4">
                            @foreach ($pageUrls as $index => $url)
                                <div class="flex flex-col items-center">
                                    <div class="mb-2 text-xs text-gray-500">
                                        Halaman {{ $index + 1 }} dari {{ count($pageUrls) }}
                                    </div>
                                    @php
                                        // Coba langsung dulu, jika gagal akan fallback ke proxy
                                        $directUrl = $url;
                                        $proxyUrl = route('comics.proxyImage', ['url' => urlencode($url)]);
                                    @endphp
                                    <img src="{{ $directUrl }}" 
                                         alt="Halaman {{ $index + 1 }}" 
                                         class="max-w-full h-auto shadow-lg rounded chapter-image"
                                         loading="lazy"
                                         referrerpolicy="no-referrer"
                                         data-proxy-url="{{ $proxyUrl }}"
                                         onload="console.log('Image loaded:', '{{ $directUrl }}')"
                                         onerror="console.error('Failed to load image directly, trying proxy:', '{{ $directUrl }}'); const img = this; const proxyUrl = img.getAttribute('data-proxy-url'); if (proxyUrl && !img.dataset.proxyTried) { img.dataset.proxyTried = 'true'; img.src = proxyUrl; } else { img.style.display='none'; if(img.nextElementSibling) img.nextElementSibling.style.display='block'; }">
                                    <div style="display:none;" class="text-center py-8 bg-gray-100 rounded">
                                        <p class="text-gray-600 text-sm">Gagal memuat gambar halaman {{ $index + 1 }}</p>
                                        <p class="text-gray-400 text-xs mt-2 break-all">{{ $url }}</p>
                                    </div>
                                </div>
                                @if($index < count($pageUrls) - 1)
                                    <hr class="my-6 border-gray-200">
                                @endif
                            @endforeach
                        </div>
                        
                        <div class="mt-8 text-center">
                            <a href="{{ url()->previous() }}" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                ← Kembali ke Daftar Chapter
                            </a>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-2">Gambar halaman tidak dapat dimuat</p>
                            <p class="text-sm text-gray-600 mb-6">Chapter ID: {{ $chapterId }}</p>
                            <a href="{{ url()->previous() }}" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                ← Kembali ke Daftar Chapter
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

