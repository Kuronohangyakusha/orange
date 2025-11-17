<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\Compte;

class ComptePolicy
{
    /**
     * Determine whether the client can view any comptes.
     */
    public function viewAny(Client $client): bool
    {
        return true; // Tous les clients peuvent voir leurs comptes
    }

    /**
     * Determine whether the client can view the compte.
     */
    public function view(Client $client, Compte $compte): bool
    {
        return $compte->user_id === $client->user_id;
    }

    /**
     * Determine whether the client can create comptes.
     */
    public function create(Client $client): bool
    {
        return true; // Les clients peuvent créer leurs propres comptes
    }
}
