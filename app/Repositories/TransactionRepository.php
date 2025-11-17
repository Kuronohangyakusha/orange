<?php

namespace App\Repositories;

use App\Models\Transaction;

class TransactionRepository
{
    protected $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function all()
    {
        return $this->transaction->with('compte')->latest()->get();
    }

    public function findById(string $id)
    {
        return $this->transaction->findOrFail($id);
    }

    public function findByCompte(string $compteId)
    {
        return $this->transaction
            ->where('compte_id', $compteId)
            ->latest();
    }

    public function findByUser(string $userId)
    {
        return $this->transaction
            ->whereHas('compte', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with('compte')
            ->latest()
            ->get();
    }

    public function create(array $data)
    {
        return $this->transaction->create($data);
    }
}
