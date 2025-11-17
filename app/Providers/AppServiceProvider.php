<?php

namespace App\Providers;

use App\Models\Client;
// Repositories
use App\Models\Compte;
use App\Models\Transaction;
use App\Observers\CompteObserver;
// Services
use App\Observers\TransactionObserver;
use App\Repositories\ClientRepository;
use App\Repositories\CompteRepository;
// Models
use App\Repositories\TransactionRepository;
use App\Services\ClientService;
use App\Services\CompteService;
// Observers
use App\Services\TransactionService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // ---------------- Repositories ----------------
        $this->app->bind(ClientRepository::class, fn () => new ClientRepository(new Client));
        $this->app->bind(CompteRepository::class, fn () => new CompteRepository(new Compte));
        $this->app->bind(TransactionRepository::class, fn () => new TransactionRepository(new Transaction));

        // ---------------- Services ----------------
        $this->app->bind(ClientService::class, fn ($app) => new ClientService(
            $app->make(ClientRepository::class),
            $app->make(CompteService::class)
        ));

        $this->app->bind(CompteService::class, fn ($app) => new CompteService(
            $app->make(CompteRepository::class),
            $app->make(TransactionRepository::class),
            $app->make(ClientRepository::class)
        ));

        $this->app->bind(TransactionService::class, fn ($app) => new TransactionService(
            $app->make(TransactionRepository::class)
        ));
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Compte::observe(CompteObserver::class);
        Transaction::observe(TransactionObserver::class);
    }
}
