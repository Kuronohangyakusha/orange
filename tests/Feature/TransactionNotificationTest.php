<?php

namespace Tests\Feature;

use App\Jobs\SendTransactionNotification;
use App\Models\Client;
use App\Models\Compte;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TransactionNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test qu'une notification email est envoyée lors de la création d'une transaction
     */
    public function test_transaction_notification_is_sent_on_creation(): void
    {
        Queue::fake();
        Mail::fake();

        $client = Client::factory()->create();
        $compte = Compte::factory()->create(['user_id' => $client->user_id]);

        // Créer une transaction
        $transaction = Transaction::create([
            'compte_id' => $compte->id,
            'type' => 'reception',
            'montant' => 100.50,
            'description' => 'Test transaction',
        ]);

        // Vérifier que le job a été dispatché
        Queue::assertPushed(SendTransactionNotification::class, 1);
    }

    /**
     * Test que l'email de notification contient les bonnes informations
     */
    public function test_transaction_notification_email_content(): void
    {
        Mail::fake();

        $client = Client::factory()->create();
        $compte = Compte::factory()->create([
            'user_id' => $client->user_id,
            'numero_compte' => 'FR123456789',
            'type' => 'courant',
        ]);

        $transaction = Transaction::create([
            'compte_id' => $compte->id,
            'type' => 'paiement',
            'montant' => 50.25,
            'description' => 'Paiement test',
            'code_marchand' => 'MARCHAND001',
        ]);

        // Simuler l'envoi de l'email
        Mail::send(new \App\Mail\TransactionNotification($transaction), [], function ($message) use ($client) {
            $message->to($client->email);
            $message->subject('Test subject');
        });

        // Vérifier que l'email a été envoyé
        Mail::assertSent(\App\Mail\TransactionNotification::class, function ($mail) use ($client, $transaction) {
            return $mail->hasTo($client->email) &&
                   $mail->transaction->id === $transaction->id;
        });

        // Vérifier le sujet de l'email
        Mail::assertSent(\App\Mail\TransactionNotification::class, function ($mail) {
            return str_contains($mail->envelope()->subject, 'Paiement de 50,25€');
        });
    }

    /**
     * Test que les notifications sont envoyées pour tous types de transaction
     */
    public function test_notifications_sent_for_all_transaction_types(): void
    {
        Queue::fake();

        $client = Client::factory()->create();
        $compte = Compte::factory()->create(['user_id' => $client->user_id]);

        $transactionTypes = ['reception', 'transfert', 'paiement'];

        foreach ($transactionTypes as $type) {
            Transaction::create([
                'compte_id' => $compte->id,
                'type' => $type,
                'montant' => 25.00,
                'description' => "Test $type",
            ]);
        }

        // Vérifier que 3 jobs ont été dispatchés
        Queue::assertPushed(SendTransactionNotification::class, 3);
    }

    /**
     * Test que le job a les bonnes tags pour le monitoring
     */
    public function test_transaction_notification_job_has_correct_tags(): void
    {
        $client = Client::factory()->create();
        $compte = Compte::factory()->create(['user_id' => $client->user_id]);

        $transaction = Transaction::create([
            'compte_id' => $compte->id,
            'type' => 'reception',
            'montant' => 100.00,
        ]);

        $job = new SendTransactionNotification($transaction);
        $tags = $job->tags();

        $this->assertContains('transaction-notification', $tags);
        $this->assertContains('client:'.$client->user_id, $tags);
        $this->assertContains('compte:'.$compte->id, $tags);
    }
}
