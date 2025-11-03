<form id="createFacilityForm">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    
    <div class="form-group">
        <label for="numero_aula">Número de Aula</label>
        <input type="text" id="numero_aula" name="numero_aula" class="form-control" maxlength="10" required>
    </div>

    <div class="form-group">
        <label for="seccion">Sección</label>
        <input type="text" id="seccion" name="seccion" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="capacidad">Capacidad</label>
        <input type="number" id="capacidad" name="capacidad" class="form-control" min="0">
    </div>

    <div class="form-group">
        <label for="ubicacion">Ubicación</label>
        <input type="text" id="ubicacion" name="ubicacion" class="form-control" maxlength="100">
    </div>

    <div class="form-group">
        <label for="tipo">Tipo</label>
        <select id="tipo" name="tipo" class="form-control" required>
            <option value="Aula" selected>Aula</option>
            <option value="Laboratorio">Laboratorio</option>
            <option value="Otro">Otro</option>
            </select>
    </div>
    
    <div class="modal-footer-custom" style="margin-top: 20px;">
        <button type="submit" class="btn btn-primary">Guardar Aula</button>
        <button type="button" class="btn btn-secondary" onclick="hideModal()">Cancelar</button>
    </div>
</form>

<div id="formMessages" style="margin-top: 15px;"></div>