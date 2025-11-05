@extends('layouts.app')

@section('title', 'Mi Información - ' . session('active_institution_name'))

@section('content')
 <div class="container">

     <div class="main-content">
            <div class="content-header">
                <div class="header-left">
                    <div class="page-title">TAREAS</div>
                </div>
                <div class="header-right">
                    <div class="welcome-message">¡Bienvenido(a) Juan perez!</div>
                    <div class="task-group-button create-task-button" id="btnCrearTarea">
                        <span>Crear tarea</span>
                    </div>
                </div>
            </div>
            
            <div class="task-group">
                <div class="task-group-button active" id="btnTodas">
                    <span>Todas</span>
                </div>
                <div class="task-group-button" id="btnEntregadas">
                    <span>Entregadas</span>
                </div>
                <div class="task-group-button" id="btnVencidas">
                    <span>Vencidas</span>
                </div>
            </div>
            
            <div class="content-area">
                <div class="content-header-bar">
                    <span class="header-bar-text">Nombre</span>
                    <span class="header-bar-text">Fecha de<br>apertura</span>
                    <span class="header-bar-text">Fecha de<br>vencimiento</span>
                    <span class="header-bar-text">Fecha de<br>cierre</span>
                    <span class="header-bar-text">Puntaje<br>máximo</span>
                    <span class="header-bar-text">%<br>cumplimiento</span>
                    <span class="header-bar-text">Acciones</span>
                </div>
                <div class="content-rectangle" id="task-list-container">
                    </div>
            </div>
        </div>
    </div>

    <div id="crearTareaModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Crear Nueva Tarea</h2>
                <span class="close-btn">&times;</span>
            </div>
            <div class="modal-body">
                <form id="newTaskForm">
                    <input type="hidden" id="taskBeingEdited">
                    
                    <div class="form-group">
                        <label for="taskNameInput">Nombre de la Tarea</label>
                        <input type="text" id="taskNameInput" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="maxScoreInput">Puntaje Máximo</label>
                        <input type="number" id="maxScoreInput" value="100" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="openDateInput">Fecha de Apertura</label>
                        <input type="date" id="openDateInput" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="dueDateInput">Fecha de Vencimiento</label>
                        <input type="date" id="dueDateInput" required>
                    </div>

                    <div class="form-group">
                        <label for="closeDateInput">Fecha de Cierre (Opcional)</label>
                        <input type="date" id="closeDateInput">
                    </div>
                    
                    <div class="form-group">
                        <label for="descriptionInput">Descripción (Opcional)</label>
                        <textarea id="descriptionInput" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="fileInput">Archivos Adjuntos (Opcional)</label>
                        <div class="file-control-container">
                            <input type="file" id="fileInput">
                            <button type="button" id="removeFileBtn" class="remove-file-btn" style="display:none;">Eliminar Archivo</button>
                        </div>
                        <small id="currentFile" style="font-size: 0.85em; color: #333; margin-top: 5px; font-style: italic;">Ningún archivo adjunto.</small>
                    </div>
                    
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn btn-cancel">Cancelar</button>
                <button type="submit" form="newTaskForm" class="modal-btn btn-save" id="btnGuardarActualizar">Guardar Tarea</button>
            </div>
        </div>
    </div>
    
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Confirmar Eliminación</h2>
                <span class="close-btn confirm-close">&times;</span>
            </div>
            <div class="modal-body">
                <p id="confirmMessage">¿Estás seguro de que quieres eliminar esta tarea?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn btn-confirm-cancel">No, Cancelar</button>
                <button type="button" class="modal-btn btn-confirm-delete">Sí, Eliminar</button>
            </div>
        </div>
    </div>
    
    <div id="notificationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Aviso</h2>
                <span class="close-btn notification-close">&times;</span>
            </div>
            <div class="modal-body">
                <p id="notificationMessage">¡Acción realizada con éxito!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn btn-notification-ok">Aceptar</button>
            </div>
        </div>
    </div>
    
    <div id="evaluationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="evaluationModalTitle">EVALUACIÓN DE TAREA</h2>
                <span class="close-btn evaluation-close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="evaluationModalForm">
                    <input type="hidden" id="taskBeingEvaluated">
                    
                    <div class="evaluation-item">
                        <label>Tarea:</label>
                        <span id="taskEvaluationName"></span>
                    </div>
                    
                    <div class="evaluation-item">
                        <label>Puntaje Máximo:</label>
                        <span id="taskEvaluationMaxScore"></span>
                    </div>
                    
                    <div class="evaluation-fields">
                        <div class="form-group evaluation-item">
                            <label for="scoreObtainedInput">Calificación Obtenida</label>
                            <input type="number" id="scoreObtainedInput" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="commentsInput">Comentarios de Evaluación</label>
                            <textarea id="commentsInput" rows="5"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn btn-cancel evaluation-close-btn">Cancelar</button>
                <button type="submit" form="evaluationModalForm" class="modal-btn btn-save-evaluation" id="btnSaveEvaluation">Guardar Evaluación</button>
            </div>
        </div>
    </div>
    <script>
        // =========================================================================
        // === CONFIGURACIÓN Y FUNCIONES DE PERSISTENCIA (localStorage) ===
        // =========================================================================

        let tasks = [];
        const STORAGE_KEY = 'uhta_tasks_list';

        function formatDate(dateString) {
            if (!dateString) {
                return '--/--/--';
            }
            const date = new Date(dateString + 'T00:00:00');
            if (isNaN(date)) {
                return '--/--/--';
            }
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        function reverseFormatDate(dateDMY) {
            if (dateDMY.includes('--')) return '';
            const parts = dateDMY.split('/');
            return parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : '';
        }

        function loadTasksFromStorage() {
            const storedTasks = localStorage.getItem(STORAGE_KEY);
            try {
                // Asegurar que todas las tareas tengan los campos necesarios al cargar
                tasks = storedTasks ? JSON.parse(storedTasks).map(task => ({
                    ...task,
                    fileName: task.fileName || '', // Archivo adjunto (Creación/Edición)
                    score: task.score || 0, // Nuevo campo: Puntaje obtenido
                    evalComment: task.evalComment || '' // Nuevo campo: Comentarios de evaluación
                })) : [];
            } catch (e) {
                console.error("Error al cargar tareas de localStorage:", e);
                tasks = [];
            }
            // Si no hay tareas, añade algunas de ejemplo para probar 
            if (tasks.length === 0) {
                 tasks.push({
                    id: 1, 
                    name: "Tarea de Ejemplo 1 (Con Archivo)", 
                    openDate: "2025-01-01", 
                    dueDate: "2025-01-15", 
                    closeDate: "", 
                    maxScore: 100, 
                    description: "Tarea de prueba con un archivo adjunto.",
                    fileName: "documento-importante.pdf",
                    score: 85, // Ejemplo de puntaje ya evaluado
                    evalComment: "Excelente trabajo, se cumplieron todos los requisitos."
                });
                 tasks.push({
                    id: 2, 
                    name: "Tarea de Ejemplo 2 (Sin Archivo)", 
                    openDate: "2025-09-20", 
                    dueDate: "2025-10-10", 
                    closeDate: "2025-10-15", 
                    maxScore: 80, 
                    description: "Esta es una tarea activa sin archivos.",
                    fileName: "",
                    score: 0, // Ejemplo de tarea sin evaluar
                    evalComment: ""
                });
                 saveTasksToStorage(); 
            }
            renderTasks();
        }

        function saveTasksToStorage() {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(tasks));
        }
        
        function saveAndRenderTasks() {
            saveTasksToStorage();
            renderTasks();
        }


        // =========================================================================
        // === MODALES DE CONFIRMACIÓN Y NOTIFICACIÓN (SIN CAMBIOS) ===
        // =========================================================================

        // MODAL DE CONFIRMACIÓN (Eliminar)
        const confirmModal = document.getElementById('confirmModal');
        const confirmMessageElement = document.getElementById('confirmMessage');
        const btnConfirmDelete = confirmModal.querySelector('.btn-confirm-delete');
        const btnConfirmCancel = confirmModal.querySelector('.btn-confirm-cancel');
        const confirmCloseBtn = confirmModal.querySelector('.confirm-close');

        function showCustomConfirm(message, callback) {
            confirmMessageElement.textContent = message;
            confirmModal.style.display = 'block';

            btnConfirmDelete.onclick = null; 
            btnConfirmCancel.onclick = null;
            confirmCloseBtn.onclick = null;

            btnConfirmDelete.onclick = function() {
                confirmModal.style.display = 'none';
                callback(true);
            };

            const cancelAction = function() {
                confirmModal.style.display = 'none';
                callback(false);
            };

            btnConfirmCancel.onclick = cancelAction;
            confirmCloseBtn.onclick = cancelAction;
            
            confirmModal.addEventListener('click', (event) => {
                if (event.target === confirmModal) {
                    cancelAction();
                }
            }, { once: true });
        }
        
        // MODAL DE NOTIFICACIÓN (Crear, Editar, Eliminar, Guardar Evaluación)
        const notificationModal = document.getElementById('notificationModal');
        const notificationMessageElement = document.getElementById('notificationMessage');
        const btnNotificationOk = notificationModal.querySelector('.btn-notification-ok');
        const notificationCloseBtn = notificationModal.querySelector('.notification-close');

        function showNotification(message, title = "Aviso") {
            notificationModal.querySelector('#notificationModal .modal-header h2').textContent = title;
            notificationMessageElement.textContent = message;
            notificationModal.style.display = 'block';

            btnNotificationOk.onclick = function() {
                notificationModal.style.display = 'none';
            };
            notificationCloseBtn.onclick = function() {
                notificationModal.style.display = 'none';
            };
            
            notificationModal.addEventListener('click', function closeOnOutsideClick(event) {
                if (event.target === notificationModal) {
                    notificationModal.style.display = 'none';
                    notificationModal.removeEventListener('click', closeOnOutsideClick);
                }
            }, { once: true });
        }


        // =========================================================================
        // === LÓGICA DE EVALUACIÓN DE TAREA (ANTERIORMENTE DETALLES - MODIFICADA) ===
        // =========================================================================

        const evaluationModal = document.getElementById('evaluationModal');
        const evaluationForm = document.getElementById('evaluationModalForm');
        const taskBeingEvaluated = document.getElementById('taskBeingEvaluated');
        const scoreInput = document.getElementById('scoreObtainedInput');
        const commentsInput = document.getElementById('commentsInput');

        function openEvaluationModal(taskData) {
            
            // Rellenar la información de la tarea
            document.getElementById('evaluationModalTitle').textContent = `EVALUACIÓN: ${taskData.name}`;
            document.getElementById('taskEvaluationName').textContent = taskData.name;
            document.getElementById('taskEvaluationMaxScore').textContent = taskData.maxScore;
            
            // Rellenar los campos de evaluación
            scoreInput.value = taskData.score || 0;
            scoreInput.max = taskData.maxScore; // Establecer el máximo dinámicamente
            commentsInput.value = taskData.evalComment || '';
            
            // Guardar la ID de la tarea a evaluar
            taskBeingEvaluated.value = taskData.id;
            
            evaluationModal.style.display = 'block';
        }

        evaluationForm.addEventListener('submit', (event) => {
            event.preventDefault();
            
            const taskId = taskBeingEvaluated.value;
            const newScore = parseInt(scoreInput.value);
            const newComment = commentsInput.value;
            const maxScore = parseInt(document.getElementById('taskEvaluationMaxScore').textContent);
            
            if (newScore > maxScore) {
                showNotification(`La calificación obtenida (${newScore}) no puede ser mayor que el puntaje máximo (${maxScore}).`, "Error de Evaluación");
                return;
            }
            
            const taskIndex = tasks.findIndex(t => t.id == taskId);
            
            if (taskIndex !== -1) {
                tasks[taskIndex].score = newScore;
                tasks[taskIndex].evalComment = newComment;
                
                saveAndRenderTasks();
                evaluationModal.style.display = 'none';
                showNotification(`La evaluación para "${tasks[taskIndex].name}" ha sido guardada.`, "Evaluación Guardada");
            } else {
                 showNotification("Error: No se encontró la tarea para guardar la evaluación.", "Error Interno");
            }
        });


        // =========================================================================
        // === LÓGICA DE DIBUJADO Y LISTENERS ===
        // =========================================================================
        
        function calculateCompliance(score, maxScore) {
             const compliance = maxScore > 0 ? (score / maxScore) * 100 : 0;
             return `${Math.round(compliance)}%`;
        }

        function attachTaskListeners(taskElement, taskData) {
            const deleteIcon = taskElement.querySelector('[data-action="delete"]');
            // MODIFICADO: Selecciona el nuevo icono de evaluación (antes 'view')
            const evaluationIcon = taskElement.querySelector('[data-action="view"]'); 
            const editIcon = taskElement.querySelector('[data-action="edit"]');

            if (deleteIcon) {
                deleteIcon.addEventListener('click', function() {
                    const message = `¿Estás seguro de que quieres eliminar la tarea "${taskData.name}"?`;
                    
                    showCustomConfirm(message, (confirmed) => {
                        if (confirmed) {
                            tasks = tasks.filter(t => t.id !== taskData.id);
                            saveAndRenderTasks();
                            showNotification(`Tarea "${taskData.name}" eliminada correctamente.`);
                        }
                    });
                });
            }
            
            if (evaluationIcon) {
                // CORREGIDO: Llama a openEvaluationModal
                evaluationIcon.addEventListener('click', function() {
                    openEvaluationModal(taskData); 
                });
            }
            
            if (editIcon) {
                editIcon.addEventListener('click', function() {
                    openEditModal(taskData);
                });
            }
        }
        
        function renderTasks() {
            const taskListContainer = document.getElementById('task-list-container');
            taskListContainer.innerHTML = '';

            tasks.forEach(task => {
                const newTask = document.createElement('div');
                newTask.className = 'task-item';
                newTask.setAttribute('data-id', task.id); 

                const displayCloseDate = formatDate(task.closeDate); 
                const compliance = calculateCompliance(task.score, task.maxScore);
                
                newTask.innerHTML = `
                    <img src="{{ asset('images/LOGO4.svg') }}" alt="Ícono de materia" class="task-icon-custom">
                    <span class="task-data">${task.name}</span>
                    <span class="task-data">${formatDate(task.openDate)}</span>
                    <span class="task-data">${formatDate(task.dueDate)}</span>
                    <span class="task-data">${displayCloseDate}</span> 
                    <span class="task-data">${task.maxScore}</span>
                    <span class="task-data">${compliance}</span>
                    <span class="task-data action-icons-wrapper">
                        <div class="action-icons-container">
                            
                            <img src="{{ asset('images/icono2.svg') }}" 
                                alt="Ícono de Evaluación" 
                                class="task-action-icon icon2" 
                                data-action="view" 
                                title="Evaluar Tarea">
                                
                            <img src="{{ asset('images/icono3.svg') }}" 
                                alt="Ícono de Editar" 
                                class="task-action-icon icon3" 
                                data-action="edit">
                            
                            <img src="{{ asset('images/icono1.svg') }}" 
                                alt="Ícono de Eliminar" 
                                class="task-action-icon icon1" 
                                data-action="delete">
                        </div>
                    </span>
                `;
                
                taskListContainer.appendChild(newTask);
                attachTaskListeners(newTask, task);
            });
            // Si la lista está vacía después del filtro, añadir mensaje.
             if (tasks.length === 0) {
                 taskListContainer.innerHTML = '<p style="text-align: center; padding: 50px; color: #777;">No hay tareas para mostrar.</p>';
             }
        }


        // =========================================================================
        // === LÓGICA DE LA MODAL (Crear / Editar) y Archivos ===
        // =========================================================================
        
        const modal = document.getElementById("crearTareaModal");
        const modalTitle = document.getElementById("modalTitle");
        const btnGuardarActualizar = document.getElementById("btnGuardarActualizar");
        const taskBeingEdited = document.getElementById("taskBeingEdited");
        const newTaskForm = document.getElementById("newTaskForm");

        const nameInput = document.getElementById('taskNameInput');
        const maxScoreInput = document.getElementById('maxScoreInput');
        const openDateInput = document.getElementById('openDateInput');
        const dueDateInput = document.getElementById('dueDateInput');
        const closeDateInput = document.getElementById('closeDateInput');
        const descriptionInput = document.getElementById('descriptionInput');
        const fileInput = document.getElementById('fileInput');
        const currentFileDisplay = document.getElementById('currentFile');
        const removeFileBtn = document.getElementById('removeFileBtn'); 

        // Variable global temporal para almacenar el nombre de archivo en edición/creación
        let tempFileName = ''; 

        /**
         * Actualiza la visualización del nombre del archivo y el botón de eliminar.
         * @param {string} fileName - Nombre del archivo (guardado o temporal).
         */
        function updateFileDisplay(fileName) {
            // Caso 1: Hay un nombre de archivo (guardado o temporal) o se seleccionó uno nuevo
            if (fileName || fileInput.files.length > 0) {
                const displayFileName = fileInput.files.length > 0 ? fileInput.files[0].name : fileName;
                currentFileDisplay.textContent = `Archivo actual: ${displayFileName}`;
                removeFileBtn.style.display = 'inline-block'; 
            } else {
                // Caso 2: No hay archivo
                currentFileDisplay.textContent = 'Ningún archivo adjunto.';
                removeFileBtn.style.display = 'none'; 
            }
        }

        function openEditModal(taskData) {
            // Guardar el nombre del archivo guardado en la variable temporal
            tempFileName = taskData.fileName || ''; 

            nameInput.value = taskData.name;
            maxScoreInput.value = taskData.maxScore;
            openDateInput.value = taskData.openDate;
            dueDateInput.value = taskData.dueDate;
            closeDateInput.value = taskData.closeDate;
            descriptionInput.value = taskData.description || '';
            
            // 1. Mostrar el estado del archivo (usa el valor de tempFileName)
            updateFileDisplay(tempFileName);
            // 2. Limpiar el input file por seguridad/compatibilidad
            fileInput.value = ''; 
            
            modalTitle.textContent = 'Editar Tarea: ' + taskData.name;
            btnGuardarActualizar.textContent = 'Actualizar Tarea';
            taskBeingEdited.value = taskData.id;
            
            modal.style.display = "block";
        }
        
        function openCreateModal() {
            // Reiniciar el estado del archivo temporal a vacío
            tempFileName = ''; 
            
            modalTitle.textContent = 'Crear Nueva Tarea';
            btnGuardarActualizar.textContent = 'Guardar Tarea';
            taskBeingEdited.value = '';
            
            newTaskForm.reset();
            updateFileDisplay(''); 
            modal.style.display = "block";
        }

        /**
         * Maneja la eliminación del adjunto (borra la selección o el nombre temporal).
         * NO GUARDA EN LOCALSTORAGE.
         */
        function removeFileAttachment() {
            // 1. Borrar la selección del input file
            fileInput.value = ''; 

            // 2. Borrar la referencia temporal (nombre de archivo asociado a la tarea en edición/creación)
            tempFileName = ''; 
            
            // 3. Actualizar la UI
            updateFileDisplay(''); 
            
            showNotification("Archivo adjunto eliminado de la selección. Pulsa Guardar para confirmar.", "Archivo Eliminado");
        }


        // =========================================================================
        // === MANEJO DE EVENTOS INICIALES ===
        // =========================================================================

        document.addEventListener('DOMContentLoaded', () => {
            
            loadTasksFromStorage(); 
            
            const createTaskButton = document.getElementById('btnCrearTarea'); 
            const closeBtn = modal.querySelector(".close-btn");
            const cancelBtn = modal.querySelector(".btn-cancel");
            // RENOMBRADO: detailsModal a evaluationModal
            const evaluationModal = document.getElementById('evaluationModal'); 
            const closeEvaluationBtns = evaluationModal.querySelectorAll(".evaluation-close, .evaluation-close-btn");
            
            
            // Evento para el botón de eliminar archivo (Maneja la eliminación temporal)
            removeFileBtn.addEventListener('click', function() {
                // Si la tarea tiene un nombre guardado O hay una selección en el input, muestra confirmación.
                if (tempFileName || fileInput.files.length > 0) {
                     showCustomConfirm('¿Estás seguro de que deseas eliminar el archivo adjunto de esta tarea? El cambio será permanente al pulsar "Guardar Tarea".', (confirmed) => {
                         if (confirmed) {
                             removeFileAttachment();
                         }
                     });
                } else {
                    removeFileAttachment(); 
                }
            });
            
            // Evento para manejar cuando se selecciona o cancela la selección de un archivo
            fileInput.addEventListener('change', function() {
                // Si se selecciona un archivo, actualiza la UI directamente desde el input.
                // Si se cancela la selección, updateFileDisplay usará el valor de tempFileName o mostrará vacío.
                const displayFileName = fileInput.files.length > 0 ? fileInput.files[0].name : tempFileName;
                updateFileDisplay(displayFileName); 
            });


            // RENOMBRADO: Cierre de la modal de Evaluación (antes detalles)
            closeEvaluationBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    evaluationModal.style.display = "none";
                });
            });

            // Lógica de los botones de filtro
            document.querySelectorAll('.task-group-button:not(.create-task-button)').forEach(button => {
                button.addEventListener('click', function() {
                    document.querySelectorAll('.task-group-button:not(.create-task-button)').forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            createTaskButton.addEventListener('click', openCreateModal);

            [closeBtn, cancelBtn].forEach(btn => {
                btn.addEventListener('click', () => {
                    modal.style.display = "none";
                });
            });

            window.addEventListener('click', (event) => {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
                // RENOMBRADO: Cerrar modal de Evaluación
                if (event.target == evaluationModal) { 
                    evaluationModal.style.display = "none";
                }
            });
            
            newTaskForm.addEventListener('submit', (event) => {
                event.preventDefault();
                
                const name = nameInput.value;
                const openDate = openDateInput.value;
                const dueDate = dueDateInput.value;
                const closeDate = closeDateInput.value;
                const maxScore = parseInt(maxScoreInput.value);
                const description = descriptionInput.value;
                
                // Determinar el nombre del archivo final al guardar:
                let finalFileName = fileInput.files.length > 0 ? fileInput.files[0].name : tempFileName;


                const taskId = taskBeingEdited.value;
                let message = '';

                if (taskId) {
                    const index = tasks.findIndex(t => t.id == taskId);
                    if (index !== -1) {
                        tasks[index] = {
                            ...tasks[index], // Mantiene score y evalComment existentes
                            name, openDate, dueDate, closeDate, maxScore, description,
                            fileName: finalFileName // Usar el nombre de archivo final
                        };
                        message = `Tarea "${name}" actualizada correctamente.`;
                    }
                } else {
                    const newTask = {
                        id: Date.now(), 
                        name, openDate, dueDate, closeDate, maxScore, description,
                        fileName: finalFileName, // Usar el nombre de archivo final
                        score: 0, // Inicializar score
                        evalComment: '' // Inicializar comentario
                    };
                    tasks.push(newTask);
                    message = `Tarea "${name}" creada manualmente y añadida a la lista.`;
                }
                
                // GUARDAR CAMBIOS Y CERRAR
                saveAndRenderTasks();
                modal.style.display = "none";
                
                showNotification(message);
            });
        });
    </script>
