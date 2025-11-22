<?php

namespace App\Models\Cursos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Cursos\Completion;

/**
 * @property int $id
 * @property int $course_id
 * @property string $title
 * @property string|null $description
 * @property string|null $file_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cursos\Activities> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Cursos\Course $course
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cursos\Subtopic> $subtopics
 * @property-read int|null $subtopics_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topics newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topics newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topics query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topics whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topics whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topics whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topics whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topics whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topics whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Topics whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Topics extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'file_path',
    ];

    /**
     * Curso al que pertenece el tema
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class ,'course_id');
    }

    /**
     * Actividades del tema
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activities::class, 'topic_id');
    }

    public function subtopics(): HasMany
    {
        return $this->hasMany(Subtopic::class, 'topic_id');
    }

    public function completions()
    {
        return $this->morphMany(Completion::class, 'completable');
    }
}
