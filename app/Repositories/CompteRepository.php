<?php

namespace App\Repositories;

use App\Models\Compte;

class CompteRepository
{
    protected $compte;

    public function __construct(Compte $compte)
    {
        $this->compte = $compte;
    }

    public function all()
    {
        return $this->compte->with('user')->get();
    }

    public function findById(string $id)
    {
        return $this->compte->with('transactions', 'user')->findOrFail($id);
    }

    public function findByNumero(string $numero)
    {
        return $this->compte->where('numero_compte', $numero)->first();
    }

    public function findByCodePaiement(string $code)
    {
        return $this->compte->where('code_paiement', $code)->first();
    }

    public function findByUser(string $userId)
    {
        return $this->compte->where('user_id', $userId)->get();
    }

    public function create(array $data)
    {
        return $this->compte->create($data);
    }

    public function update(string $id, array $data)
    {
        $compte = $this->findById($id);
        $compte->update($data);

        return $compte;
    }

    public function delete(string $id)
    {
        $compte = $this->findById($id);

        return $compte->delete();
    }
}
