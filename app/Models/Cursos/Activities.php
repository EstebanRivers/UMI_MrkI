<?php

namespace App\Models\Cursos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Activities extends Model
{
    use HasFactory;

    protected $table = 'activities';

    protected $fillable = [
        'topic_id',
        'subtopic_id',
        'title',
        'description',
        'type',
        'content',
        
    ];

    protected $casts = [
        'content' => 'array',
    ];

    /**
     * Tema al que pertenece la actividad
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topics::class, 'topic_id');
    }
    public function subtopic(): BelongsTo
    {
        return $this->belongsTo(Subtopic::class, 'subtopic_id');
    }
}
