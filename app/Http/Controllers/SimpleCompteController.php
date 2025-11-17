<?php

namespace App\Http\Controllers;

use App\Services\CompteService;
use App\Traits\ApiResponses;
use Exception;
use Illuminate\Http\Request;

class SimpleCompteController extends Controller
{
    use ApiResponses;

    protected $compteService;

    public function __construct(CompteService $compteService)
    {
        $this->compteService = $compteService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comptes",
     *     summary="Get authenticated client's accounts",
     *     description="Get all accounts belonging to the authenticated client",
     *     operationId="getMyAccounts",
     *     tags={"Comptes"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Accounts retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Comptes récupérés"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="client", ref="#/components/schemas/Client"),
     *                 @OA\Property(property="comptes", type="array", @OA\Items(ref="#/components/schemas/Compte"))
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Utilisateur non authentifié', 401);
        }

        try {
            $comptes = $this->compteService->getComptes($user->client);
            $data = [
                'user' => $user,
                'comptes' => $comptes,
            ];
            return $this->successResponse($data, 'Comptes récupérés');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comptes/{id}/solde",
     *     summary="Get balance for specific account",
     *     description="Get balance for a specific account by ID (user must own the account)",
     *     operationId="getAccountBalanceById",
     *     tags={"Comptes"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Account ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Balance retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Solde récupéré avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="compte_id", type="string", format="uuid"),
     *                 @OA\Property(property="numero_compte", type="string"),
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="solde", type="number", format="float")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Access denied",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Accès non autorisé à ce compte")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Account not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Compte introuvable")
     *         )
     *     )
     * )
     */
    public function soldeParId(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Utilisateur non authentifié', 401);
        }

        try {
            // Vérifier que le compte appartient au client connecté
            $compte = $user->comptes()->where('id', $id)->first();
            if (!$compte) {
                return $this->errorResponse('Accès non autorisé à ce compte', 403);
            }

            $solde = $this->compteService->solde($compte->id);

            $data = [
                'compte_id' => $compte->id,
                'numero_compte' => $compte->numero_compte,
                'type' => $compte->type,
                'solde' => $solde,
            ];

            return $this->successResponse($data, 'Solde récupéré avec succès');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comptes/{id}/historique",
     *     summary="Get transaction history for specific account",
     *     description="Get all transactions for a specific account by ID (user must own the account)",
     *     operationId="getAccountTransactionHistoryById",
     *     tags={"Comptes"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Account ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Transaction history retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Historique des transactions récupéré"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Transaction"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Access denied",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Accès non autorisé à ce compte")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Account not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Compte introuvable")
     *         )
     *     )
     * )
     */
    public function historiqueParId(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Utilisateur non authentifié', 401);
        }

        try {
            // Vérifier que le compte appartient au client connecté
            $compte = $user->comptes()->where('id', $id)->first();
            if (!$compte) {
                return $this->errorResponse('Accès non autorisé à ce compte', 403);
            }

            $transactions = $this->compteService->historique($compte->id);

            return $this->successResponse($transactions, 'Historique des transactions récupéré');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comptes/solde",
     *     summary="Get account balances",
     *     description="Get balances for all accounts of the authenticated user",
     *     operationId="getAccountBalances",
     *     tags={"Comptes"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Balances retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Soldes récupérés avec succès"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="compte_id", type="string", format="uuid"),
     *                 @OA\Property(property="numero_compte", type="string"),
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="solde", type="number", format="float")
     *             ))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Utilisateur non authentifié")
     *         )
     *     )
     * )
     */
    public function solde(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Utilisateur non authentifié', 401);
        }

        try {
            $comptes = $user->comptes;
            $soldes = [];
            foreach ($comptes as $compte) {
                $solde = $this->compteService->solde($compte->id);
                $soldes[] = [
                    'compte_id' => $compte->id,
                    'numero_compte' => $compte->numero_compte,
                    'type' => $compte->type,
                    'solde' => $solde,
                ];
            }

            return $this->successResponse($soldes, 'Soldes récupérés avec succès');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comptes/{id}/paiement-code",
     *     summary="Payment by code or transfer by phone from specific account",
     *     description="Make a payment using a payment code or transfer money to a phone number from a specific account",
     *     operationId="paymentByCodeFromAccount",
     *     tags={"Comptes"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Source account ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"code","montant"},
     *
     *             @OA\Property(property="code", type="string", description="Payment code or phone number", example="PAY123456"),
     *             @OA\Property(property="montant", type="number", format="float", example="50.00")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Payment successful",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Paiement effectué avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="source", ref="#/components/schemas/Compte"),
     *                 @OA\Property(property="destination", ref="#/components/schemas/Compte")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Access denied",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Accès non autorisé à ce compte")
     *         )
     *     )
     * )
     */
    public function paiementCode(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Utilisateur non authentifié', 401);
        }

        // Verify that the source account belongs to the logged-in client
        $compteSource = $user->comptes()->where('id', $id)->first();
        if (!$compteSource) {
            return $this->errorResponse('Accès non autorisé à ce compte', 403);
        }

        $data = $this->validateByKey('compte_paiement_code', $request->all());

        try {
            // Try payment by code first
            $result = $this->compteService->paiementParCode($id, $data['code'], $data['montant']);
            return $this->successResponse($result, 'Paiement effectué avec succès');
        } catch (Exception $e) {
            if ($e->getMessage() === 'payment_code_invalid') {
                // Treat as phone number for transfer
                try {
                    $result = $this->compteService->transfertParNumero(
                        $id,
                        $data['code'], // phone number
                        $data['montant']
                    );
                    return $this->successResponse($result, 'Transfert effectué avec succès');
                } catch (Exception $e2) {
                    return $this->errorResponse($e2->getMessage(), 400);
                }
            } else {
                return $this->errorResponse($e->getMessage(), 400);
            }
        }
    }


    /**
     * @OA\Post(
     *     path="/api/v1/comptes/{id}/transfert-tel",
     *     summary="Transfer by phone number from specific account",
     *     description="Transfer money to another account using phone number from a specific account",
     *     operationId="transferByPhoneFromAccount",
     *     tags={"Comptes"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Source account ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"numero_tel","montant"},
     *
     *             @OA\Property(property="numero_tel", type="string", example="0123456789"),
     *             @OA\Property(property="montant", type="number", format="float", example="25.00")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Transfer successful",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Transfert effectué avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Access denied",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Accès non autorisé à ce compte")
     *         )
     *     )
     * )
     */
    public function transfertTel(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Utilisateur non authentifié', 401);
        }

        // Vérifier que le compte source appartient au client connecté
        $compte = $user->comptes()->where('id', $id)->first();
        if (!$compte) {
            return $this->errorResponse('Accès non autorisé à ce compte', 403);
        }

        $data = $this->validateByKey('compte_transfert_tel', $request->all());

        try {
            $result = $this->compteService->transfertParNumero(
                $id,
                $data['numero_tel'],
                $data['montant']
            );

            return $this->successResponse($result, 'Transfert effectué avec succès');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}