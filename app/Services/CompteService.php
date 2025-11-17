<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Compte;
use App\Models\User;
use App\Repositories\ClientRepository;
use App\Repositories\CompteRepository;
use App\Repositories\TransactionRepository;
use Exception;
use Illuminate\Support\Str;

class CompteService
{
    protected $compteRepo;

    protected $transactionRepo;

    protected $clientRepo;

    public function __construct(
        CompteRepository $compteRepo,
        TransactionRepository $transactionRepo,
        ClientRepository $clientRepo
    ) {
        $this->compteRepo = $compteRepo;
        $this->transactionRepo = $transactionRepo;
        $this->clientRepo = $clientRepo;
    }

    /**
     * Créer un compte pour un client
     * Génère automatiquement un code de paiement
     * Si client est merchant, génère code_marchand
     */
    public function creerCompte(array $data)
    {
        // Trouver l'utilisateur
        $user = User::find($data['user_id']);
        if (!$user) {
            throw new Exception('user_not_found');
        }

        $data['code_paiement'] = strtoupper(Str::random(8)); // Code alphanumérique unique
        $data['solde'] = 10000; // Solde initial de 10000 FRC

        // Vérifier si c'est un marchand
        if ($user->role === 'merchant') {
            $data['code_marchand'] = strtoupper(Str::random(10)); // Code marchand unique
        }

        $compte = $this->compteRepo->create($data);

        // Créer une transaction d'ouverture de compte
        $this->transactionRepo->create([
            'compte_id' => $compte->id,
            'type' => 'reception',
            'montant' => 10000,
            'description' => 'Ouverture de compte - solde initial',
        ]);

        return $compte;
    }

    /**
     * Paiement par code marchand (transfert depuis source vers destination)
     */
    public function paiementParCode(string $sourceId, string $code, float $montant)
    {
        $source = $this->compteRepo->findById($sourceId);
        if (!$source) {
            throw new Exception('account_not_found');
        }

        // Vérifier que le client source n'est pas un marchand (marchands ne peuvent pas payer)
        if ($source->user->role === 'merchant') {
            throw new Exception('merchants_cannot_pay');
        }

        $dest = Compte::where('code_marchand', $code)->first();
        if (!$dest) {
            throw new Exception('merchant_code_invalid');
        }

        $soldeSource = $source->calculerSolde();
        if ($soldeSource < $montant) {
            throw new Exception('insufficient_balance');
        }

        // Créer les transactions
        $this->transactionRepo->create([
            'compte_id' => $source->id,
            'type' => 'paiement',
            'montant' => $montant,
            'code_marchand' => $code,
            'description' => "Paiement vers marchand {$code}",
        ]);

        $this->transactionRepo->create([
            'compte_id' => $dest->id,
            'type' => 'reception',
            'montant' => $montant,
            'description' => "Réception de paiement de {$source->user->name}",
        ]);

        // Mettre à jour les soldes
        $source->updateSolde();
        $dest->updateSolde();

        return [
            'source' => $source->fresh(['transactions']),
            'destination' => $dest->fresh(['transactions']),
        ];
    }

    /**
     * Transfert par numéro de téléphone
     */
    public function transfertParNumero(string $sourceCompteId, string $numeroTel, float $montant)
    {
        $source = $this->compteRepo->findById($sourceCompteId);
        if (! $source) {
            throw new Exception('account_not_found');
        }

        $destClient = $this->clientRepo->findByTelephone($numeroTel);
        if (! $destClient) {
            throw new Exception('recipient_not_found');
        }

        $dest = $destClient->comptes()->first(); // On prend le premier compte du client
        if (! $dest) {
            throw new Exception('account_not_found');
        }

        $soldeSource = $source->calculerSolde();
        if ($soldeSource < $montant) {
            throw new Exception('insufficient_balance');
        }

        // Créer les transactions
        $this->transactionRepo->create([
            'compte_id' => $source->id,
            'type' => 'transfert',
            'montant' => $montant,
            'numero_destinataire' => $numeroTel,
            'description' => "Transfert vers {$destClient->telephone}",
        ]);

        $this->transactionRepo->create([
            'compte_id' => $dest->id,
            'type' => 'reception',
            'montant' => $montant,
            'description' => "Transfert reçu de {$source->user->name}",
        ]);

        // Mettre à jour les soldes
        $source->updateSolde();
        $dest->updateSolde();

        return [
            'source' => $source->fresh(['transactions']),
            'destination' => $dest->fresh(['transactions']),
        ];
    }

    /**
     * Vérifier le solde
     */
    public function solde(string $compteId)
    {
        $compte = $this->compteRepo->findById($compteId);
        if (! $compte) {
            throw new Exception('account_not_found');
        }

        return $compte->solde ?? $compte->calculerSolde();
    }

    /**
     * Historique des transactions
     */
    public function historique(string $compteId)
    {
        $compte = $this->compteRepo->findById($compteId);
        if (! $compte) {
            throw new Exception('account_not_found');
        }

        return $compte->transactions()->orderBy('created_at', 'desc')->get();
    }

    /**
     * Récupérer tous les comptes d'un client
     */
    public function getComptes(Client $client)
    {
        return $client->comptes()->with('transactions')->get();
    }

    /**
     * Récupérer un compte spécifique d'un client
     */
    public function getCompteById(Client $client, string $compteId)
    {
        $compte = $client->comptes()->where('id', $compteId)->first();

        if (! $compte) {
            throw new Exception('account_not_found');
        }

        return $compte->load('transactions');
    }

    /**
     * Récupérer le solde d'un compte d'un client
     */
    public function getSolde(Client $client, string $compteId)
    {
        $compte = $this->getCompteById($client, $compteId);

        return $compte->solde ?? $compte->calculerSolde();
    }

    /**
     * Récupérer les transactions d'un compte d'un client avec pagination
     */
    public function getTransactions(Client $client, string $compteId, int $perPage = 15)
    {
        $compte = $this->getCompteById($client, $compteId);

        return $compte->transactions()->orderBy('created_at', 'desc')->paginate($perPage);
    }
}
