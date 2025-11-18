<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Illuminate\Support\Facades\Http;

class Smtp2GoTransport implements TransportInterface
{
    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
        $to = $envelope->getRecipients()[0]->getAddress();
        $from = $envelope->getSender()->getAddress();
        $subject = $message->getHeaders()->get('subject')?->getBodyAsString() ?? '';
        $body = $message->getBody()->bodyToString();

        $response = Http::post('https://api.smtp2go.com/v3/email/send', [
            'api_key' => env('SMTP2GO_API_KEY'),
            'sender' => $from,
            'to' => [['email' => $to]],
            'subject' => $subject,
            'html_body' => $body,
        ]);

        if ($response->successful()) {
            return new SentMessage($message, $envelope);
        }

        throw new \Exception('SMTP2GO API error: ' . $response->body());
    }

    public function __toString(): string
    {
        return 'smtp2go';
    }
}