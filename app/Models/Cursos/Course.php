<?php

namespace App\Models\Cursos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Users\User;
use App\Models\Users\Institution;
use App\Models\Users\Workstation;
use App\Models\Users\Department;
use App\Models\Users\Career;


class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';

    protected $fillable = [
        'title',
        'description',
        'hours',
        'credits',
        'institution_id',
        'instructor_id',
        'image',

    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topics::class, 'course_id');
    }

    // Relación polimórfica para las carreras
    public function careers()
    {
        return $this->morphedByMany(Career::class, 'targetable');
    }

    // Relación polimórfica para los departamentos
    public function departments()
    {
        return $this->morphedByMany(Department::class, 'targetable');
    }

    // Relación polimórfica para los puestos de trabajo
    public function workstations()
    {
        return $this->morphedByMany(Workstation::class, 'targetable');
    }

}
