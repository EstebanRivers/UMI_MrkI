<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>Iniciar Sesión - Mundo Imperial</title>
    @vite(['resources/css/login.css', 'resources/js/app.js'])
</head>
<body>
    <div class="login-container">
    <!-- Imagen lado izquierdo -->
    <div class="login-left">
        <img src="{{ asset('images/building.jpg') }}" alt="Logo UHTA" loading="lazy">
        <div class="logos">
            <img src="{{ asset('images/logos/Palacio Mundo Imperial.png') }}" alt="Logo Palacio" loading="lazy" style="width: 160px; height: auto;">
            <img src="{{ asset('images/logos/Princess Mundo Imperial.png') }}" alt="Logo Princess" loading="lazy" style="width: 160px; height: auto;">
            <img src="{{ asset('images/logos/Pierre Mundo Imperial.png') }}" alt="Logo Pierre" loading="lazy" style="width: 160px; height: auto;">
            <img src="{{ asset('images/logos/Universidad Mundo Imperial.png') }}" alt="Logo UMI" loading="lazy" style="width: 150px; height: auto;">
        </div>
    </div>

    <!-- Formulario lado derecho -->
    <div class="login-right">
        <div class="login-form">
            
            <!-- Logo -->
            <div class="logo">
                <img src="{{ asset('images/logos/logomundoimperial.png') }}" alt="Logo Mundo Imperial" loading="lazy">
            </div>
            
            <!-- Formulario -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="login">Usuario</label>
                    
                    {{-- !! CAMBIO IMPORTANTE: name="login" !! --}}
                    <input type="text" id="login" name="login" 
                           placeholder="Ingrese su RFC o Matrícula"
                           value="{{ old('login') }}" required autofocus
                           autocomplete="username">
                    
                    {{-- !! CAMBIO IMPORTANTE: @error('login') !! --}}
                    @error('login')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Campo contraseña -->
                <div class="form-group password-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" 
                               placeholder="Ingrese su contraseña"
                               required autocomplete="current-password">
                        <span class="toggle-password" onclick="togglePassword()">
                        <span class="icon" ><img src="{{ asset('/images/icons/eye-solid-full.svg') }}" alt="" style="width:18px;height:18px"></span>
                        </span>
                    </div>
                    @error('password')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Botón ingresar -->
                <button type="submit" class="login-btn">Ingresar</button>
            </form>
        </div>
    </div>
</div>

<!-- Script para mostrar/ocultar contraseña -->
<script>
    function togglePassword() {
        const input = document.getElementById('password');
        input.type = input.type === 'password' ? 'text' : 'password';
    }
</script>
</body>
</html>