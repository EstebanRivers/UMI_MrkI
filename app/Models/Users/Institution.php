<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Cursos\Course;


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
}
