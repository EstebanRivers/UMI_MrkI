<div id="miModal" class="modal">

  <div class="modal-contenido">
    <span class="cerrar">&times;</span> <h2>Agregar Nueva Carrera</h2>
    <form method="post" action="">
        @csrf <div>
            <label for="nombre">Nombre:</label>
            <input type="text" id="name" name="nombre">
        </div>
        <div>
            <label for="nombre">RVOE:</label>
            <input type="text" id="official_id" name="rvoe">
        </div>
        <div>
            <label for="nombre">Desc1:</label>
            <input type="text" id="description1" name="descripcion1">
        </div>
        <div>
            <label for="nombre">Desc2:</label>
            <input type="text" id="description2" name="descripcion2">
        </div>
        <div>
            <label for="nombre">Desc3:</label>
            <input type="text" id="description3" name="descripcion3">
        </div>
        
        <div class="options">
            <div class="Checkboxes">
                <label for="nombre">Modalidad:</label>
                <div>
                    <input type="radio" id="type" name="tipo_carrera" value="Presencial">
                    <label for="nombre">Presencial:</label>
                </div>
                <div>
                    <input type="radio" id="type" name="tipo_carrera" value="En linea">
                    <label for="nombre">En linea:</label>
                </div>
            </div>
            <div clas="lists">
                <label for="nombre">No. de semestres:</label>
                <select id="semesters" name="semestres">
                    @for ($i = 1; $i <= 15; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>
      <button type="submit">Guardar</button>
    </form>
  </div>

</div>