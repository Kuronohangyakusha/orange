<?php

namespace App\Models;

use App\Utils\GenererUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Transaction",
 *     type="object",
 *
 *     @OA\Property(property="id", type="string", format="uuid", example="39adc2af-39c8-41cf-8aa7-930ba114aad0"),
 *     @OA\Property(property="compte_id", type="string", format="uuid", example="f9136320-10c8-47cc-8de0-ba4cc2ef78b1"),
 *     @OA\Property(property="type", type="string", enum={"reception", "transfert", "paiement"}, example="reception"),
 *     @OA\Property(property="montant", type="number", format="float", example="100.50"),
 *     @OA\Property(property="description", type="string", example="Dépôt d'argent"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Transaction extends Model
{
    use GenererUuid, HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    public const TYPES = ['reception', 'transfert', 'paiement'];

    protected $fillable = [
        'compte_id',
        'type',               // reception, transfert, paiement
        'montant',
        'code_marchand',      // si paiement
        'numero_destinataire', // si transfert
        'description',
    ];

    // Une transaction appartient à un compte
    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }
}
