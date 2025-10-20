<?php

namespace App\Models\AdmonCont;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importar

// Modelos necesarios para la consulta y relaciones
use App\Models\AdmonCont\Materia;

class Career extends Model
{
    //
    use HasFactory;

    protected $table = 'carrers';

    protected $fillable = [
        'official_id',
        'name',
        'description1',
        'description2',
        'description3',
        'type',
        'semestre'
    ];

    public function materiaProfile(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }
}
