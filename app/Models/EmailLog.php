<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'recipient',
        'subject',
        'status',
        'error_message',
    ];

    public static function log($recipient, $subject, $status, $errorMessage = null)
    {
        try {
            return self::create([
                'recipient' => $recipient,
                'subject' => $subject,
                'status' => $status,
                'error_message' => $errorMessage,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to write email log: " . $e->getMessage());
        }
    }
}
