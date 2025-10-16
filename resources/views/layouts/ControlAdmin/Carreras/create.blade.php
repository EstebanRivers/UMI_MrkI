    <h2>Agregar Nueva Carrera</h2>
    <form method="post" action="{{ route('career.store') }}">
        @csrf 
        <div>
            <label for="nombre">Nombre:</label>
            <input type="text" id="name" name="name">
        </div>
        <div>
            <label for="nombre">RVOE:</label>
            <input type="text" id="official_id" name="official_id">
        </div>
        <div>
            <label for="nombre">Desc1:</label>
            <input type="text" id="description1" name="description1">
        </div>
        <div>
            <label for="nombre">Desc2:</label>
            <input type="text" id="description2" name="description2">
        </div>
        <div>
            <label for="nombre">Desc3:</label>
            <input type="text" id="description3" name="description3">
        </div>
        
        <div class="options">
            <div class="Checkboxes">
                <label for="nombre">Modalidad:</label>
                <div>
                    <input type="radio" id="type" name="type" value="Presencial">
                    <label for="nombre">Presencial:</label>
                </div>
                <div>
                    <input type="radio" id="type" name="type" value="En linea">
                    <label for="nombre">En linea:</label>
                </div>
            </div>
            <div clas="lists">
                <label for="nombre">No. de semestres:</label>
                <select id="semesters" name="semesters">
                    @for ($i = 1; $i <= 15; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>
        <button type="submit">Guardar</button>
    </form>