<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OtpAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test génération d'OTP lors de l'inscription
     */
    public function test_otp_generation_on_registration(): void
    {
        Mail::fake();

        $data = [
            'nom' => 'Ndeye',
            'prenom' => 'Ndiaye',
            'email' => 'ndeye@example.com',
            'telephone' => '+221770000000',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $data);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Un code OTP a été envoyé à votre email',
            ]);

        $this->assertDatabaseHas('clients', [
            'nom' => 'Ndeye',
            'prenom' => 'Ndiaye',
            'email' => 'ndeye@example.com',
            'telephone' => '+221770000000',
        ]);

        $client = Client::where('email', 'ndeye@example.com')->first();
        $this->assertNotNull($client->otp_code);
        $this->assertNotNull($client->otp_expires_at); // OTP expires

        Mail::assertSent(\App\Mail\OtpEmail::class, 1);
    }

    /**
     * Test vérification OTP valide
     */
    public function test_valid_otp_verification(): void
    {
        $client = Client::factory()->create();
        $authService = app(AuthService::class);
        $otp = $authService->generateOtp($client);

        $response = $this->postJson('/api/v1/auth/verify', [
            'telephone' => $client->telephone,
            'otp' => $otp,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token',
                    'client',
                ],
            ]);

        $client->refresh();
        // OTP invalidated after use
        $this->assertNull($client->otp_code);
        $this->assertNull($client->otp_expires_at);
    }


    /**
     * Test vérification OTP invalide
     */
    public function test_invalid_otp_verification(): void
    {
        $client = Client::factory()->create();
        $authService = app(AuthService::class);
        $authService->generateOtp($client);

        $response = $this->postJson('/api/v1/auth/verify', [
            'telephone' => $client->telephone,
            'otp' => '000000',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Code OTP invalide ou expiré',
            ]);
    }
}
