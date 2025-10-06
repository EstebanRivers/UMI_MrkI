<?php

namespace App\Models\Cursos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
