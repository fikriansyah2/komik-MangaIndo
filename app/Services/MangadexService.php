<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException; // Penting untuk menangkap error HTTP

class MangadexService
{
    protected $baseUrl = 'https://api.mangadex.org/';

    /**
     * Mengambil daftar Manga (komik) terbaru.
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLatestManga(int $limit = 12, int $offset = 0): array
    {
        try {
            // Build query string manual untuk format yang benar
            $queryParams = http_build_query([
                'limit' => min($limit, 100),
                'offset' => max($offset, 0),
                'order[updatedAt]' => 'desc',
            ]);
            
            // Tambahkan multiple values dengan format yang benar
            $queryParams .= '&includes[]=cover_art';
            $queryParams .= '&contentRating[]=safe';
            $queryParams .= '&contentRating[]=suggestive';
            
            $url = $this->baseUrl . 'manga?' . $queryParams;
            
            $response = Http::timeout(10)->get($url);

            $response->throw();
            $data = $response->json();
            
            // Log untuk debugging
            Log::info("Latest manga response:", [
                'total' => $data['total'] ?? 0,
                'data_count' => count($data['data'] ?? []),
                'included_count' => count($data['included'] ?? []),
            ]);
            
            return $data;

        } catch (RequestException $e) {
            Log::error("Gagal mengambil Manga terbaru dari Mangadex: " . $e->getMessage());
            if ($e->response) {
                Log::error("Response status: " . $e->response->status());
                Log::error("Response body: " . $e->response->body());
            }
            return ['data' => [], 'total' => 0, 'included' => []];
        }
    }

    /**
     * Mengambil daftar rekomendasi manga (berdasarkan popularitas).
     * @param int $limit
     * @return array
     */
    public function getRecommendedManga(int $limit = 8): array
    {
        try {
            // Build query string manual untuk format yang benar
            $queryParams = http_build_query([
                'limit' => min($limit, 100),
                'order[followedCount]' => 'desc',
            ]);
            
            // Tambahkan multiple values dengan format yang benar
            $queryParams .= '&includes[]=cover_art';
            $queryParams .= '&contentRating[]=safe';
            $queryParams .= '&contentRating[]=suggestive';
            
            $url = $this->baseUrl . 'manga?' . $queryParams;
            
            $response = Http::timeout(10)->get($url);

            $response->throw();
            $data = $response->json();
            
            // Log untuk debugging
            Log::info("Recommended manga response:", [
                'total' => $data['total'] ?? 0,
                'data_count' => count($data['data'] ?? []),
                'included_count' => count($data['included'] ?? []),
            ]);
            
            return $data;

        } catch (RequestException $e) {
            Log::error("Gagal mengambil rekomendasi Manga dari Mangadex: " . $e->getMessage());
            if ($e->response) {
                Log::error("Response status: " . $e->response->status());
                Log::error("Response body: " . $e->response->body());
            }
            return ['data' => [], 'included' => []];
        }
    }
    
    /**
     * Mendapatkan daftar chapter untuk Manga tertentu.
     * MENGHILANGKAN filter bahasa untuk memastikan chapter Catastrophic Love muncul.
     * @param string $mangaId
     * @param int $limit Maksimal 100 (batas API MangaDex)
     * @param int $offset Untuk pagination
     * @return array
     */
    public function getMangaChapters(string $mangaId, int $limit = 100, int $offset = 0): array
    {
        try {
            $limit = min($limit, 100);
            
            $params = [
                'limit' => $limit,
                'offset' => $offset,
                'order[chapter]' => 'asc',
                'order[volume]' => 'asc',
                
                // HAPUS filter 'translatedLanguage' untuk mencari chapter dalam bahasa apa pun
                // Jika Anda ingin Inggris & Indonesia, gunakan: 'translatedLanguage' => ['en', 'id'],
                
                'includes' => ['cover_art'],
                'contentRating' => ['safe', 'suggestive'],
            ];
            
            $response = Http::timeout(10)->get($this->baseUrl . 'manga/' . $mangaId . '/feed', $params);

            $response->throw();
            $data = $response->json();
            
            Log::info("Chapter data untuk Manga ID {$mangaId}:", [
                'total' => $data['total'] ?? 0,
                'count' => count($data['data'] ?? []),
                'limit' => $limit,
                'offset' => $offset,
                'response_status' => $response->status(),
            ]);
            
            return $data;

        } catch (RequestException $e) {
            Log::error("Gagal mengambil chapter untuk Manga ID {$mangaId}: " . $e->getMessage());
            if ($e->response) {
                Log::error("Response status: " . $e->response->status());
                Log::error("Response body: " . $e->response->body());
            }
            return ['data' => [], 'total' => 0];
        }
    }
    
