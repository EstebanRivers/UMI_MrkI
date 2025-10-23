<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\Cursos\Course;
use App\Models\Users\Career;     
use App\Models\Users\Department;
use App\Models\Users\Workstation;


/**
 * @property int $id
 * @property string $name
 * @property string|null $logo_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Career> $careers
 * @property-read int|null $careers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Course> $courses
 * @property-read int|null $courses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Department> $departments
 * @property-read int|null $departments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Users\User> $users
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Workstation> $workstations
 * @property-read int|null $workstations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Institution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Institution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Institution query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Institution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Institution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Institution whereLogoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Institution whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Institution whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Institution extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo_path',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'institution_user');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function careers(): HasMany
    {
        return $this->hasMany(Career::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function workstations(): HasManyThrough
    {
        // El primer argumento es el modelo final al que queremos llegar (Workstation).
        // El segundo argumento es el modelo intermedio (Department).
        return $this->hasManyThrough(Workstation::class, Department::class);
    }
}
