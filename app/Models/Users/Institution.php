<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\Cursos\Course;
use App\Models\Users\Career;     
use App\Models\Users\Department;
use App\Models\Users\Workstation;


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

    public function careers(): HasMany
    {
        return $this->hasMany(Career::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function workstations(): HasManyThrough
    {
        // El primer argumento es el modelo final al que queremos llegar (Workstation).
        // El segundo argumento es el modelo intermedio (Department).
        return $this->hasManyThrough(Workstation::class, Department::class);
    }
}
