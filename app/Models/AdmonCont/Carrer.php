<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Carrer extends Model
{
    use HasFactory;

    // Nombre de la tabla (opcional si sigue la convención plural "carrers")
    protected $table = 'carrers';

    // Campos que se pueden asignar en masa
    protected $fillable = [
        'official_id',
        'name',
        'description1',
        'description2',
        'description3',
        'credits',
        'type',
        'semesters',
    ];

}
