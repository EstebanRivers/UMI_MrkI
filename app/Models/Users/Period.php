<?php

namespace App\Models\Users; 

use App\Models\Users\Institution; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'institution_id',
    ];

    /**
     * Define los casts para los tipos de atributos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Define la relación "pertenece a" con Institution.
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }
}