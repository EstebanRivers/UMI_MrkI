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


/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property int $credits
 * @property int $hours
 * @property string|null $image
 * @property int $instructor_id
 * @property int $institution_id
 * @property string|null $target_department_career Carrera (Académico) o Nombre del Departamento (Corporativo)
 * @property string|null $target_job_title Puesto de trabajo específico para el filtrado corporativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Career> $careers
 * @property-read int|null $careers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Department> $departments
 * @property-read int|null $departments_count
 * @property-read Institution $institution
 * @property-read User $instructor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cursos\Topics> $topics
 * @property-read int|null $topics_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Workstation> $workstations
 * @property-read int|null $workstations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCredits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereInstitutionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereInstructorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereTargetDepartmentCareer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereTargetJobTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';

    protected $fillable = [
        'title',
        'description',
        'credits',
        'hours',
        'instructor_id',
        'institution_id',
        'image',
        'guide_material_path',

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

    /**
     * Los usuarios inscritos en este curso.
     */
    public function users()
    {
        // Asumiendo que tu modelo de usuario es 'User'
        // Usa App\Models\Users\User::class
        return $this->belongsToMany(User::class, 'course_user');
    }

    public function finalExam()
    {
        return $this->hasOne(Activities::class)
                    ->where('is_final_exam', true);
    }

}
