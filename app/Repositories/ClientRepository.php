<?php

namespace App\Repositories;

use App\Models\Client;

class ClientRepository
{
    protected $model;

    public function __construct(Client $client)
    {
        $this->model = $client;
    }

    public function all()
    {
        return $this->model->with('comptes')->get();
    }

    public function findById(string $id)
    {
        return $this->model->with('comptes')->findOrFail($id);
    }

    /**
     * Chercher un client par son email
     */
    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Chercher un client par son numéro de téléphone
     */
    public function findByTelephone(string $telephone)
    {
        return $this->model->where('telephone', $telephone)->first();
    }

    /**
     * Créer un nouveau client
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Mettre à jour un client
     */
    public function update(string $id, array $data)
    {
        $client = $this->findById($id);
        $client->update($data);

        return $client;
    }

    /**
     * Supprimer un client
     */
    public function delete(string $id)
    {
        $client = $this->findById($id);

        return $client->delete();
    }
}
