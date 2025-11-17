<?php

namespace App\Models;

use App\Utils\GenererUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    use GenererUuid, HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'nom',
        'prenom',
        'email',
        'telephone',
        'password',
        'otp_code',
        'otp_expires_at',
        'role',
    ];

    protected $hidden = [
        'password',
        'otp_code',
        'otp_expires_at',
    ];

    protected $casts = [
        'otp_expires_at' => 'datetime',
    ];

    // Un merchant appartient à un user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Un merchant peut avoir plusieurs comptes
    public function comptes()
    {
        return $this->hasMany(Compte::class, 'user_id', 'user_id');
    }
}
