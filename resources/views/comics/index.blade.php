<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Komik Terbaru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Latest Updates Section --}}
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Latest Updates</h2>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            @forelse ($latestManga as $item)
                                @php
                                    $mangaId = $item['id'];
                                    $title = $item['attributes']['title']['en'] 
                                        ?? $item['attributes']['title']['ja'] 
                                        ?? ($item['attributes']['title'][array_key_first($item['attributes']['title'] ?? [])] ?? 'Judul Tidak Tersedia');
                                    $coverUrl = $item['coverUrl'] ?? null;
                                @endphp
                                
                                <a href="{{ route('comics.showChapters', ['mangaId' => $mangaId]) }}" 
                                   class="group block">
                                    <div class="border rounded-lg shadow hover:shadow-lg transition-shadow duration-200 overflow-hidden">
                                        @if($coverUrl)
                                                <img src="{{ route('comics.coverProxy') }}?url={{ rawurlencode($coverUrl . '.256.jpg') }}" 
                                                 alt="{{ $title }}" 
                                                 class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-200"
                                                 onerror="this.onerror=null; this.src='https://via.placeholder.com/256x384?text=No+Cover'">
                                        @else
                                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                                <span class="text-gray-400 text-sm">No Cover</span>
                                            </div>
                                        @endif
                                        <div class="p-3">
                                            <h3 class="font-semibold text-sm text-gray-900 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                                {{ $title }}
                                            </h3>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="col-span-full p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                                    <p class="font-bold">⚠️ Data Komik Tidak Ditemukan atau Gagal Diambil.</p>
                                    <p class="text-sm mt-2">Coba muat ulang halaman atau periksa koneksi API Mangadex.</p>
                                </div>
                            @endforelse
                        </div>
                        
                        {{-- Pagination --}}
                        @if(isset($latestPagination) && $latestPagination['total'] > $latestPagination['limit'])
                            <div class="mt-6 flex justify-center gap-2">
                                @php
                                    $currentPage = ($latestPagination['offset'] / $latestPagination['limit']) + 1;
                                    $totalPages = ceil($latestPagination['total'] / $latestPagination['limit']);
                                @endphp
                                
                                @if($currentPage > 1)
                                    <a href="{{ route('comics.index', ['page' => $currentPage - 1]) }}" 
                                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                        Previous
                                    </a>
                                @endif
                                
                                <span class="px-4 py-2 text-gray-700">
                                    Page {{ $currentPage }} of {{ $totalPages }}
                                </span>
                                
                                @if($currentPage < $totalPages)
                                    <a href="{{ route('comics.index', ['page' => $currentPage + 1]) }}" 
                                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                        Next
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Recommended Section --}}
            @if(isset($recommendedManga) && count($recommendedManga) > 0)
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Rekomendasi</h2>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                @foreach ($recommendedManga as $item)
                                    @php
                                        $mangaId = $item['id'];
                                        $title = $item['attributes']['title']['en'] 
                                            ?? $item['attributes']['title']['ja'] 
                                            ?? ($item['attributes']['title'][array_key_first($item['attributes']['title'] ?? [])] ?? 'Judul Tidak Tersedia');
                                        $coverUrl = $item['coverUrl'] ?? null;
                                    @endphp
                                    
                                    <a href="{{ route('comics.showChapters', ['mangaId' => $mangaId]) }}" 
                                       class="group block">
                                        <div class="border rounded-lg shadow hover:shadow-lg transition-shadow duration-200 overflow-hidden">
                                            @if($coverUrl)
                                                <img src="{{ route('comics.coverProxy') }}?url={{ rawurlencode($coverUrl . '.256.jpg') }}" 
                                                     alt="{{ $title }}" 
                                                     class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-200"
                                                     onerror="this.onerror=null; this.src='https://via.placeholder.com/256x384?text=No+Cover'">
                                            @else
                                                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                                    <span class="text-gray-400 text-sm">No Cover</span>
                                                </div>
                                            @endif
                                            <div class="p-3">
                                                <h3 class="font-semibold text-sm text-gray-900 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                                    {{ $title }}
                                                </h3>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
