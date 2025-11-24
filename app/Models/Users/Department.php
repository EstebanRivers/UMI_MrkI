<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use App\Models\Users\Institution;
use App\Models\Users\Workstation;
use Illuminate\Database\Eloquent\Factories\HasFactory;


/**
 * @property int $id
 * @property string $name
 * @property int $institution_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Institution $institution
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Workstation> $workstations
 * @property-read int|null $workstations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereInstitutionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Department extends Model
{
   use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'institution_id',
    ];

    
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    
    public function workstations()
    {
        return $this->hasMany(Workstation::class);
    }

}