    /**
     * Mendapatkan detail manga termasuk cover art.
     * @param string $mangaId
     * @return array|null Response lengkap dari API termasuk data dan included
     */
    public function getMangaDetails(string $mangaId): ?array
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl . 'manga/' . $mangaId, [
                // PERBAIKAN: Format array konsisten
                'includes' => ['cover_art', 'author', 'artist'],
            ]);

            $response->throw();
            $data = $response->json();
            
            Log::info("Manga details untuk ID {$mangaId}:", [
                'has_data' => isset($data['data']),
                'has_included' => isset($data['included']),
                'included_count' => count($data['included'] ?? []),
            ]);
            
            return $data;

        } catch (RequestException $e) {
            Log::error("Gagal mengambil detail manga untuk Manga ID {$mangaId}: " . $e->getMessage());
            if ($e->response) {
                Log::error("Response status: " . $e->response->status());
                Log::error("Response body: " . $e->response->body());
            }
            return null;
        }
    }
    
    /**
     * Mendapatkan URL cover image untuk manga.
     * Sesuai dokumentasi: https://api.mangadex.org/docs/03-manga/covers/
     * Format: https://uploads.mangadex.org/covers/:manga-id/:cover-filename.{256, 512}.jpg
     * 
     * Penting: filename lengkap dengan extension harus digunakan, lalu ditambahkan .{size}.jpg
     * Contoh: filename.png.256.jpg atau filename.jpg.512.jpg
     */
    public function getCoverImageUrl(array $mangaData, ?array $included = null, ?string $size = '512'): ?string
    {
        $mangaId = $mangaData['id'] ?? null;
        if (!$mangaId) {
            Log::warning("Manga ID tidak ditemukan dalam data");
            return null;
        }

        // Cari cover_art relationship
        $relationships = $mangaData['relationships'] ?? [];
        $coverArtId = null;
        $coverFileName = null;
        
        // Langkah 1: Cek apakah fileName ada langsung di relationships.attributes (untuk API v5.12+)
        foreach ($relationships as $relationship) {
            if (($relationship['type'] ?? '') === 'cover_art') {
                $coverArtId = $relationship['id'] ?? null;
                
                // Cek apakah attributes ada langsung di relationship (API v5.12+)
                if (isset($relationship['attributes']['fileName'])) {
                    $coverFileName = $relationship['attributes']['fileName'];
                    Log::debug("Cover filename ditemukan di relationships.attributes: {$coverFileName} untuk Manga ID: {$mangaId}");
                    break;
                }
            }
        }

        // Langkah 2: Jika tidak ditemukan di relationships, cari di included data
        if (!$coverFileName && $included && is_array($included) && $coverArtId) {
            foreach ($included as $item) {
                if (isset($item['type']) && $item['type'] === 'cover_art' && 
                    isset($item['id']) && $item['id'] === $coverArtId) {
                    $coverFileName = $item['attributes']['fileName'] ?? null;
                    if ($coverFileName) {
                        Log::debug("Cover filename ditemukan di included: {$coverFileName} untuk Manga ID: {$mangaId}");
                        break;
                    }
                }
            }
        }
        
        if (!$coverFileName) {
            Log::warning("Cover filename tidak ditemukan untuk Manga ID {$mangaId}, Cover Art ID: {$coverArtId}");
            Log::debug("Relationships count: " . count($relationships));
            Log::debug("Included count: " . count($included ?? []));

            // Fallback: panggil endpoint /cover?manga[]={$mangaId} untuk mencoba mendapatkan fileName
            try {
                $resp = Http::timeout(8)->get($this->baseUrl . 'cover', [
                    'manga[]' => $mangaId,
                    'limit' => 1,
                ]);
                $resp->throw();
                $respJson = $resp->json();
                $first = $respJson['data'][0] ?? null;
                if ($first && isset($first['attributes']['fileName'])) {
                    $coverFileName = $first['attributes']['fileName'];
                    Log::info("Cover filename ditemukan via /cover endpoint: {$coverFileName} untuk Manga ID: {$mangaId}");
                }
            } catch (RequestException $e) {
                Log::error("Fallback cover fetch gagal untuk Manga ID {$mangaId}: " . $e->getMessage());
                if ($e->response) {
                    Log::debug("Fallback response status: " . $e->response->status());
                    Log::debug("Fallback response body: " . $e->response->body());
                }
            } catch (\Exception $e) {
                Log::error("Unexpected error saat fallback cover fetch untuk Manga ID {$mangaId}: " . $e->getMessage());
            }

            if (!$coverFileName) {
                return null;
            }
        }

        // Build URL sesuai dokumentasi MangaDex
        // Format yang diharapkan: https://uploads.mangadex.org/covers/:manga-id/:cover-filename.{size}.jpg
        // Beberapa filename mengandung karakter khusus sehingga harus di-encode
        $encodedMangaId = rawurlencode((string) $mangaId);
        $encodedFileName = rawurlencode($coverFileName);

        // Kembalikan URL dasar (tanpa suffix ukuran). View/JS akan menambahkan
        // suffix `.256.jpg` atau `.512.jpg` bila ingin thumbnail.
        $baseUrl = 'https://uploads.mangadex.org/covers/' . $encodedMangaId . '/' . $encodedFileName;

        Log::debug("Cover base URL generated untuk Manga ID {$mangaId}: {$baseUrl}");
        return $baseUrl;
    }
    
    /**
     * Mendapatkan URL gambar untuk chapter tertentu.
     * Menggunakan endpoint /at-home/server/{chapterId} dari MangaDex API
     * @param string $chapterId
     * @return array Array of image URLs
     */
    public function getChapterImages(string $chapterId): array
    {
        try {
            $response = Http::timeout(15)->get($this->baseUrl . 'at-home/server/' . $chapterId);
            
            $response->throw();
            $chapterData = $response->json();
            
            Log::info("Chapter server response untuk Chapter ID {$chapterId}:", [
                'has_chapter' => isset($chapterData['chapter']),
                'has_baseUrl' => isset($chapterData['baseUrl']),
                'baseUrl' => $chapterData['baseUrl'] ?? 'N/A',
            ]);
            
            if (!isset($chapterData['chapter']) || !isset($chapterData['baseUrl'])) {
                Log::warning("Data chapter tidak valid untuk Chapter ID {$chapterId}");
                Log::debug("Response data: " . json_encode($chapterData));
                return [];
            }
            
            $hash = $chapterData['chapter']['hash'] ?? null;
            $baseUrl = $chapterData['baseUrl'];
            
            if (!$hash) {
                Log::error("Hash tidak ditemukan untuk Chapter ID {$chapterId}");
                return [];
            }
            
            // Coba gunakan data (full quality) terlebih dahulu, jika tidak ada gunakan dataSaver
            $data = $chapterData['chapter']['data'] ?? $chapterData['chapter']['dataSaver'] ?? [];
            $useDataSaver = !isset($chapterData['chapter']['data']);
            
            Log::info("Chapter image data untuk Chapter ID {$chapterId}:", [
                'hash' => $hash,
                'image_count' => count($data),
                'using_dataSaver' => $useDataSaver,
                'baseUrl' => $baseUrl,
            ]);
            
            if (empty($data)) {
                Log::warning("Tidak ada data gambar untuk Chapter ID {$chapterId}");
                return [];
            }
            
            $imageUrls = [];
            $path = $useDataSaver ? '/data-saver/' : '/data/';
            
            foreach ($data as $imageFileName) {
                // Format URL: {baseUrl}/data/{hash}/{filename} atau {baseUrl}/data-saver/{hash}/{filename}
                $imageUrl = rtrim($baseUrl, '/') . $path . $hash . '/' . $imageFileName;
                $imageUrls[] = $imageUrl;
            }
            
            Log::info("Generated " . count($imageUrls) . " image URLs untuk Chapter ID {$chapterId}");
            if (count($imageUrls) > 0) {
                Log::debug("First image URL: " . $imageUrls[0]);
            }
            
            return $imageUrls;
            
        } catch (RequestException $e) {
            Log::error("Gagal mengambil gambar untuk Chapter ID {$chapterId}: " . $e->getMessage());
            if ($e->response) {
                Log::error("Response status: " . $e->response->status());
                Log::error("Response body: " . $e->response->body());
            }
            return [];
        } catch (\Exception $e) {
            Log::error("Unexpected error saat mengambil gambar Chapter ID {$chapterId}: " . $e->getMessage());
            return [];
        }
    }
}