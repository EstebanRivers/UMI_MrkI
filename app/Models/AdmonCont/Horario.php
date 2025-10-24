<?php

namespace App\Models\AdmonCont;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelos necesarios para la consulta y relaciones
use App\Models\AdmonCont\Career;

class Horario extends Model
{
    protected $table = 'horarios';

    protected $fillable = [
        'materia_id',
        'carrera_id',
        'user_id',
        'dias_disponibles',
        'hora_inicio',
        'hora_fin'
    ];
    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }
}
