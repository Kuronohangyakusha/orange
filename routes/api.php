<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SimpleCompteController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Routes d'authentification
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/verify', [AuthController::class, 'verifyOtp'])->middleware('throttle:5,1');
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::prefix('comptes')->middleware('auth:sanctum')->group(function () {
            Route::get('/', [SimpleCompteController::class, 'index']);
            Route::get('/solde', [SimpleCompteController::class, 'solde']);
            Route::get('/{id}/solde', [SimpleCompteController::class, 'soldeParId']);
            Route::get('/{id}/historique', [SimpleCompteController::class, 'historiqueParId']);
            Route::post('/{id}/paiement-code', [SimpleCompteController::class, 'paiementCode'])->middleware('throttle:10,1');
            Route::post('/{id}/transfert-tel', [SimpleCompteController::class, 'transfertTel'])->middleware('throttle:5,1');
        });

    Route::prefix('transactions')->middleware('auth:sanctum')->group(function () {
        Route::post('/', [TransactionController::class, 'store'])->middleware('throttle:10,1');
    });

});
