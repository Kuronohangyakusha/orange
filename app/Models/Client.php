<?php

namespace App\Models;

use App\Utils\GenererUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Client extends Authenticatable
{
    use GenererUuid, HasApiTokens, HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'password',
        'otp_code',
        'otp_expires_at',
        'role',
        'user_id',
    ];

    protected $hidden = [
        'password',
        'otp_code',
        'otp_expires_at',
    ];

    protected $casts = [
        'otp_expires_at' => 'datetime',
    ];

    // Un client appartient à un user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Un client peut avoir plusieurs comptes
    public function comptes()
    {
        return $this->hasMany(Compte::class, 'user_id', 'user_id');
    }
}
