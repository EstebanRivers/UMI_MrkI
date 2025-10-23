<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $user_id
 * @property string|null $unidad_negocio
 * @property string|null $rol
 * @property int|null $department_id
 * @property int|null $workstation_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Users\User $user
 * @property-read \App\Models\Users\Workstation|null $workstation
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CorporateProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CorporateProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CorporateProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CorporateProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CorporateProfile whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CorporateProfile whereRol($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CorporateProfile whereUnidadNegocio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CorporateProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CorporateProfile whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CorporateProfile whereWorkstationId($value)
 * @mixin \Eloquent
 */
class CorporateProfile extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'unidad_negocio',
        'rol',
        'departamento',
        'puesto',
    ];

    /**
     * El usuario al que pertenece este perfil corporativo.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function workstation()
    {
        return $this->belongsTo(Workstation::class);
    }
}