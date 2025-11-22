<?php

namespace App\Policies;

use App\Models\Cursos\Course;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    use HandlesAuthorization;
    /**
     * El método 'before' se ejecuta antes que cualquier otra regla.
     * Es perfecto para darle acceso total a un super-administrador.
     */
    public function before(User $user, string $ability): bool|null
    {
        // Si el usuario tiene el rol 'master', puede hacer cualquier cosa.
        if ($user->hasActiveRole('master')) {
            return true;
        }

        return null; // Si no es master, continuamos con las otras reglas.
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Course $course): bool
    {
        $activeInstitutionId = session('active_institution_id');

        if ($course->institution_id != $activeInstitutionId) {
            return false;
        }

        // Un docente puede ver cualquier curso de su institución.
        if ($user->hasActiveRole('docente')) {
            return true;
        }

        // Un estudiante solo puede ver el curso si está dirigido a su carrera.
        if ($user->hasActiveRole('estudiante')) {
            $academicProfile = $user->academicProfile;
            
            // CORREGIDO: Verificar que el perfil existe Y que el curso incluye la carrera.
            return $academicProfile && $course->careers->contains($academicProfile->career_id);
        }

        // Un anfitrión solo puede ver el curso si está dirigido a su departamento o puesto.
        if ($user->hasActiveRole('anfitrion')) {
            $corporateProfile = $user->corporateProfile;
            
            // Si el usuario no tiene perfil corporativo, no puede ver ningún curso.
            if (!$corporateProfile) {
                return false;
            }

            // --- LÓGICA CORREGIDA ---
            // El usuario SÍ puede ver el curso si:
            // 1. El curso está dirigido a su departamento Y...
            $isForDepartment = $course->departments->contains($corporateProfile->department_id);

            // 2. ... ( (El curso es para TODO el depto (lista de puestos vacía) ) O 
            //           (El curso SÍ tiene puestos Y el suyo está incluido) )
            $isForEveryoneInDept = $course->workstations->isEmpty();
            $isForTheirWorkstation = $course->workstations->contains($corporateProfile->workstation_id);

            return $isForDepartment && ($isForEveryoneInDept || $isForTheirWorkstation);
        }

        return false;

    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyActiveRole(['master', 'docente']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Course $course): bool
    {
        $activeInstitutionId = session('active_institution_id');

        if ($course->institution_id != $activeInstitutionId) {
            return false;
        }
        
        // El 'before' ya maneja al 'master', así que solo comprobamos 'docente'
        return $user->hasActiveRole('docente')
            ? $course->instructor_id == $user->id
            : false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Course $course): bool
    {
        $activeInstitutionId = session('active_institution_id');

        if ($course->institution_id != $activeInstitutionId) {
            return false;
        }

        // El 'before' ya maneja al 'master', así que solo comprobamos 'docente'
        return $user->hasActiveRole('docente')
            ? $course->instructor_id == $user->id
            : false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Course $course): bool
    {
        // Esta regla ya es manejada por el método 'before'
        return false; 
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Course $course): bool
    {
        // Esta regla ya es manejada por el método 'before'
        return false;
    }
}