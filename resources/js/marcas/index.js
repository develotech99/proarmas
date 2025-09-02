const formContainer = document.getElementById('formContainer');
const formTitle = document.getElementById('formTitle');
const marcaForm = document.getElementById('marcaForm');
const methodSpoof = document.getElementById('methodSpoof');
const btnNueva = document.getElementById('btnNueva');
const btnCancelar = document.getElementById('btnCancelar');

function clearMethodSpoof() {
    const spoof = document.getElementById('spoof_method_input');
    if (spoof) spoof.remove();
    methodSpoof.innerHTML = '';
}

function addPutMethod() {
    clearMethodSpoof();
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = '_method';
    input.value = 'PUT';
    input.id = 'spoof_method_input';
    methodSpoof.appendChild(input);
}

// NUEVA marca
btnNueva.addEventListener('click', () => {
    formContainer.classList.remove('hidden');
    formTitle.textContent = 'Nueva Marca';
    btnSubmit.textContent = "Guardar";
    marcaForm.reset();
    marcaForm.action = window.marcasRoutes.store;
    clearMethodSpoof();
    document.getElementById('descripcion').focus();
});

// Cancelar
btnCancelar.addEventListener('click', () => {
    formContainer.classList.add('hidden');
    marcaForm.reset();
    clearMethodSpoof();
});

// Editar
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="editar"]');
    if (!btn) return;

    const id = btn.dataset.id;
    const descripcion = btn.dataset.descripcion;
    const situacion = btn.dataset.situacion;

    formContainer.classList.remove('hidden');
    formTitle.textContent = 'Editar Marca';
    btnSubmit.textContent = "Editar";

    document.getElementById('descripcion').value = descripcion;
    document.getElementById('situacion').value = situacion;

    const updateUrl = window.marcasRoutes.update.replace(':id', id);
    marcaForm.action = updateUrl;
    addPutMethod();
    document.getElementById('descripcion').focus();
});