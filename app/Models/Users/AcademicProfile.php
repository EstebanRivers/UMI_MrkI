<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicProfile extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'campus',
        'semestre',
        'status',
        'carrera_id',
        'departamento',
        'modulos',
        'documentos',
        'rol',
    ];

    protected $casts = [
        'modulos' => 'array',
        'documentos' => 'array',
    ];

    /**
     * El usuario al que pertenece este perfil académico.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}