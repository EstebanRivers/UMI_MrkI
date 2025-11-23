<?php

namespace App\Models\AdmonCont;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'numero_aula',
        'seccion',
        'capacidad',
        'ubicacion',
        'tipo'
    ];
}
