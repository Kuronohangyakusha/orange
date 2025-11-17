<?php

namespace App\Jobs;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $client;

    /**
     * Create a new job instance.
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $compte = $this->client->comptes->first();

        // Générer un token de réinitialisation
        $token = \Illuminate\Support\Facades\Password::createToken($this->client);

        $data = [
            'nom' => $this->client->nom,
            'prenom' => $this->client->prenom,
            'email' => $this->client->email,
            'reset_link' => url('/reset-password?token='.$token.'&email='.urlencode($this->client->email)),
            'numero_compte' => $compte->numero_compte ?? null,
            'qr_code' => $compte->qr_code ?? null,
            'code_paiement' => $compte->code_paiement ?? null,
            'code_marchand' => $compte->code_marchand ?? null,
        ];

        Mail::send('emails.welcome', $data, function ($message) use ($data) {
            $message->to($data['email'], $data['nom'].' '.$data['prenom'])
                ->subject('Bienvenue chez Orange - Informations de votre compte');
        });
    }
}
