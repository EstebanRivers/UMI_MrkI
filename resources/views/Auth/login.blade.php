<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - UHTA</title>
    @vite(['resources/css/login.css', 'resources/js/app.js'])
</head>
<body>
    <div class="login-container">
    <!-- Imagen lado izquierdo -->
    <div class="login-left">
        <img src="{{ asset('images/building.jpg') }}" alt="Logo UHTA" loading="lazy">
        <div class="logos">
            <img src="{{ asset('images/logos/logopalacio.png') }}" alt="Logo Palacio" loading="lazy" style="width: 160px; height: auto;">
            <img src="{{ asset('images/logos/logoprincess.png') }}" alt="Logo Princess" loading="lazy" style="width: 160px; height: auto;">
            <img src="{{ asset('images/logos/logopierre.png') }}" alt="Logo Pierre" loading="lazy" style="width: 160px; height: auto;">
            <img src="{{ asset('images/logos/logoumi.png') }}" alt="Logo UMI" loading="lazy" style="width: 150px; height: auto;">
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

                <!-- Campo usuario/correo -->
                <div class="form-group">
                    <label for="email">Usuario o correo electrónico</label>
                    <input type="text" id="email" name="email" 
                           placeholder="Ingrese su usuario"
                           value="{{ old('email') }}" required autofocus
                           autocomplete="username">
                    @error('email')
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
                        <span class="icon" ><img src="{{ asset('icons/eye-solid-full.svg') }}" alt="" style="width:18px;height:18px"></span>
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