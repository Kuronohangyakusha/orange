<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Smtp2GoService
{
    public static function sendEmail($to, $subject, $body)
    {
        $response = Http::post('https://api.smtp2go.com/v3/email/send', [
            'api_key' => env('SMTP2GO_API_KEY'),
            'sender' => env('MAIL_FROM_ADDRESS'),
            'to' => [['email' => $to]],
            'subject' => $subject,
            'html_body' => $body,
        ]);

        return $response->json();
    }
}