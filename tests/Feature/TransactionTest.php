<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Compte;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que le solde est cohérent après une transaction de réception
     */
    public function test_balance_consistency_after_reception(): void
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create(['user_id' => $client->user_id, 'solde' => 0]);

        $montant = 100.50;

        Transaction::create([
            'compte_id' => $compte->id,
            'type' => 'reception',
            'montant' => $montant,
            'description' => 'Test réception',
        ]);

        $compte->refresh();
        $this->assertEquals($montant, $compte->solde);
        $this->assertEquals($montant, $compte->calculerSolde());
    }

    /**
     * Test que le solde est cohérent après une transaction de paiement
     */
    public function test_balance_consistency_after_paiement(): void
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create(['user_id' => $client->user_id, 'solde' => 0]);

        // Ajouter une réception d'abord
        Transaction::create([
            'compte_id' => $compte->id,
            'type' => 'reception',
            'montant' => 200,
            'description' => 'Initial deposit',
        ]);

        $montant = 50.25;

        Transaction::create([
            'compte_id' => $compte->id,
            'type' => 'paiement',
            'montant' => $montant,
            'description' => 'Test paiement',
        ]);

        $compte->refresh();
        $this->assertEquals(200 - $montant, $compte->solde);
        $this->assertEquals(200 - $montant, $compte->calculerSolde());
    }

    /**
     * Test que le solde est cohérent après un transfert sortant
     */
    public function test_balance_consistency_after_transfert_out(): void
    {
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();
        $compte1 = Compte::factory()->create(['user_id' => $client1->user_id, 'solde' => 0]);
        $compte2 = Compte::factory()->create(['user_id' => $client2->user_id, 'solde' => 0]);

        // Ajouter une réception pour compte1
        Transaction::create([
            'compte_id' => $compte1->id,
            'type' => 'reception',
            'montant' => 300,
            'description' => 'Initial deposit compte1',
        ]);

        $montant = 75.00;

        // Transfert sortant
        Transaction::create([
            'compte_id' => $compte1->id,
            'type' => 'transfert',
            'montant' => $montant,
            'numero_destinataire' => $client2->telephone,
            'description' => 'Test transfert sortant',
        ]);

        // Réception
        Transaction::create([
            'compte_id' => $compte2->id,
            'type' => 'reception',
            'montant' => $montant,
            'description' => 'Test réception',
        ]);

        $compte1->refresh();
        $compte2->refresh();

        $this->assertEquals(300 - $montant, $compte1->solde);
        $this->assertEquals($montant, $compte2->solde);
        $this->assertEquals(300 - $montant, $compte1->calculerSolde());
        $this->assertEquals($montant, $compte2->calculerSolde());
    }

    /**
     * Test que les types de transaction sont valides
     */
    public function test_transaction_types_are_valid(): void
    {
        $this->assertEquals(['reception', 'transfert', 'paiement'], Transaction::TYPES);
    }
}
