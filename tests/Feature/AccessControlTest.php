<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Compte;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test qu'un client peut accéder à ses propres comptes
     */
    public function test_client_can_access_own_accounts(): void
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create(['user_id' => $client->user_id]);

        $this->actingAs($client, 'sanctum');

        $response = $this->getJson('/api/v1/comptes');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Comptes récupérés',
            ])
            ->assertJsonStructure([
                'data' => [
                    'comptes' => [
                        '*' => [
                            'id',
                            'numero_compte',
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test qu'un client ne peut pas accéder aux comptes d'un autre client
     */
    public function test_client_cannot_access_other_clients_accounts(): void
    {
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();
        $compte = Compte::factory()->create(['user_id' => $client2->user_id]);

        $this->actingAs($client1, 'sanctum');

        $response = $this->getJson('/api/v1/comptes/'.$compte->id.'/solde');

        $response->assertStatus(403);
    }

    /**
     * Test qu'un client peut accéder au solde de son propre compte
     */
    public function test_client_can_access_own_account_balance(): void
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create(['user_id' => $client->user_id]);

        $this->actingAs($client, 'sanctum');

        $response = $this->getJson('/api/v1/comptes/'.$compte->id.'/solde');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Solde récupéré avec succès',
            ]);
    }

    /**
     * Test qu'un client ne peut pas accéder au solde d'un compte d'un autre client
     */
    public function test_client_cannot_access_other_clients_account_balance(): void
    {
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();
        $compte = Compte::factory()->create(['user_id' => $client2->user_id]);

        $this->actingAs($client1, 'sanctum');

        $response = $this->getJson('/api/v1/comptes/'.$compte->id.'/solde');

        $response->assertStatus(403);
    }

    /**
     * Test qu'un client peut accéder aux transactions de son propre compte
     */
    public function test_client_can_access_own_account_transactions(): void
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create(['user_id' => $client->user_id]);

        $this->actingAs($client, 'sanctum');

        $response = $this->getJson('/api/v1/comptes/'.$compte->id.'/historique');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Historique des transactions récupéré',
            ]);
    }

    /**
     * Test qu'un client ne peut pas accéder aux transactions d'un compte d'un autre client
     */
    public function test_client_cannot_access_other_clients_account_transactions(): void
    {
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();
        $compte = Compte::factory()->create(['user_id' => $client2->user_id]);

        $this->actingAs($client1, 'sanctum');

        $response = $this->getJson('/api/v1/comptes/'.$compte->id.'/historique');

        $response->assertStatus(403);
    }

    /**
     * Test de la pagination des transactions
     */
    public function test_transactions_pagination(): void
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create(['user_id' => $client->user_id]);

        // Créer plusieurs transactions
        for ($i = 0; $i < 25; $i++) {
            Transaction::create([
                'compte_id' => $compte->id,
                'type' => 'reception',
                'montant' => 10,
                'description' => 'Test transaction '.$i,
            ]);
        }

        $this->actingAs($client, 'sanctum');

        $response = $this->getJson('/api/v1/comptes/'.$compte->id.'/historique');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'type',
                        'montant',
                        'description',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'total',
                    'count',
                    'per_page',
                    'current_page',
                    'total_pages',
                ],
            ]);

        // Vérifier que seulement 10 éléments sont retournés
        $this->assertCount(10, $response->json('data.data'));
    }
}
