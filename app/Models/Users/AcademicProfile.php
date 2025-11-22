<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $user_id
 * @property int|null $career_id
 * @property int|null $semestre
 * @property string|null $rol
 * @property array<array-key, mixed>|null $documentos
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Users\Career|null $career
 * @property-read \App\Models\Users\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicProfile whereCareerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicProfile whereDocumentos($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicProfile whereRol($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicProfile whereSemestre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicProfile whereUserId($value)
 * @mixin \Eloquent
 */
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
        'carrera',
        'departamento',
        'modules',
        'documentos',
        'rol',
    ];

    protected $casts = [
        'modules' => 'array',
        'documentos' => 'array',
    ];

    /**
     * El usuario al que pertenece este perfil académico.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function career()
    {
        return $this->belongsTo(Career::class);
    }
}