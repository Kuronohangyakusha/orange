<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ClientService;
use App\Traits\ApiResponses;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    use ApiResponses;

    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function index()
    {
        $clients = $this->clientService->allClients();

        return $this->successResponse($clients, 'Liste des clients');
    }

    public function store(Request $request)
    {
        $data = $this->validateByKey('client_store', $request->all());

        // Créer l'utilisateur si user_id non fourni
        if (!isset($data['user_id'])) {
            $user = User::create([
                'name' => $data['nom'] . ' ' . $data['prenom'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'] ?? 'client',
            ]);
            $data['user_id'] = $user->id;
        }

        try {
            $client = $this->clientService->createClient($data);

            return $this->successResponse($client, 'Client créé avec compte', [], 201);
        } catch (Exception $e) {
            return $this->errorResponseByKey('client_creation_failed', 400);
        }
    }

    public function profile(Request $request)
    {
        $client = $request->user()->load('comptes');

        return $this->successResponse($client, 'Profil récupéré');
    }

    /**
     *     path="/api/v1/clients/{id}",
     *     summary="Get client by ID",
     *     description="Get a specific client by their ID",
     *     operationId="getClient",
     *     tags={"Clients"},
     *     security={{"sanctum":{}}},
     *
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         description="Client ID"
     *     ),
     *
     *         response=200,
     *         description="Client retrieved successfully",
     *
     *
     *         )
     *     ),
     *
     *         response=404,
     *         description="Client not found",
     *
     *
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $client = $this->clientService->voirClient($id);

            return $this->successResponse($client, 'Client trouvé');
        } catch (Exception $e) {
            return $this->errorResponseByKey('client_not_found', 404);
        }
    }

    /**
     *     path="/api/v1/clients/me",
     *     summary="Update authenticated client profile",
     *     description="Update the currently authenticated client's information",
     *     operationId="updateMyProfile",
     *     tags={"Clients"},
     *     security={{"sanctum":{}}},
     *
     *
     *
     *         )
     *     ),
     *
     *         response=200,
     *         description="Profile updated successfully",
     *
     *
     *         )
     *     )
     * )
     */
    public function updateProfile(Request $request)
    {
        $client = $request->user();
        $rules = $this->validationRules['client_update'];
        // Ajouter les règles d'unicité avec l'ID du client connecté
        $rules['email'] = "sometimes|email|unique:clients,email,{$client->id}";
        $rules['telephone'] = "sometimes|string|unique:clients,telephone,{$client->id}";

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $this->errorMessages['validation_failed'],
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        try {
            $client = $this->clientService->updateClient($client->id, $data);

            return $this->successResponse($client, 'Profil mis à jour');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     *     path="/api/v1/clients/me",
     *     summary="Delete authenticated client account",
     *     description="Delete the currently authenticated client's account and all associated data",
     *     operationId="deleteMyAccount",
     *     tags={"Clients"},
     *     security={{"sanctum":{}}},
     *
     *         response=204,
     *         description="Account deleted successfully",
     *
     *
     *         )
     *     )
     * )
     */
    public function deleteProfile(Request $request)
    {
        $client = $request->user();

        try {
            $this->clientService->deleteClient($client->id);

            // Supprimer l'utilisateur associé si pas d'autres clients/merchants
            if ($client->user->clients()->count() === 0 && $client->user->merchants()->count() === 0) {
                $client->user->delete();
            }

            return $this->successResponse(null, 'Compte supprimé', [], 204);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
