<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Services\AuthService;
use App\Services\ClientService;
use App\Services\CompteService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Info(
 *     title="Orange Banking API",
 *     version="1.0.0",
 *     description="API documentation for Orange Banking System"
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8001",
 *     description="Local development server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     name="Authorization",
 *     in="header",
 *     description="Enter token in format: Bearer {token}"
 * )
 *
 * @OA\Schema(
 *     schema="Client",
 *     type="object",
 *
 *     @OA\Property(property="id", type="string", format="uuid", example="f9136320-10c8-47cc-8de0-ba4cc2ef78b1"),
 *     @OA\Property(property="nom", type="string", example="Dupont"),
 *     @OA\Property(property="prenom", type="string", example="Jean"),
 *     @OA\Property(property="email", type="string", format="email", example="jean.dupont@example.com"),
 *     @OA\Property(property="telephone", type="string", example="0123456789"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class AuthController extends Controller
{
    use ApiResponses;

    protected $compteService;

    public function __construct(CompteService $compteService)
    {
        $this->compteService = $compteService;
    }


    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     summary="Register new client",
     *     description="Register a new client and send OTP for verification",
     *     operationId="register",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"nom","prenom","email","telephone","password"},
     *
     *             @OA\Property(property="nom", type="string", example="Dupont"),
     *             @OA\Property(property="prenom", type="string", example="Jean"),
     *             @OA\Property(property="email", type="string", format="email", example="jean.dupont@example.com"),
     *             @OA\Property(property="telephone", type="string", example="0123456789"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Registration successful, OTP sent",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Un code OTP a été envoyé à votre email"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="client", type="object",
     *                     @OA\Property(property="nom", type="string", example="Dupont"),
     *                     @OA\Property(property="telephone", type="string", example="0123456789")
     *                 )
     *             )
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
     *     )
     * )
     */
    public function register(Request $request, AuthService $authService, ClientService $clientService)
    {
        $data = $this->validateByKey('client_store', $request->all());

        // Créer l'utilisateur d'abord
        $user = User::create([
            'name' => $data['nom'] . ' ' . $data['prenom'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'client',
        ]);

        // Ajouter user_id aux données
        $data['user_id'] = $user->id;

        // Créer le client et son compte via ClientService
        $client = $clientService->createClient($data);

        // Générer et envoyer OTP
        $otp = $authService->generateOtp($client);
        $authService->sendOtpEmail($client, $otp);

        return $this->successResponse([
            'client' => [
                'nom' => $client->nom,
                'telephone' => $client->telephone,
            ],
        ], 'Un code OTP a été envoyé à votre email pour activer votre compte');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/verify",
     *     summary="Verify OTP",
     *     description="Verify OTP code and authenticate client",
     *     operationId="verifyOtp",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"telephone","otp"},
     *
     *             @OA\Property(property="telephone", type="string", example="0123456789"),
     *             @OA\Property(property="otp", type="string", example="123456")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Authentification réussie"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="1|abc123..."),
     *                 @OA\Property(property="client", ref="#/components/schemas/Client")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Invalid or expired OTP",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Code OTP invalide ou expiré")
     *         )
     *     )
     * )
     */
    public function verifyOtp(Request $request, AuthService $authService)
    {
        $request->validate([
            'telephone' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        $client = $authService->verifyOtp($request->telephone, $request->otp);

        if (! $client) {
            return $this->errorResponse('Code OTP invalide ou expiré', 401);
        }

        return $this->successResponse([
            'client' => $client,
        ], 'Compte activé avec succès. Vous pouvez maintenant vous connecter avec votre numéro de téléphone et mot de passe.');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Login with phone and password",
     *     description="Authenticate client using telephone and password",
     *     operationId="login",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"telephone","password"},
     *
     *             @OA\Property(property="telephone", type="string", example="0123456789"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Connexion réussie"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="1|abc123...")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Identifiants invalides")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'telephone' => 'required|string',
            'password' => 'required|string',
        ]);

        $client = Client::where('telephone', $request->telephone)->first();

        if (!$client || !Hash::check($request->password, $client->user->password)) {
            return $this->errorResponse('Identifiants invalides', 401);
        }

        $token = $client->user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
        ], 'Connexion réussie');
    }
}
