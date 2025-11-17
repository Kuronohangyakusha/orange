<?php

namespace App\Observers;

use App\Jobs\SendTransactionNotification;
use App\Models\Transaction;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        // Mettre à jour le solde du compte après création de transaction
        $transaction->compte->updateSolde();

        // Envoyer une notification par email au client
        SendTransactionNotification::dispatch($transaction);
    }
}
