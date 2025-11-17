<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransactionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;

    /**
     * Create a new message instance.
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $typeLabel = $this->getTransactionTypeLabel();
        $montant = number_format($this->transaction->montant, 2, ',', ' ');

        return new Envelope(
            subject: "Orange - {$typeLabel} de {$montant}€",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.transaction_notification',
            with: [
                'transaction' => $this->transaction,
                'client' => $this->transaction->compte->user->client,
                'compte' => $this->transaction->compte,
                'typeLabel' => $this->getTransactionTypeLabel(),
                'isDebit' => $this->isDebitTransaction(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get human readable transaction type label.
     */
    private function getTransactionTypeLabel(): string
    {
        return match ($this->transaction->type) {
            'reception' => 'Réception',
            'transfert' => 'Transfert',
            'paiement' => 'Paiement',
            default => 'Transaction',
        };
    }

    /**
     * Check if transaction is a debit (reduces balance).
     */
    private function isDebitTransaction(): bool
    {
        return in_array($this->transaction->type, ['transfert', 'paiement']);
    }
}
