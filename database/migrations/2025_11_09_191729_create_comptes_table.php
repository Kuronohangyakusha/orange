<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comptes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->string('numero_compte')->unique();
            $table->decimal('solde', 15, 2)->default(0);
            $table->enum('type', ['courant', 'cheque', 'epargne'])->default('courant');
            $table->string('code_marchand')->nullable();
            $table->string('code_paiement')->nullable();
            $table->timestamps();

            // Clé étrangère vers Client
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
