<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $colonia
 * @property string $calle
 * @property string $ciudad
 * @property string $estado
 * @property string $codigo_postal
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Users\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereCalle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereCiudad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereCodigoPostal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereColonia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'colonia',
        'calle',
        'ciudad',
        'estado',
        'codigo_postal',
    ];

    /**
     * Los usuarios que tienen esta dirección.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}