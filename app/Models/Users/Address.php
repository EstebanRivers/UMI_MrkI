<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'colonia',
        'calle',
        'ciudad',
        'estado',
        'codigo_postal',
    ];

    /**
     * Los usuarios que tienen esta dirección.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}