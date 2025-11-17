<?php

namespace App\Services;

use App\Repositories\TransactionRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    protected $transactionRepo;

    public function __construct(TransactionRepository $transactionRepo)
    {
        $this->transactionRepo = $transactionRepo;
    }

    /**
     * Lister toutes les transactions
     */
    public function listerTransactions()
    {
        return $this->transactionRepo->all();
    }

    /**
     * Lister les transactions d'un user spécifique
     */
    public function listerTransactionsDuUser(string $userId)
    {
        return $this->transactionRepo->findByUser($userId);
    }

    /**
     * Voir une transaction spécifique
     */
    public function voirTransaction(string $id)
    {
        $transaction = $this->transactionRepo->findById($id);
        if (! $transaction) {
            throw new Exception('transaction_not_found');
        }

        return $transaction;
    }

    /**
     * Créer une transaction
     */
    public function creerTransaction(array $data)
    {
        return DB::transaction(function () use ($data) {
            return $this->transactionRepo->create($data);
        });
    }

    /**
     * Récupérer toutes les transactions d'un compte
     */
    public function transactionsDuCompte(string $compteId)
    {
        return $this->transactionRepo->findByCompte($compteId);
    }
}
