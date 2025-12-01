<?php
namespace App\Models\Cursos;
use Illuminate\Database\Eloquent\Model;
use App\Models\Users\User;

class Completion extends Model
{
    // Permitir asignación masiva
<<<<<<< HEAD
    protected $fillable = [
        'user_id', 
        'completable_type', 
        'completable_id', 
        'score'
    ];
=======
    protected $fillable = ['user_id', 'completable_type', 'completable_id'];
>>>>>>> parent of 0358ee6 (Fix: Reemplazo forzoso de Proyecto)

    public function completable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
