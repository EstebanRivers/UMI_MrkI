<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

// Relaciones
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // Faltaba importar esto

// Modelos
use App\Models\Facturacion\Billing; // Faltaba importar esto
use App\Models\Users\Role;
use App\Models\Users\Institution;
use App\Models\Users\Address;
use App\Models\Users\AcademicProfile;
use App\Models\Users\CorporateProfile;
use App\Models\Cursos\Course;
use App\Models\Cursos\Completion;

/**
 * @property int $id
 * @property string $nombre
 * @property string $apellido_paterno
 * @property string $apellido_materno
 * @property string $email
 * @property string $password
 * @property string $RFC
 * @property string|null $telefono
 * @property string|null $fecha_nacimiento
 * @property int|null $edad
 * @property int|null $address_id
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read AcademicProfile|null $academicProfile
 * @property-read Address|null $address
 * @property-read CorporateProfile|null $corporateProfile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Institution> $institutions
 * @property-read int|null $institutions_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereApellidoMaterno($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereApellidoPaterno($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEdad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFechaNacimiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRFC($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTelefono($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */

class User extends Authenticatable
{
    use SoftDeletes, HasFactory, Notifiable;

    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'password',
        'RFC',
        'telefono',
        'fecha_nacimiento',
        'edad',
        'is_active', // <--- ¡ESTE ES EL IMPORTANTE QUE FALTABA!
        'address_id',
        'institution_id',
        'department_id',
        'workstation_id',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime', 
            'updated_at' => 'datetime',
        ];
    }

    // --- TRUCO DE COMPATIBILIDAD PARA LARAVEL ---
    // Laravel a veces busca 'name' internamente. Esto evita errores raros.
    public function getNameAttribute()
    {
        return "{$this->nombre} {$this->apellido_paterno}";
    }

    // --- RELACIONES ---

    public function billings(): HasMany
    {
        return $this->hasMany(Billing::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles_institution', 'user_id', 'role_id')
                    ->withPivot('institution_id', 'is_active') 
                    ->withTimestamps();
    }

    public function institutions(): BelongsToMany
    {
        return $this->belongsToMany(Institution::class, 'institution_user');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
    
    public function academicProfile(): HasOne
    {
        return $this->hasOne(AcademicProfile::class);
    }
   
    public function corporateProfile(): HasOne
    {
        return $this->hasOne(CorporateProfile::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_user');
    }

    public function completions()
    {
        return $this->hasMany(Completion::class);
    }

    // --- FUNCIONES AUXILIARES ---

    public function hasActiveRole(string $roleName): bool
    {
        return session('active_role_name') === $roleName;
    }

    public function hasAnyActiveRole(array $roles): bool
    {
        return in_array(session('active_role_name'), $roles);
    }

    public function hasRole(string $roleName): bool
    {
        return DB::table('user_roles_institution')
        ->join('roles', 'user_roles_institution.role_id', '=', 'roles.id')
        ->where('user_roles_institution.user_id', $this->id)
        ->where('roles.name', $roleName)
        ->where('user_roles_institution.is_active', true)
        ->exists();    
    }

    public function hasAnyRole(array $roles): bool
    {
        return DB::table('user_roles_institution')
        ->join('roles', 'user_roles_institution.role_id', '=', 'roles.id')
        ->where('user_roles_institution.user_id', $this->id)
        ->whereIn('roles.name', $roles)
        ->where('user_roles_institution.is_active', true)
        ->exists();
    }

    public function getAvailableRoles(): array
    {
        $contexts = [];

       $userContexts = DB::table('user_roles_institution')
        ->join('roles', 'user_roles_institution.role_id', '=', 'roles.id')
        ->join('institutions', 'user_roles_institution.institution_id', '=', 'institutions.id')
        ->where('user_roles_institution.user_id', $this->id)
        ->where('user_roles_institution.is_active', true)
        ->select(
            'institutions.id as institution_id',
            'institutions.name as institution_name',
            'institutions.logo_path',
            'roles.id as role_id',
            'roles.name as role_name',
            'roles.display_name',
            'user_roles_institution.is_active'
        )
        ->get();

        foreach ($userContexts as $context) {
            $contexts[] = (array) $context; 
        }

        return $contexts;
    }

    public function getRoleNames(): array
    {
        return $this->roles()->pluck('name')->toArray();
    }

    public function rolesInInstitution($institutionId)
    {
        return $this->roles()
            ->wherePivot('institution_id', $institutionId)
            ->get();
    }
}