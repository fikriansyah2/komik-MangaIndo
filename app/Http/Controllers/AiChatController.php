<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class AiChatController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $userMessage = $request->input('message');
        $client = new Client();

        // Try OpenAI first
        $reply = $this->tryOpenAI($client, $userMessage);

        // If OpenAI fails, fallback to simple offline responses
        if ($reply === null) {
            $reply = $this->getOfflineResponse($userMessage);
            Log::info('Using offline fallback response');
        }

        return response()->json(['reply' => trim($reply)]);
    }

    private function tryOpenAI($client, $userMessage)
    {
        $apiKey = env('OPENAI_API_KEY');
        $model = env('OPENAI_MODEL', 'gpt-5-nano');

        if (empty($apiKey)) {
            Log::info('OpenAI API key not configured, skipping OpenAI.');
            return null;
        }

        try {
            $resp = $client->post('https://api.openai.com/v1/responses', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'input' => $userMessage,
                    'store' => true,
                ],
                'timeout' => 15,
            ]);

            $body = json_decode((string) $resp->getBody(), true);

            if (isset($body['output'])) {
                Log::info('OpenAI response successful');
                return $body['output'];
            } elseif (isset($body['text'])) {
                Log::info('OpenAI response successful');
                return $body['text'];
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('OpenAI failed, will use offline fallback: ' . $e->getMessage());
            return null;
        }
    }

    private function getOfflineResponse($userMessage)
    {
        $lower = strtolower($userMessage);
        
        // Simple keyword-based responses (Indonesian)
        $keywords = [
            'halo|hi|hello|hey' => 'Halo! Apa kabar? Saya di sini untuk membantu.',
            'siapa|who are' => 'Saya adalah AI chatbot untuk website komik ini. Saya siap membantu Anda!',
            'manga|komik|anime' => 'Saya suka membahas manga dan anime! Apa manga favorit Anda?',
            'terima kasih|thanks|thank you' => 'Sama-sama! Senang bisa membantu. Ada pertanyaan lagi?',
            'bye|goodbye|sampai|dadah' => 'Sampai jumpa! Terima kasih telah berbincang dengan saya.',
            'apa itu|what is|explain' => 'Bisa jelaskan lebih detail tentang apa yang ingin Anda tahu? Saya akan coba bantu.',
            'mana|where|dimana' => 'Silakan cek di menu utama atau gunakan fitur pencarian untuk menemukan apa yang Anda cari.',
            'bagaimana|how|cara' => 'Tergantung apa yang Anda butuhkan. Bisa Anda jelaskan lebih detail?',
            'waktu|time|jam' => 'Sekarang adalah waktu yang tepat untuk membaca manga favorit Anda!',
            'bintang|star|rating' => 'Rating dan ulasan sangat membantu orang lain menemukan manga terbaik.',
        ];

        foreach ($keywords as $pattern => $response) {
            if (preg_match('/(' . $pattern . ')/i', $lower)) {
                return $response;
            }
        }

        // Default response
        $defaults = [
            'Pertanyaan menarik! Saya coba bantu sebaik mungkin.',
            'Maaf, saya tidak sepenuhnya mengerti. Bisa Anda rephrase?',
            'Itu adalah topik yang menarik. Apa yang ingin Anda ketahui lebih lanjut?',
            'Saya sedang offline mode sekarang. Coba lagi nanti atau gunakan pencarian di website.',
        ];

        return $defaults[array_rand($defaults)];
    }
}
