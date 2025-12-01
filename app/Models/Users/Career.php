<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use App\Models\Users\Institution;


/**
 * @property int $id
 * @property string $name
 * @property int $institution_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Institution $institution
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Career newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Career newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Career query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Career whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Career whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Career whereInstitutionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Career whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Career whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Career extends Model
{
    protected $fillable = [
        'official_id',
        'name',
        'description1',
        'description2',
        'description3',
        'type',
        'semesters', // <--- ✅ CORRECTO: Plural, como en tu BD
        'institution_id', // Agregado por seguridad ya que está en tu BD
        'credits'         // Agregado por seguridad
    ];

    // --- RELACIONES ---

    public function institution(): BelongsTo 
    { 
        return $this->belongsTo(Institution::class); 
    }

}
