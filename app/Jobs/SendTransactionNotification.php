<?php

namespace App\Jobs;

use App\Mail\TransactionNotification;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

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

        // Envoyer la notification par email
        Mail::to($client->email)->send(new TransactionNotification($this->transaction));
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
