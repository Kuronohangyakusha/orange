<?php

namespace App\Utils;

use Illuminate\Support\Str;

trait GenererUuid
{
    /**
     * Boot the trait for a model.
     * Assigne automatiquement un UUID à la création.
     */
    protected static function bootGenererUuid()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
