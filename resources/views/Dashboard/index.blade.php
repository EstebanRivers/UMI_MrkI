@extends('layouts.app')

@section('title', 'Bienvenido - ' . session('active_institution_name'))

@section('content')
<div class="simple-welcome-container">
  <div class="welcome-content">
    <div class="logo-container">
        @if (session('active_institution_name') == 'Universidad Mundo Imperial')
            {{-- Si la institución activa es 'Universidad Mundo Imperial', muestra su logo específico --}}
            <img src="{{ asset('images/logos/Universidad Mundo Imperial.png') }}" alt="Logo Universidad Mundo Imperial" >
        @else
            {{-- Para TODAS las demás instituciones, muestra el logo por defecto --}}
            <img src="{{ asset('images/logos/logomundoimperial.png') }}" alt="Logo Principal" style="width: 60%; height: auto;" >
        @endif
    </div>
    <h1 class="welcome-message">
      @php
        $user = Auth::user();
        $primaryRole = $user->roles->first();
        $roleName = $primaryRole ? $primaryRole->display_name : 'Usuario';
        
        // Personalizar saludo según el rol
        $greeting = match($primaryRole?->name) {
          'master' => '¡Bienvenido(a) Master',
          'docente' => '¡Bienvenido(a) Maestro',
          'alumno' => '¡Bienvenido(a) Estudiante',
          'anfitrion' => '¡Bienvenido(a) Anfitrion',
          default => '¡Bienvenido(a)'
        };
      @endphp
      {{ $greeting }} {{ $user->nombre }}!
    </h1>
  </div>
</div>

<style>
.simple-welcome-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 70vh;
  width: 100%;
  background: var(--page-bg);
}

.welcome-content {
  text-align: center;
  max-width: 500px;
  padding: var(--spacing-xl);
}

.logo-container {
  margin-bottom: var(--spacing-xl);
}

.welcome-logo {
  width: 200px;
  height: auto;
  max-width: 100%;
  filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
}

.welcome-message {
  font-size: 28px;
  font-weight: 400;
  color: var(--text);
  margin: 0;
  line-height: 1.3;
}

/* Responsive */
@media (max-width: 600px) {
  .welcome-logo {
    width: 150px;
  }
  
  .welcome-message {
    font-size: 24px;
  }
  
  .simple-welcome-container {
    min-height: 60vh;
  }
  
  .welcome-content {
    padding: var(--spacing-lg);
  }
}
</style>
@endsection