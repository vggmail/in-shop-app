<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $token;
    protected $phoneNumberId;
    protected $version;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->version = config('services.whatsapp.version', 'v19.0');
    }

    /**
     * Send a template message to a customer.
     *
     * @param string $to Recipient phone number (with country code, no +)
     * @param string $templateName Template name registered on Meta Dashboard
     * @param array $parameters Array of parameters for the template
     * @return array|bool
     */
    public function sendTemplateMessage($to, $templateName, $parameters = [])
    {
        if (!$this->token || !$this->phoneNumberId) {
            Log::error("WhatsApp Service: Token or Phone Number ID is missing.");
            return false;
        }

        // Clean phone number: remove any non-digit characters
        $to = preg_replace('/[^0-9]/', '', $to);
        
        // Ensure it has country code (Meta requires it)
        // If it's 10 digits, assume India (+91)
        if (strlen($to) == 10) {
            $to = '91' . $to;
        }

        $url = "https://graph.facebook.com/{$this->version}/{$this->phoneNumberId}/messages";

        $body = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => 'en_US'
                ],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => array_map(function ($value) {
                            return ['type' => 'text', 'text' => (string)$value];
                        }, $parameters)
                    ]
                ]
            ]
        ];

        try {
            $response = Http::withToken($this->token)->post($url, $body);

            if ($response->successful()) {
                Log::info("WhatsApp message sent successfully to {$to}. Response: " . $response->body());
                return $response->json();
            } else {
                Log::error("WhatsApp API Error: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("WhatsApp Service Exception: " . $e->getMessage());
            return false;
        }
    }
}
