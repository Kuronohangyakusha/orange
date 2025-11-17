<?php

namespace App\Services;

use App\Jobs\SendWelcomeEmail;
use App\Models\Client;
use App\Models\Compte;
use App\Repositories\ClientRepository;
use App\Services\CompteService;
use Exception;
use Illuminate\Support\Facades\Hash;

class ClientService
{
    protected $clientRepo;
    protected $compteService;

    public function __construct(ClientRepository $clientRepo, CompteService $compteService)
    {
        $this->clientRepo = $clientRepo;
        $this->compteService = $compteService;
    }

    /**
     * Récupérer tous les clients avec leurs comptes
     */
    public function allClients()
    {
        return $this->clientRepo->all();
    }

    /**
     * Créer un client et lui créer automatiquement un compte courant
     */
    public function createClient(array $data)
    {
        $data['role'] = $data['role'] ?? 'client'; // Default to client

        // Création du client
        $client = $this->clientRepo->create($data);

        // Création d'un compte courant
        $this->compteService->creerCompte([
            'user_id' => $client->user_id,
            'type' => 'courant',
        ]);

        // Envoyer l'email de bienvenue avec lien de réinitialisation
        SendWelcomeEmail::dispatch($client);

        // Retourne le client
        return $client;
    }

    /**
     * Créer un marchand et lui créer automatiquement un compte courant
     */
    public function createMerchant(array $data)
    {
        $data['role'] = $data['role'] ?? 'merchant'; // Default to merchant

        // Création du marchand
        $merchant = $this->clientRepo->create($data); // Utilise le même repo pour les marchands

        // Création d'un compte courant
        $this->compteService->creerCompte([
            'user_id' => $merchant->user_id,
            'type' => 'courant',
        ]);

        // Envoyer l'email de bienvenue avec lien de réinitialisation
        SendWelcomeEmail::dispatch($merchant);

        // Retourne le marchand
        return $merchant;
    }

    /**
     * Récupérer un client par ID
     */
    public function getClientById(string $id)
    {
        return $this->clientRepo->findById($id);
    }

    /**
     * Mettre à jour un client
     */
    public function updateClient(string $id, array $data)
    {
        return $this->clientRepo->update($id, $data);
    }

    /**
     * Supprimer un client
     */
    public function deleteClient(string $id)
    {
        return $this->clientRepo->delete($id);
    }

    public function voirClient(string $id)
    {
        $client = $this->clientRepo->findById($id);
        if (! $client) {
            throw new Exception('client_not_found');
        }

        return $client;
    }
}
