<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $name
 * @property int $institution_id
 * @property int $department_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Users\Department $department
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Workstation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Workstation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Workstation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Workstation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Workstation whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Workstation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Workstation whereInstitutionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Workstation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Workstation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Workstation extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'department_id',
        'institution_id',
    ];

    /**
     * Define la relación "pertenece a" con Department.
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

}
