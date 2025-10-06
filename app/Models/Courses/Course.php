<?php

namespace App\Models\Cursos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';

    protected $fillable = [
        'title',
        'description',
        'credits',
        'hours',
        'prerequisites',
        'instructor_id',
        'image',
        //'category_id',

    ];
    protected $casts = [
        'prerequisites' => 'array', 
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topics::class, 'course_id');
    }

}
