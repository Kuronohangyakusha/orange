<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('compte_id');
            $table->enum('type', ['reception', 'transfert', 'paiement']);
            $table->decimal('montant', 15, 2);
            $table->string('code_marchand')->nullable();
            $table->string('numero_destinataire')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            // Clé étrangère vers Compte
            $table->foreign('compte_id')->references('id')->on('comptes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
