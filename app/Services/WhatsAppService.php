<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $url;
    private string $token;

    public function __construct()
    {
        $this->url = config('services.whatsapp.url');
        $this->token = config('services.whatsapp.token');
    }

    public function send(string $phone, string $message): bool
    {
        $phone = $this->normalizePhone($phone);

        $response = Http::withToken($this->token)
            ->post($this->url . '/send', [
                'phone' => $phone,
                'message' => $message,
            ]);

        if (! $response->successful() || ! ($response->json('success') ?? false)) {
            Log::error('WhatsApp send failed', [
                'phone' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        }

        return true;
    }

    private function normalizePhone(string $phone): string
    {
        // Strip everything except digits
        $digits = preg_replace('/\D/', '', $phone);

        // Add Argentina country code if missing
        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            $digits = '549' . ltrim($digits, '0');
        } elseif (strlen($digits) === 10) {
            $digits = '549' . $digits;
        }

        return $digits;
    }
}
