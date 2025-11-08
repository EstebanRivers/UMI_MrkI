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
use App\Models\Users\CorporativeProfile;
use App\Models\AdmonCont\Horario;
use Illuminate\Support\Facades\DB;

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
        'created_at',
        'updated_at'
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
            'created_at' => 'datetime', 
            'updated_at' => 'datetime',
        ];
    }

    
    //El rol que pertenece al usuario.
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles_institution');
    }

    //Las instituciones a las que pertenece el usuario.
    public function institutions(): BelongsToMany
    {
        return $this->belongsToMany(Institution::class, 'institution_user');
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
    public function corporativeProfile(): HasOne
    {
        return $this->hasOne(CorporativeProfile::class);
    }





}
