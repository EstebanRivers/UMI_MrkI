<?php

namespace App\Policies;

use App\Models\Cursos\Course;
use App\Models\Users\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
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
        return $user->hasAnyActiveRole(['master', 'docente', 'alumno']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Course $course): bool
    {
        $activeInstitutionId = session('active_institution_id');

        return $course->institution_id == $activeInstitutionId;
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
