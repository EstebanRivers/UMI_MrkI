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

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    
    //El rol que pertenece al usuario.
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
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

    public function hasRole(string $role): bool
    {
        // Consulta si el usuario tiene un rol específico
        return $this->roles()->where('name', $role)->exists();
    }

    public function getAvailableRoles(): array
    {
        $contexts = [];

        // Carga las relaciones 'institutions' y 'roles' para optimizar.
        $this->load('institutions', 'roles');

        if ($this->institutions->isEmpty() || $this->roles->isEmpty()) {
            return $contexts;
        }

        // Como un usuario puede estar en varias instituciones y tener varios roles,
        // simplemente combinamos cada institución con cada rol.
        foreach ($this->institutions as $institution) {
            foreach ($this->roles as $role) {
                $contexts[] = [
                    'institution_id'   => $institution->id,
                    'institution_name' => $institution->name,
                    'role_id'          => $role->id,
                    'role_name'        => $role->name,
                    'display_name'     => $role->display_name,
                ];
            }
        }

        return $contexts;
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
}
