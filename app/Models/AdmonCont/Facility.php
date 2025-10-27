<?php

namespace App\Models\AdmonCont;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $fillable = [
        'numero_aula',
        'seccion',
        'capacidad',
        'ubicacion',
        'tipo'
    ];
}
