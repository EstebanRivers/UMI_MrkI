<?php

namespace App\Observers;

use App\Models\Users\User;
use App\Models\Cursos\Course;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->enrollInAutoCourses($user->fresh());
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }

    /**
     * Lógica para inscribir al usuario en cursos relevantes.
     */
    protected function enrollInAutoCourses(User $user)
    {
        $courseIds = collect();

        // 1. Si tiene perfil corporativo
        if ($profile = $user->corporateProfile) {

            $departmentId = $profile->department_id;
            $workstationId = $profile->workstation_id;

            // Buscar cursos para su departamento...
            $courses = Course::whereHas('departments', fn($q) => $q->where('id', $departmentId))
                ->where(function ($query) use ($workstationId) {
                    // ...que sean para todos O para su puesto específico
                    $query->whereDoesntHave('workstations') // Para todos
                        ->orWhereHas('workstations', fn($q) => $q->where('id', $workstationId));
                })
                ->pluck('id'); // Obtenemos solo los IDs

            $courseIds = $courseIds->merge($courses);
        }

        // 2. Si tiene perfil académico
        if ($profile = $user->academicProfile) {

            // Buscar cursos para su carrera
            $courses = Course::whereHas('careers', fn($q) => $q->where('id', $profile->career_id))
                ->pluck('id');

            $courseIds = $courseIds->merge($courses);
        }

        // 3. Inscribir (sync para evitar duplicados)
        if ($courseIds->isNotEmpty()) {
            $user->courses()->syncWithoutDetaching($courseIds->unique());
            Log::info('Usuario inscrito automáticamente en cursos', [
                'user_id' => $user->id, 
                'courses' => $courseIds->unique()->all()
            ]);
        }
    }
}
