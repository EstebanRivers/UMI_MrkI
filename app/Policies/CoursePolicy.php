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
            return $academicProfile && $course->career_id === $academicProfile->career_id;
        }

        // Un anfitrión solo puede ver el curso si está dirigido a su departamento o puesto.
        if ($user->hasActiveRole('anfitrion')) {
            $corporateProfile = $user->corporateProfile;
            return $corporateProfile && (
                $course->department_id === $corporateProfile->department_id ||
                $course->workstation_id === $corporateProfile->workstation_id
            );
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

        return $user->hasActiveRole('docente')
            ? $course->instructor_id == $user->id
            : false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Course $course): bool
    {
        return $user->hasActiveRole('master');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Course $course): bool
    {
        return $user->hasActiveRole('master');
    }
}
