<?php
namespace App\Models\Cursos;
use Illuminate\Database\Eloquent\Model;
use App\Models\Users\User;

class Completion extends Model
{
    // Permitir asignación masiva
    protected $fillable = ['user_id', 'completable_type', 'completable_id'];

    public function completable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
