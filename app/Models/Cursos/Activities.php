<?php

namespace App\Models\Cursos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Users\User;
use App\Models\Cursos\Completion;



/**
 * @property int $id
 * @property int|null $topic_id
 * @property int|null $subtopic_id
 * @property string $title
 * @property string|null $description
 * @property string $type
 * @property array<array-key, mixed> $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cursos\Subtopic|null $subtopic
 * @property-read \App\Models\Cursos\Topics|null $topic
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activities newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activities newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activities query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activities whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activities whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activities whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activities whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activities whereSubtopicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activities whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activities whereTopicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activities whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activities whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

    public function completedByUsers()
    {
        return $this->belongsToMany(User::class, 'activity_user', 'user_id', 'activity_id')
                    ->withTimestamps('completed_at');
    }


}
