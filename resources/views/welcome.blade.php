@php
    use Illuminate\Support\Str;

    $getTitle = function (array $manga): string {
        $titles = $manga['attributes']['title'] ?? [];
        if (! is_array($titles)) {
            return 'Unknown Title';
        }

        return $titles['en'] ?? $titles['ja'] ?? reset($titles) ?? 'Unknown Title';
    };

    $getDesc = function (array $manga): string {
        $descriptions = $manga['attributes']['description'] ?? [];
        if (! is_array($descriptions)) {
            return '';
        }

        $desc = $descriptions['en'] ?? $descriptions['id'] ?? reset($descriptions) ?? '';
        return Str::limit(strip_tags($desc), 120);
    };

    $getStatusBadge = function (string $status): array {
        $status = strtolower($status);
        $badges = [
            'ongoing' => ['bg-green-100 text-green-800', 'Ongoing'],
            'completed' => ['bg-blue-100 text-blue-800', 'Completed'],
            'hiatus' => ['bg-yellow-100 text-yellow-800', 'Hiatus'],
            'cancelled' => ['bg-red-100 text-red-800', 'Cancelled'],
        ];
        return $badges[$status] ?? ['bg-gray-100 text-gray-800', ucfirst($status)];
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>MangaIndo - Baca Komik Gratis</title>
        <meta name="description" content="Baca komik dan manga terbaru secara gratis. Update harian dengan koleksi lengkap dari MangaIndo.">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif

        <style>
            body { font-family: 'Inter', sans-serif; }
            .manga-card {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .manga-card:hover {
                transform: translateY(-8px);
            }
            .manga-cover {
                transition: transform 0.3s ease;
            }
            .manga-card:hover .manga-cover {
                transform: scale(1.05);
            }
            .gradient-overlay {
                background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 100%);
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .fade-in {
                animation: fadeIn 0.6s ease-out;
            }
        </style>
    </head>
    <body class="bg-gray-50 antialiased">
        {{-- Navigation Bar --}}
        <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-lg">
                            <span class="text-white font-bold text-xl">M</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900">MangaIndo</span>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('home') }}" class="text-gray-700 hover:text-purple-600 font-medium transition-colors">Beranda</a>
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-gray-700 hover:text-purple-600 font-medium transition-colors">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-700 hover:text-purple-600 font-medium transition-colors">Masuk</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-4 py-2 rounded-lg font-medium hover:from-purple-700 hover:to-indigo-700 transition-all shadow-md hover:shadow-lg">
                                    Daftar
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        {{-- Hero Section --}}
        <section class="relative bg-gradient-to-br from-purple-600 via-indigo-600 to-blue-600 text-white overflow-hidden">
            <div class="absolute inset-0 bg-black/20"></div>
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.05\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
                <div class="text-center">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold mb-4">
                        Baca Komik Favoritmu
                        <span class="block text-yellow-300 mt-2">Gratis & Tanpa Iklan</span>
                    </h1>
                    <p class="text-lg sm:text-xl text-purple-100 max-w-2xl mx-auto mb-8">
                        Jelajahi koleksi komik terbaru dan terpopuler. Update harian dengan ribuan chapter baru setiap hari.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <a href="#latest" class="bg-white text-purple-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            Lihat Update Terbaru
                        </a>
                        <a href="#recommended" class="bg-white/10 backdrop-blur-sm text-white border-2 border-white/30 px-8 py-3 rounded-lg font-semibold hover:bg-white/20 transition-all">
                            Komik Populer
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            {{-- Latest Updates Section --}}
            <section id="latest" class="mb-16 fade-in">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-1 h-8 bg-gradient-to-b from-purple-600 to-indigo-600 rounded-full"></div>
                            <h2 class="text-3xl font-bold text-gray-900">Update Terbaru</h2>
                        </div>
                        <p class="text-gray-600 mt-1">Komik yang baru saja diperbarui</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 sm:gap-6">
                    @forelse ($latestManga as $index => $manga)
                        @php
                            $coverUrl = $manga['coverUrl'] ?? null;
                            $title = $getTitle($manga);
                            $description = $getDesc($manga);
                            $status = $manga['attributes']['status'] ?? 'ongoing';
                            [$statusClass, $statusText] = $getStatusBadge($status);
                            
                            // Fallback image jika coverUrl null atau kosong
                            $finalCoverUrl = $coverUrl ?: 'https://via.placeholder.com/300x400/9CA3AF/FFFFFF?text=No+Cover';
                        @endphp
                        <a href="{{ route('comics.showChapters', ['mangaId' => $manga['id']]) }}" 
                           class="manga-card group block">
                            <article class="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-2xl">
                                <div class="relative aspect-[2/3] overflow-hidden bg-gray-200">
                                    @if($coverUrl)
                                        <img src="{{ route('comics.coverProxy') }}?url={{ rawurlencode($coverUrl . '.256.jpg') }}" 
                                             alt="{{ $title }}" 
                                             class="manga-cover w-full h-full object-cover"
                                             loading="lazy"
                                             onerror="handleCoverError(this)">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                            <span class="text-gray-400 text-xs text-center px-2">No Cover</span>
                                        </div>
                                    @endif
                                    
                                    {{-- New Badge --}}
                                    <div class="absolute top-2 left-2 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold px-2 py-1 rounded-md shadow-lg">
                                        NEW
                                    </div>
                                    
                                    {{-- Status Badge --}}
                                    <div class="absolute top-2 right-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold {{ $statusClass }} shadow-md">
                                            {{ $statusText }}
                                        </span>
                                    </div>
                                    
                                    {{-- Hover Overlay --}}
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/0 to-black/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        <div class="absolute bottom-0 left-0 right-0 p-3">
                                            <p class="text-white text-xs line-clamp-3">{{ $description ?: 'Tidak ada deskripsi' }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="p-3">
                                    <h3 class="font-semibold text-sm text-gray-900 line-clamp-2 group-hover:text-purple-600 transition-colors min-h-[2.5rem]">
                                        {{ $title }}
                                    </h3>
                                </div>
                            </article>
                        </a>
                    @empty
                        <div class="col-span-full bg-white rounded-xl p-12 text-center shadow-md">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="mt-4 text-gray-500 font-medium">Tidak ada data terbaru yang bisa ditampilkan.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if(($pagination['total'] ?? 0) > $pagination['per_page'])
                    <div class="mt-10 flex items-center justify-center">
                        <nav class="flex items-center space-x-2 bg-white rounded-lg shadow-md p-2">
                            @if($pagination['has_previous'])
                                <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] - 1]) }}" 
                                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    ← Sebelumnya
                                </a>
                            @else
                                <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                                    ← Sebelumnya
                                </span>
                            @endif
                            
                            <span class="px-4 py-2 text-sm font-medium text-gray-700">
                                Halaman <span class="font-bold text-purple-600">{{ $pagination['current_page'] }}</span> dari {{ $pagination['last_page'] }}
                            </span>
                            
                            @if($pagination['has_more'])
                                <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] + 1]) }}" 
                                   class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-all shadow-md hover:shadow-lg">
                                    Selanjutnya →
                                </a>
                            @else
                                <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                                    Selanjutnya →
                                </span>
                            @endif
                        </nav>
                    </div>
                @endif
            </section>

            {{-- Recommended Section --}}
            <section id="recommended" class="mb-16 fade-in">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-1 h-8 bg-gradient-to-b from-amber-500 to-orange-500 rounded-full"></div>
                            <h2 class="text-3xl font-bold text-gray-900">Rekomendasi</h2>
                        </div>
                        <p class="text-gray-600 mt-1">Komik populer yang banyak dibaca</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:gap-6">
                    @forelse ($recommendedManga as $manga)
                        @php
                            $coverUrl = $manga['coverUrl'] ?? null;
                            $title = $getTitle($manga);
                            $status = $manga['attributes']['status'] ?? 'ongoing';
                            [$statusClass, $statusText] = $getStatusBadge($status);
                        @endphp
                        <a href="{{ route('comics.showChapters', ['mangaId' => $manga['id']]) }}" 
                           class="manga-card group block">
                            <article class="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-2xl">
                                <div class="relative aspect-[2/3] overflow-hidden bg-gray-200">
                                    @if($coverUrl)
                                        <img src="{{ route('comics.coverProxy') }}?url={{ rawurlencode($coverUrl . '.256.jpg') }}" 
                                             alt="{{ $title }}" 
                                             class="manga-cover w-full h-full object-cover"
                                             loading="lazy"
                                             onerror="handleCoverError(this)">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                            <span class="text-gray-400 text-xs text-center px-2">No Cover</span>
                                        </div>
                                    @endif
                                    
                                    {{-- Popular Badge --}}
                                    <div class="absolute top-2 left-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-xs font-bold px-2 py-1 rounded-md shadow-lg flex items-center space-x-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <span>POPULAR</span>
                                    </div>
                                    
                                    {{-- Status Badge --}}
                                    <div class="absolute top-2 right-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold {{ $statusClass }} shadow-md">
                                            {{ $statusText }}
                                        </span>
                                    </div>
                                    
                                    {{-- Hover Overlay --}}
                                    <div class="absolute inset-0 gradient-overlay opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        <div class="absolute bottom-0 left-0 right-0 p-4">
                                            <h3 class="text-white font-bold text-sm mb-1 line-clamp-2">{{ $title }}</h3>
                                            <p class="text-white/90 text-xs">Klik untuk membaca</p>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </a>
                    @empty
                        <div class="col-span-full bg-white rounded-xl p-12 text-center shadow-md">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            <p class="mt-4 text-gray-500 font-medium">Tidak ada rekomendasi yang ditampilkan.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </main>

        {{-- Footer --}}
        <footer class="bg-gray-900 text-gray-300 mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    <div>
                        <div class="flex items-center space-x-2 mb-4">
                            <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-lg">
                                <span class="text-white font-bold text-xl">M</span>
                            </div>
                            <span class="text-xl font-bold text-white">MangaIndo</span>
                        </div>
                        <p class="text-gray-400 text-sm">
                            Platform membaca komik gratis dengan koleksi lengkap dari MangaIndo API.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-white font-semibold mb-4">Tautan Cepat</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#latest" class="hover:text-purple-400 transition-colors">Update Terbaru</a></li>
                            <li><a href="#recommended" class="hover:text-purple-400 transition-colors">Rekomendasi</a></li>
                            @auth
                                <li><a href="{{ url('/dashboard') }}" class="hover:text-purple-400 transition-colors">Dashboard</a></li>
                            @else
                                <li><a href="{{ route('login') }}" class="hover:text-purple-400 transition-colors">Masuk</a></li>
                            @endauth
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-white font-semibold mb-4">Tentang</h3>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <a href="https://api.mangadex.org/docs/" target="_blank" class="hover:text-purple-400 transition-colors">
                                    MangaDex API Docs
                                </a>
                            </li>
                            <li class="text-gray-400">Data oleh MangaDex API</li>
                            <li class="text-gray-400">Harap patuhi Acceptable Usage Policy</li>
                        </ul>
                    </div>
                </div>
                
                <div class="border-t border-gray-800 pt-8 text-center text-sm text-gray-400">
                    <p>© {{ date('Y') }} MangaIndo. Dibuat dengan Laravel v{{ Illuminate\Foundation\Application::VERSION }} • PHP v{{ PHP_VERSION }}</p>
                </div>
            </div>
        </footer>

        {{-- Smooth Scroll Script --}}
        <script>
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Debug: Log cover image URLs untuk troubleshooting
            @if(config('app.debug'))
            document.querySelectorAll('.manga-cover').forEach(img => {
                img.addEventListener('error', function() {
                    console.error('Failed to load cover image:', this.src);
                });
                img.addEventListener('load', function() {
                    console.log('Successfully loaded cover image:', this.src);
                });
            });
            @endif
        </script>
        <script>
            // Coba ukuran alternatif ketika gambar cover gagal dimuat
            function handleCoverError(img) {
                try {
                    if (!img || !img.src) return;

                    // Prevent infinite loop
                    if (img.dataset.triedFallback) {
                        img.onerror = null;
                        img.src = 'https://via.placeholder.com/300x400/9CA3AF/FFFFFF?text=No+Cover';
                        return;
                    }

                    img.dataset.triedFallback = '1';

                    // Jika URL mengandung ukuran seperti .256.jpg atau .512.jpg, coba ubah ke ukuran alternatif
                    if (img.src.includes('.256.jpg')) {
                        img.src = img.src.replace('.256.jpg', '.512.jpg');
                        return;
                    }

                    if (img.src.includes('.512.jpg')) {
                        img.src = img.src.replace('.512.jpg', '.256.jpg');
                        return;
                    }

                    // Jika tidak ada suffix ukuran, coba tambahkan .512.jpg
                    img.src = img.src + '.512.jpg';
                } catch (e) {
                    img.onerror = null;
                    img.src = 'https://via.placeholder.com/300x400/9CA3AF/FFFFFF?text=No+Cover';
                }
            }
        </script>
    </body>
</html>
