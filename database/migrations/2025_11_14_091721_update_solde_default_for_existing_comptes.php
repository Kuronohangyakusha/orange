<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('comptes')->where('solde', 0)->update(['solde' => 10000]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('comptes')->where('solde', 10000)->update(['solde' => 0]);
    }
};
