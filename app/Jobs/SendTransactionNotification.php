<?php

namespace App\Jobs;

use App\Mail\TransactionNotification;
use App\Models\Transaction;
use App\Services\Smtp2GoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTransactionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $transaction;

    /**
     * Create a new job instance.
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $client = $this->transaction->compte->user->client;

        // Rendre le contenu HTML de l'email
        $emailContent = (new TransactionNotification($this->transaction))->render();

        // Envoyer via SMTP2GO API
        Smtp2GoService::sendEmail($client->email, 'Notification de Transaction', $emailContent);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'transaction-notification',
            'user:'.$this->transaction->compte->user_id,
            'compte:'.$this->transaction->compte_id,
        ];
    }
}
