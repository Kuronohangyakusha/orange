<?php

namespace App\Services;

use App\Mail\OtpEmail;
use App\Models\Client;
use App\Services\Smtp2GoService;
use Carbon\Carbon;

class AuthService
{
    /**
     * Génère un OTP pour le client
     */
    public function generateOtp(Client $client): string
    {
        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        $client->update([
            'otp_code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10), // OTP valide 10 minutes
        ]);

        return $otp;
    }

    /**
     * Envoie l'OTP par email
     */
    public function sendOtpEmail(Client $client, string $otp): void
    {
        $emailContent = (new OtpEmail($client, $otp))->render();
        Smtp2GoService::sendEmail($client->email, 'Code OTP de vérification', $emailContent);
    }

    /**
     * Vérifie l'OTP et retourne le client si valide
     */
    public function verifyOtp(string $phone, string $otp): ?Client
    {
        $client = Client::where('telephone', $phone)->first();

        if (! $client ||
            ! $client->otp_code ||
            $client->otp_code !== $otp ||
            ! $client->otp_expires_at ||
            $client->otp_expires_at->isPast()) {
            return null;
        }

        // Invalider l'OTP après utilisation
        $client->update([
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        return $client;
    }
}
