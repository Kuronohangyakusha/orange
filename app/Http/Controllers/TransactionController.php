<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use App\Traits\ApiResponses;
use Exception;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use ApiResponses;

    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function historique(Request $request, $id)
    {
        $user = $request->user();

        // Vérifier que le compte appartient au client connecté
        $compte = $user->comptes()->where('id', $id)->first();
        if (!$compte) {
            return $this->errorResponse('Accès non autorisé à ce compte', 403);
        }

        $perPage = $request->get('per_page', 10);
        $paginated = $this->transactionService->transactionsDuCompte($compte->id)->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Historique des transactions',
            'data' => $paginated->items(),
            'pagination' => [
                'total' => $paginated->total(),
                'count' => $paginated->count(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'total_pages' => $paginated->lastPage(),
            ],
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/v1/transactions",
     *     summary="Create a new transaction",
     *     description="Create a new transaction on one of the client's accounts",
     *     operationId="createTransaction",
     *     tags={"Transactions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"compte_id","type","montant"},
     *
     *             @OA\Property(property="compte_id", type="string", format="uuid", example="f9136320-10c8-47cc-8de0-ba4cc2ef78b1"),
     *             @OA\Property(property="type", type="string", enum={"depot", "retrait", "transfert", "paiement"}, example="depot"),
     *             @OA\Property(property="montant", type="number", format="float", example="100.50"),
     *             @OA\Property(property="description", type="string", example="Dépôt d'argent")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Transaction created successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Transaction créée"),
     *             @OA\Property(property="data", ref="#/components/schemas/Transaction")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Les données fournies sont invalides.")
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
     *             @OA\Property(property="message", type="string", example="Accès au compte refusé")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $this->validateByKey('transaction_store', $request->all());

        // Vérifier que le compte appartient au client connecté
        $compte = $request->user()->comptes()->where('id', $data['compte_id'])->first();
        if (! $compte) {
            return $this->errorResponseByKey('account_access_denied', 403);
        }

        try {
            $transaction = $this->transactionService->creerTransaction($data);

            return $this->successResponse($transaction, 'Transaction créée', [], 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

}
