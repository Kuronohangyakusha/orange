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
        Schema::table('comptes', function (Blueprint $table) {
            // Drop foreign key to clients
            $table->dropForeign(['client_id']);

            // Change client_id to user_id, change type from uuid to unsignedBigInteger
            $table->dropColumn('client_id');
            $table->unsignedBigInteger('user_id')->nullable();

            // Add foreign key to users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            // Drop foreign key to users
            $table->dropForeign(['user_id']);

            // Change back to client_id uuid
            $table->dropColumn('user_id');
            $table->uuid('client_id')->nullable();

            // Add foreign key to clients
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }
};
