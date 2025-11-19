<?php

namespace App\Jobs;

use App\Mail\TransactionNotification;
use App\Models\Transaction;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
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

        if (!$client || !$client->email) {
            Log::error('Client or email not found for transaction ' . $this->transaction->id);
            return;
        }

        try {
            Mail::to($client->email)->send(new TransactionNotification($this->transaction));
        } catch (\Exception $e) {
            Log::error('Failed to send transaction notification: ' . $e->getMessage());
        }
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
