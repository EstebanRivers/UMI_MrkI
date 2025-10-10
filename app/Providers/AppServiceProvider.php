<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Cursos\Course; 
use App\Policies\CoursePolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    protected $policies = [
        Course::class => CoursePolicy::class, // <-- AÑADE ESTA LÍNEA
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
