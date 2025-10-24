<?php

namespace App\Models\AdmonCont;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Alumno extends Model
{
    //
    use HasFactory;
    protected $table = 'users';
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
    ];
}
