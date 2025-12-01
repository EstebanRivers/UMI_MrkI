<?php

namespace App\Models\AdmonCont;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AdmonCont\HorarioFranja;
use App\Models\AdmonCont\Career;
use App\Models\AdmonCont\Materia;
use App\Models\AdmonCont\Facility;
use App\Models\Users\User;

class HorarioClase extends Model
{
    //
    protected $fillable = [
        'materia_id',
        'carrera_id',
        'user_id',
        'aula_id'
    ];

    public function franjas()
    {
        return $this->hasMany(HorarioFranja::class);
    }
    public function carrera(): BelongsTo
    {
        // Asegúrate de que 'carrera_id' sea el nombre de la columna FK en tu tabla 'horarios_clases'
        return $this->belongsTo(Career::class, 'carrera_id'); 
    }
    public function materia(): BelongsTo
    {
        // El nombre de la columna FK en horarios_clases es 'materia_id'
        return $this->belongsTo(Materia::class, 'materia_id'); 
    }
    public function user(): BelongsTo
    {
        // La columna FK en horarios_clases es 'user_id'
        return $this->belongsTo(User::class, 'user_id'); 
    }
    public function aula(): BelongsTo
    {
        // La columna FK en horarios_clases es 'user_id'
        return $this->belongsTo(Facility::class, 'aula_id'); 
    }
}
