<?php

namespace App\Models\Cursos;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $topic_id
 * @property string $title
 * @property string|null $description
 * @property string|null $file_path
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cursos\Activities> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Cursos\Topics $topic
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subtopic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subtopic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subtopic query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subtopic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subtopic whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subtopic whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subtopic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subtopic whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subtopic whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subtopic whereTopicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subtopic whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Subtopic extends Model
{
    protected $fillable = [
        'topic_id',
        'title',
        'description',
        'file_path',
        'order',
    ];

    public function topic()
    {
        return $this->belongsTo(Topics::class, 'topic_id');
    }

    public function activities()
    {
        return $this->hasMany(Activities::class, 'subtopic_id');
    }

}
