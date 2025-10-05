<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Role;
use App\Models\Institution;
use App\Models\Address;
use App\Models\AcademicProfile;
use App\Models\CorporateProfile;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    
    //El rol que pertenece al usuario.
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    //La institución a la que pertenece el usuario.
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
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

    public function hasRole(string $role): bool
    {
        // Consulta si el usuario tiene un rol específico
        return $this->roles()->where('name', $role)->exists();
    }

    public function getAvailableRoles(): array
    {
        $contexts = [];

        if ($this->relationLoaded('roles')) {
            $this->load('roles');
        }
        if ($this->relationLoaded('institution')) {
            $this->load('institution');
        }

        if ($this->roles->isEmpty()){
            return $contexts;
        }

        foreach ($this->roles as $role) {
            $contexts[] = [
                'institution_id' => $this->institution_id,
                'institution_name' => $this->institution->name,
                'role_id' => $role->id,
                'role_name' => $role->name,
                'display_name' => $role->display_name,
            ];
        }
        return $contexts;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
