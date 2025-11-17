<?php

namespace App\Models;

use App\Utils\GenererUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Compte",
 *     type="object",
 *
 *     @OA\Property(property="id", type="string", format="uuid", example="f9136320-10c8-47cc-8de0-ba4cc2ef78b1"),
 *     @OA\Property(property="user_id", type="integer", example="1"),
 *     @OA\Property(property="numero_compte", type="string", example="FR4284166632"),
 *     @OA\Property(property="solde", type="number", format="float", example="150.75"),
 *     @OA\Property(property="type", type="string", enum={"courant", "cheque", "epargne"}, example="courant"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Compte extends Model
{
    use GenererUuid, HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'numero_compte',
        'solde',
        'type',          // courant, cheque, epargne
        'code_marchand', // optionnel
        'code_paiement', // généré automatiquement
        'qr_code',       // QR code généré automatiquement
    ];

    public const TYPES = ['courant', 'cheque', 'epargne'];

    protected $hidden = [
        'code_marchand',
    ];

    // Un compte appartient à un user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Un compte peut avoir plusieurs transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Calculer le solde actuel à partir des transactions
     */
    public function calculerSolde(): float
    {
        return \Illuminate\Support\Facades\Cache::remember(
            "compte_solde_{$this->id}",
            3600, // 1 heure
            function () {
                $credit = $this->transactions()
                    ->where('type', 'reception')
                    ->sum('montant');

                $debit = $this->transactions()
                    ->whereIn('type', ['paiement', 'transfert'])
                    ->sum('montant');

                return $credit - $debit;
            }
        );
    }

    /**
     * Mettre à jour le solde du compte
     */
    public function updateSolde(): void
    {
        // Invalider le cache d'abord
        \Illuminate\Support\Facades\Cache::forget("compte_solde_{$this->id}");

        $this->solde = $this->calculerSolde();
        $this->save();
    }
}
