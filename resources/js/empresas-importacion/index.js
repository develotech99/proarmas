const formContainer = document.getElementById('formContainer');
const formTitle = document.getElementById('formTitle');
const empresaForm = document.getElementById('empresaForm');
const methodSpoof = document.getElementById('methodSpoof');
const btnNueva = document.getElementById('btnNueva');
const btnCancelar = document.getElementById('btnCancelar');
const btnSubmit = document.getElementById('btnSubmit');

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

// NUEVA empresa
btnNueva.addEventListener('click', () => {
    formContainer.classList.remove('hidden');
    formTitle.textContent = 'Nueva Empresa de Importación';
    btnSubmit.textContent = "Guardar";
    empresaForm.reset();
    empresaForm.action = window.empresasRoutes.store;
    clearMethodSpoof();
    document.getElementById('descripcion').focus();
});

// Cancelar
btnCancelar.addEventListener('click', () => {
    formContainer.classList.add('hidden');
    empresaForm.reset();
    clearMethodSpoof();
});

// Editar
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="editar"]');
    if (!btn) return;

    const id = btn.dataset.id;
    const descripcion = btn.dataset.descripcion;
    const pais = btn.dataset.pais;
    const situacion = btn.dataset.situacion;

    formContainer.classList.remove('hidden');
    formTitle.textContent = 'Editar Empresa de Importación';
    btnSubmit.textContent = "Actualizar";

    document.getElementById('descripcion').value = descripcion;
    document.getElementById('pais').value = pais;
    document.getElementById('situacion').value = situacion;

    const updateUrl = window.empresasRoutes.update.replace(':id', id);
    empresaForm.action = updateUrl;
    addPutMethod();
    document.getElementById('descripcion').focus();
});

// Validación en tiempo real para la descripción (máximo 50 caracteres)
document.getElementById('descripcion').addEventListener('input', function() {
    const maxLength = 50;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    // Buscar o crear el contador
    let counter = this.parentNode.querySelector('.char-counter');
    if (!counter) {
        counter = document.createElement('span');
        counter.className = 'char-counter text-xs';
        this.parentNode.appendChild(counter);
    }
    
    // Actualizar el contador
    if (remaining >= 0) {
        counter.textContent = `${remaining} caracteres restantes`;
        counter.className = 'char-counter text-xs text-gray-500';
        this.classList.remove('border-red-300', 'ring-red-400');
        this.classList.add('border-gray-300', 'ring-blue-400');
    } else {
        counter.textContent = `${Math.abs(remaining)} caracteres de más`;
        counter.className = 'char-counter text-xs text-red-600';
        this.classList.remove('border-gray-300', 'ring-blue-400');
        this.classList.add('border-red-300', 'ring-red-400');
    }
});

// Limpiar contador al resetear el formulario
function resetForm() {
    empresaForm.reset();
    const counter = document.querySelector('.char-counter');
    if (counter) {
        counter.remove();
    }
    // Restaurar clases originales del input de descripción
    const descripcionInput = document.getElementById('descripcion');
    descripcionInput.classList.remove('border-red-300', 'ring-red-400');
    descripcionInput.classList.add('border-gray-300', 'ring-blue-400');
}

// Actualizar eventos para usar la nueva función resetForm
btnNueva.addEventListener('click', () => {
    formContainer.classList.remove('hidden');
    formTitle.textContent = 'Nueva Empresa de Importación';
    btnSubmit.textContent = "Guardar";
    resetForm();
    empresaForm.action = window.empresasRoutes.store;
    clearMethodSpoof();
    document.getElementById('descripcion').focus();
});

btnCancelar.addEventListener('click', () => {
    formContainer.classList.add('hidden');
    resetForm();
    clearMethodSpoof();
});

// Confirmación antes de eliminar
document.addEventListener('click', (e) => {
    const deleteBtn = e.target.closest('form[onsubmit*="confirm"] button[type="submit"]');
    if (deleteBtn) {
        // El evento onsubmit del formulario ya maneja la confirmación
        return;
    }
});

// Auto-hide messages después de 5 segundos
document.addEventListener('DOMContentLoaded', () => {
    const messages = document.querySelectorAll('[class*="bg-green-50"], [class*="bg-red-50"]');
    messages.forEach(message => {
        setTimeout(() => {
            if (message.parentNode) {
                message.style.transition = 'opacity 0.5s ease-out';
                message.style.opacity = '0';
                setTimeout(() => {
                    if (message.parentNode) {
                        message.remove();
                    }
                }, 500);
            }
        }, 5000);
    });
});