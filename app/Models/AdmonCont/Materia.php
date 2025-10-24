<?php

namespace App\Models\AdmonCont;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importar

// Modelos necesarios para la consulta y relaciones
use App\Models\Users\Career;


class Materia extends Model
{
    //
    protected $table = 'materias';

    protected $fillable = [
        'nombre',
        'clave',
        'creditos',
        'career_id',
        'descripcion',
        'type',
        'semestre'
    ];
    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }
}
