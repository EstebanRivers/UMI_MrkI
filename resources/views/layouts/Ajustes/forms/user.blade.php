
<div class="form-group">
    <label for="role_id">Rol del Usuario</label>
    <select id="role_id" name="role_id" required>
        <option value="">Seleccione un rol</option>
        @foreach($roles as $role)
            <option value="{{ $role->id }}"
                    {{ (isset($item) && $item->roles->first()->id == $role->id) ? 'selected' : '' }}>
                {{ $role->display_name }}
            </option>
        @endforeach
    </select>
</div>

{{-- ... otros campos como institution_id, department_id, workstation_id ... --}}
<hr style="margin: 15px 0;">

<div class="form-group">
    <label for="nombre">Nombre(s)</label>
    <input type="text" id="nombre" name="nombre" required
           value="{{ old('nombre', $item->nombre ?? '') }}">
</div>

<div class="form-group">
    <label for="apellido_paterno">Apellido Paterno</label>
    <input type="text" id="apellido_paterno" name="apellido_paterno" required
           value="{{ old('apellido_paterno', $item->apellido_paterno ?? '') }}">
</div>

<div class="form-group">
    <label for="apellido_materno">Apellido Materno</label>
    <input type="text" id="apellido_materno" name="apellido_materno"
           value="{{ old('apellido_materno', $item->apellido_materno ?? '') }}">
</div>

<div class="form-group">
    <label for="rfc">Usuario (RFC)</label>
    {{-- Asegúrate que el name coincida EXACTO con tu columna 'RFC' --}}
    <input type="text" id="rfc" name="RFC" required
           value="{{ old('RFC', $item->RFC ?? '') }}" style="text-transform: uppercase;">
</div>

<div class="form-group">
    <label for="email">Correo Electrónico</label>
    <input type="email" id="email" name="email" required
           value="{{ old('email', $item->email ?? '') }}">
</div>

{{-- ... campos de contraseña ... --}}
<hr style="margin: 15px 0;">

<div class="form-group">
    <label for="password">Contraseña</label>
    <input type="password" id="password" name="password" {{ isset($item) ? '' : 'required' }}>
    @if(isset($item))
        <small style="display: block; color: #555;">Dejar en blanco para no cambiar la contraseña.</small>
    @endif
</div>

<div class="form-group">
    <label for="password_confirmation">Confirmar Contraseña</label>
    <input type="password" id="password_confirmation" name="password_confirmation" {{ isset($item) ? '' : 'required' }}>
</div>