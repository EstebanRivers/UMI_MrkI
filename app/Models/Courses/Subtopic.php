<?php

namespace App\Models\Cursos;

use Illuminate\Database\Eloquent\Model;

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
