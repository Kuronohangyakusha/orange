<?php

namespace App\Traits;

trait ApiResponses
{
    // Messages d'erreur centralisés
    protected $errorMessages = [
        // Authentification
        'invalid_credentials' => 'Email ou mot de passe incorrect.',
        'unauthorized' => 'Accès non autorisé. Vous n\'avez pas les permissions nécessaires.',
        'token_expired' => 'Votre session a expiré. Veuillez vous reconnecter.',
        'token_invalid' => 'Token d\'authentification invalide.',

        // Clients
        'client_not_found' => 'Client introuvable.',
        'client_email_exists' => 'Un client avec cet email existe déjà.',
        'client_phone_exists' => 'Un client avec ce numéro de téléphone existe déjà.',
        'client_creation_failed' => 'Échec de la création du client.',

        // Comptes
        'account_not_found' => 'Compte introuvable.',
        'account_access_denied' => 'Vous n\'avez pas accès à ce compte.',
        'account_creation_failed' => 'Échec de la création du compte.',
        'insufficient_balance' => 'Solde insuffisant pour effectuer cette opération.',
        'invalid_account_type' => 'Type de compte invalide.',

        // Transactions
        'transaction_not_found' => 'Transaction introuvable.',
        'transaction_creation_failed' => 'Échec de la création de la transaction.',
        'invalid_transaction_type' => 'Type de transaction invalide.',
        'invalid_amount' => 'Montant invalide.',
        'payment_code_invalid' => 'Code de paiement invalide.',
        'recipient_not_found' => 'Destinataire introuvable.',

        // Validation
        'validation_failed' => 'Les données fournies sont invalides.',
        'required_field' => 'Ce champ est obligatoire.',
        'invalid_email' => 'Format d\'email invalide.',
        'invalid_phone' => 'Format de numéro de téléphone invalide.',
        'password_too_short' => 'Le mot de passe doit contenir au moins 6 caractères.',

        // Serveur
        'server_error' => 'Une erreur interne du serveur s\'est produite.',
        'service_unavailable' => 'Service temporairement indisponible.',
    ];

    // Règles de validation centralisées
    protected $validationRules = [
        // Authentification
        'login' => [
            'email' => 'required|email',
            'password' => 'required|string',
        ],

        // Clients
        'client_store' => [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'telephone' => 'required|string|unique:clients,telephone',
            'password' => 'required|string|min:6',
        ],
        'client_update' => [
            'nom' => 'sometimes|string|max:255',
            'prenom' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'telephone' => 'sometimes|string',
        ],

        // Comptes
        'compte_store' => [
            'type' => 'required|string|in:courant,cheque,epargne',
        ],
        'compte_paiement_code' => [
            'code' => 'required|string',
            'montant' => 'required|numeric|min:0.01',
        ],
        'compte_transfert_tel' => [
            'numero_tel' => 'required|string|exists:clients,telephone',
            'montant' => 'required|numeric|min:0.01',
        ],

        // Transactions
        'transaction_store' => [
            'compte_id' => 'required|exists:comptes,id',
            'type' => 'required|string|in:reception,transfert,paiement',
            'montant' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ],
    ];

    protected function successResponse($data, $message = null, $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponseWithData($data, $message, $code)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse($message, $code)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }

    protected function errorResponseByKey($key, $code = 400, $customMessage = null)
    {
        $message = $customMessage ?: ($this->errorMessages[$key] ?? 'Une erreur inattendue s\'est produite.');

        return $this->errorResponse($message, $code);
    }

    /**
     * Valider les données selon les règles centralisées
     */
    protected function validateByKey($key, $data)
    {
        if (! isset($this->validationRules[$key])) {
            throw new \InvalidArgumentException("Règle de validation '{$key}' introuvable.");
        }

        $validator = \Illuminate\Support\Facades\Validator::make($data, $this->validationRules[$key]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $validator->validated();
    }

    protected function paginatedResponse($data, $links = [], $message = null, $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'total' => $data->total(),
                'count' => $data->count(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'total_pages' => $data->lastPage(),
                'links' => $links,
            ],
        ], $code);
    }
}
