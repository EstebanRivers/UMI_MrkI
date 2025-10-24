<?php

namespace App\Models\Users;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Users\Role;
use App\Models\Users\Institution;
use App\Models\Users\Address;
use App\Models\Users\AcademicProfile;
use App\Models\Users\CorporateProfile;
use Illuminate\Support\Facades\DB;

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
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Verifica si el rol ACTIVO en la sesión coincide con el nombre dado.
     */
    public function hasActiveRole(string $roleName): bool
    {
        return session('active_role_name') === $roleName;
    }

    /**
     * Verifica si el rol ACTIVO en la sesión está en la lista de roles.
     */
    public function hasAnyActiveRole(array $roles): bool
    {
        return in_array(session('active_role_name'), $roles);
    }

    /**
     * Verifica si el usuario tiene un rol asignado,
     * sin importar el contexto activo.
     */
    public function hasRole(string $roleName): bool
    {
        return DB::table('user_roles_institution')
        ->join('roles', 'user_roles_institution.role_id', '=', 'roles.id')
        ->where('user_roles_institution.user_id', $this->id)
        ->where('roles.name', $roleName)
        ->exists();    
    }

    /**
     * (Función original) Verifica si el usuario tiene alguno de los roles
     * especificados, sin importar el contexto activo.
     */
    public function hasAnyRole(array $roles): bool
    {
        return DB::table('user_roles_institution')
        ->join('roles', 'user_roles_institution.role_id', '=', 'roles.id')
        ->where('user_roles_institution.user_id', $this->id)
        ->whereIn('roles.name', $roles)
        ->exists();
    }

    public function getAvailableRoles(): array
    {
        $contexts = [];

       $userContexts = DB::table('user_roles_institution')
        ->join('roles', 'user_roles_institution.role_id', '=', 'roles.id')
        ->join('institutions', 'user_roles_institution.institution_id', '=', 'institutions.id')
        ->where('user_roles_institution.user_id', $this->id)
        ->select(
            'institutions.id as institution_id',
            'institutions.name as institution_name',
            'roles.id as role_id',
            'roles.name as role_name',
            'roles.display_name'
        )
        ->get();

    foreach ($userContexts as $context) {
        $contexts[] = (array) $context; // Convertir el objeto a array
    }

        return $contexts;
    }

    public function getRoleNames(): array
    {
        return $this->roles()->pluck('name')->toArray();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
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
        'address_id',
        'department_id',
        'workstation_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    
    //El rol que pertenece al usuario.
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles_institution')->withPivot('intitution_id');
    }

    //Las instituciones a las que pertenece el usuario.
    public function institutions(): BelongsToMany
    {
        return $this->belongsToMany(Institution::class, 'institution_user');
    }

    public function rolesInInstitution($institutionId)
    {
        return $this->roles()
            ->wherePivot('institution_id', $institutionId)
            ->get();
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    
    //El perfil académico asociado al usuario.
    public function academicProfile(): HasOne
    {
        return $this->hasOne(AcademicProfile::class);
    }

    
    // El perfil corporativo asociado al usuario.
    public function corporateProfile(): HasOne
    {
        return $this->hasOne(CorporateProfile::class);
    }

    
}
