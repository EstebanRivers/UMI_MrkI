<?php

namespace App\Models\AdmonCont;

use Illuminate\Database\Eloquent\Model;
use App\Models\AdmonCont\HorarioClase;

class HorarioFranja extends Model
{
    protected $fillable = [
        'horario_clase_id',
        'dias_semana',
        'hora_inicio',
        'hora_fin'
    ];
    protected $casts = [
        'dias_semana' => 'array', // Esto le dice a Laravel: ¡Trata esto como un array!
    ];

    public function horarioClase(){
        // Una franja horaria pertenece a una única clase.
        return $this->belongsTo(HorarioClase::class); 
    }
}
