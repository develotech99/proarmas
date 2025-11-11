const formContainer = document.getElementById('formContainer');
const formTitle     = document.getElementById('formTitle');
const tipoForm      = document.getElementById('tipoForm');
const methodSpoof   = document.getElementById('methodSpoof');
const btnNueva      = document.getElementById('btnNueva');
const btnCancelar   = document.getElementById('btnCancelar');
const btnSubmit     = document.getElementById('btnSubmit');

function clearMethodSpoof() {
  const spoof = document.getElementById('spoof_method_input');
  if (spoof) spoof.remove();
  methodSpoof.innerHTML = '';
}

function addPutMethod() {
  clearMethodSpoof();
  const input = document.createElement('input');
  input.type  = 'hidden';
  input.name  = '_method';
  input.value = 'PUT';
  input.id    = 'spoof_method_input';
  methodSpoof.appendChild(input);
}

// NUEVO tipo
btnNueva.addEventListener('click', () => {
  formContainer.classList.remove('hidden');
  formTitle.textContent = 'Nuevo Tipo';
  tipoForm.reset();
  tipoForm.action = window.tipoarmaRoutes.store;

  clearMethodSpoof();
  // Botón en modo Guardar (verde)
  btnSubmit.textContent = "Guardar";
  btnSubmit.classList.remove("bg-blue-600","hover:bg-blue-700","focus:ring-blue-400");
  btnSubmit.classList.add("bg-green-600","hover:bg-green-700","focus:ring-green-400");
  document.getElementById('descripcion').focus();
});

// Cancelar
btnCancelar.addEventListener('click', () => {
  formContainer.classList.add('hidden');
  tipoForm.reset();
  clearMethodSpoof();
});

// Editar
document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-action="editar"]');
  if (!btn) return;

  const id          = btn.dataset.id;
  const descripcion = btn.dataset.descripcion;
  const situacion   = btn.dataset.situacion;

  formContainer.classList.remove('hidden');
  formTitle.textContent = 'Editar Tipo';

  document.getElementById('descripcion').value = descripcion;
  document.getElementById('situacion').value   = situacion;

  const updateUrl = window.tipoarmaRoutes.update.replace(':id', id);
  tipoForm.action = updateUrl;
  addPutMethod();

  // Botón en modo Editar (azul)
  btnSubmit.textContent = "Editar";
  btnSubmit.classList.remove("bg-green-600","hover:bg-green-700","focus:ring-green-400");
  btnSubmit.classList.add("bg-blue-600","hover:bg-blue-700","focus:ring-blue-400");

  document.getElementById('descripcion').focus();
});