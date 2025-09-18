/* resources/js/prolicencias/index.js */
window.licenciasManager = function () {



  
  // ---------- helpers ----------
  const getJSONFromScript = (id) => {
    const el = document.getElementById(id);
    if (!el) return null;
    try { return JSON.parse(el.textContent || "null"); } catch { return null; }
  };

  const csrf = () =>
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  const API_BASE = (window.PROLICENCIAS_BASE || '/prolicencias').replace(/\/+$/,''); 

  const headersJSON = () => ({
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrf(),
    'Accept': 'application/json'
    // ⚠️ No seteamos Content-Type cuando body es FormData
  });

  // Convierte objeto (con arrays u objetos anidados) a FormData con notación de corchetes
  // Ej: armas[0][arma_sub_cat], armas[1][arma_cantidad], etc.
  const toFormDataDeep = (obj, fd = new FormData(), prefix = '') => {
    if (obj === null || obj === undefined) return fd;

    if (Array.isArray(obj)) {
      obj.forEach((val, idx) => {
        const key = prefix ? `${prefix}[${idx}]` : String(idx);
        toFormDataDeep(val, fd, key);
      });
      return fd;
    }

    if (typeof obj === 'object') {
      Object.keys(obj).forEach(k => {
        const val = obj[k];
        if (val === undefined || val === null) return;
        const key = prefix ? `${prefix}[${k}]` : k;
        if (typeof val === 'object' && !(val instanceof File) && !(val instanceof Blob)) {
          toFormDataDeep(val, fd, key);
        } else {
          fd.append(key, val);
        }
      });
      return fd;
    }

    fd.append(prefix, obj); // primitivo
    return fd;
  };

  const pick = (src, keys) => {
    const out = {};
    keys.forEach(k => { if (k in src) out[k] = src[k]; });
    return out;
  };

  const uid = () => `${Date.now()}-${Math.random().toString(36).slice(2)}`;

  // -------- Normalizadores de armas --------

  // MOSTRAR en UI
  const normalizeArmaInput = (raw = {}) => ({
    _rowKey: uid(),
    arma_lic_id: raw.arma_lic_id ?? raw.id ?? null,

    arma_sub_cat: raw.arma_sub_cat
      ?? raw.arma_subcate_id
      ?? raw.arma_subcategoria_id
      ?? raw.subcategoria_id
      ?? raw.subcategoria?.subcategoria_id
      ?? '',

    arma_modelo: raw.arma_modelo
      ?? raw.modelo_id
      ?? raw.modelo?.modelo_id
      ?? '',

    // calibre (desde campo, alias o relación)
    arma_calibre: raw.arma_calibre
      ?? raw.calibre_id
      ?? raw.calibre?.calibre_id
      ?? '',

    arma_empresa: raw.arma_empresa
      ?? raw.empresaimp_id
      ?? raw.empresa?.empresaimp_id
      ?? raw.empresaimp?.empresaimp_id
      ?? '',

    arma_largo_canon: raw.arma_largo_canon
      ?? raw.modelo?.largo_canon
      ?? '',

    arma_cantidad: raw.arma_cantidad ?? 1,
  });

  // ENVIAR al backend
  const normalizeArmaForBackend = (raw = {}, licId) => ({
    arma_num_licencia: Number(licId),
    arma_sub_cat:      raw.arma_sub_cat      !== '' ? Number(raw.arma_sub_cat)      : null,
    arma_modelo:       raw.arma_modelo       !== '' ? Number(raw.arma_modelo)       : null,
    arma_calibre:      raw.arma_calibre      !== '' ? Number(raw.arma_calibre)      : null,
    arma_empresa:      raw.arma_empresa      !== '' ? Number(raw.arma_empresa)      : null,
    arma_largo_canon:  raw.arma_largo_canon  !== '' ? Number(raw.arma_largo_canon)  : null,
    arma_cantidad:     raw.arma_cantidad     !== '' ? Number(raw.arma_cantidad)     : 1,
    ...(raw.arma_lic_id != null ? { arma_lic_id: Number(raw.arma_lic_id) } : {})
  });

  // ---------- estado inicial ----------
  const initialLicencias = getJSONFromScript('licencias-data') || [];

  return {
 licencias: initialLicencias,
    alerts: [],
    showModal: false,
    isEditing: false,
    isSubmitting: false,
    isViewing: false,  
    showDetailsModal: false,
    currentLicencia: null,
    licenciaId: null,  
    documentos: [],
    pendingPdfs: [],

    // filtros
    searchTerm: '',
    statusFilter: '',

    // form compuesto (licencia + armas[])
    formData: {
      lipaimp_id: '',
      lipaimp_poliza: '',
      lipaimp_descripcion: '',
      lipaimp_fecha_emision: '',
      lipaimp_fecha_vencimiento: '',
      lipaimp_observaciones: '',
      lipaimp_situacion: '',
      armas: []
    },

// En tu función addPdf, crea URLs temporales
addPdf() {
  const input = this.$refs.inputPdf || document.getElementById('inputPdf');
  if (!input) {
     console.warn('No se encontró input file');
     return;
   }

   input.value = '';
   input.onchange = async () => {
     const file = input.files?.[0] || null;
     if (!file) return;

     console.log('File selected:', file.name, file.size, file.type);

     // 🔥 COMPORTAMIENTO UNIFORME: Siempre agregar como pending
     // No importa si está editando o creando, siempre va a pending
     const fileWithPreview = {
       file: file,
       _url: URL.createObjectURL(file), // URL temporal para previsualización
       name: file.name,
       size: file.size,
       type: file.type
     };

     this.pendingPdfs.push(fileWithPreview);
     
     if (this.isEditing) {
       this.pushAlert('PDF agregado a la cola. Se subirá al actualizar la licencia.', 'success');
     } else {
       this.pushAlert('PDF agregado a la cola. Se subirá al guardar la licencia.', 'success');
     }
     
     console.log('Pending PDFs ahora:', this.pendingPdfs);
   };
   
   input.click();
},
async eliminarPendiente(index) {
  // Confirm opcional:
  const ok = await this.swalConfirmDelete('¿Quitar PDF?', 'Solo elimina la carga local (no servidor).');
  if (!ok) return;

  this.pendingPdfs.splice(index, 1);
  this.pushAlert('PDF quitado de la cola.', 'success');
},

async subirPdf(file, targetId = null) {
  // Esta función ahora solo se usa para agregar PDFs a licencias EXISTENTES
  // durante edición, no durante creación
  
  const id = targetId || this.licenciaId;
  
  if (!id) {
    // Durante creación, simplemente agregar a pendientes
    this.pendingPdfs.push(file);
    this.pushAlert('PDF en cola para subir cuando se guarde la licencia', 'info');
    return true;
  }

  const numericId = Number(id);
  const fd = new FormData();
  fd.append('pdf', file);

  try {
    const res = await fetch(`${API_BASE}/${encodeURIComponent(numericId)}/documentos`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf() },
      body: fd,
      credentials: 'same-origin'
    });
    
    const data = await res.json().catch(() => ({}));
    
    if (!res.ok || data?.ok === false) {
      throw new Error(data?.message || 'Error del servidor');
    }
    
    await this.cargarDocumentos();
    this.pushAlert('PDF agregado correctamente', 'success');
    return true;
    
  } catch (e) {
    console.error('Error subiendo PDF:', e);
    await this.swalError('Error al subir PDF', e.message);
    return false;
  }
},

async cargarDocumentos() {
  const id = this.licenciaId || this.formData?.lipaimp_id;

  if (!id || !Number.isInteger(Number(id))) {
    this.documentos = [];
    return;
  }

  try {
    const res = await fetch(`${API_BASE}/${encodeURIComponent(id)}/documentos`, {
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin',
    });

    if (!res.ok) {
      console.warn('cargarDocumentos: HTTP', res.status);
      this.documentos = [];
      return;
    }

    const data = await res.json().catch(() => ({}));

    if (data?.ok && Array.isArray(data.docs)) {
      this.documentos = data.docs;
    } else {
      this.documentos = [];
    }
  } catch (e) {
    console.error('cargarDocumentos error:', e);
    this.documentos = [];
  }
},

formatBytes(bytes) {
  const units = ['B','KB','MB','GB','TB'];
  if (!bytes) return '0 B';
  const i = Math.floor(Math.log(bytes) / Math.log(1024));
  return `${(bytes / Math.pow(1024, i)).toFixed(2)} ${units[i]}`;
},



    // ---------- UI helpers ----------
    pushAlert(message, type = 'success', timeout = 3500) {
      const id = Date.now() + Math.random();
      const item = { id, message, type };
      this.alerts.push(item);
      setTimeout(() => this.removeAlert(id), timeout);
    },
    removeAlert(id) {
      this.alerts = this.alerts.filter(a => a.id !== id);
    },

    async swalOk(title = 'Operación exitosa', text = '') {
      if (window.Swal) {
        await Swal.fire({ icon: 'success', title, text, confirmButtonText: 'OK' });
      } else {
        this.pushAlert(title, 'success');
      }
    },
    async swalError(title = 'Ocurrió un error', text = '') {
      if (window.Swal) {
        await Swal.fire({ icon: 'error', title, text, confirmButtonText: 'OK' });
      } else {
        this.pushAlert(`${title}${text ? ': ' + text : ''}`, 'error');
      }
    },
    async swalConfirmDelete(title = '¿Eliminar registro?', text = 'Esta acción no se puede deshacer') {
      if (window.Swal) {
        const res = await Swal.fire({
          icon: 'warning',
          title, text,
          showCancelButton: true,
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar'
        });
        return res.isConfirmed;
      }
      return confirm(text || title);
    },

    // ---------- Filtros ----------
    filterLicencias() { /* trigger reactividad */ },
    clearFilters() {
      this.searchTerm = '';
      this.statusFilter = '';
    },
    showLicencia(id) {
      const l = this.licencias.find(x => x.lipaimp_id === id);
      if (!l) return false;

      if (this.searchTerm) {
        const q = this.searchTerm.toString().toLowerCase();
        const text = [
          l.lipaimp_descripcion || '',
          l.lipaimp_observaciones || '',
          (l.lipaimp_id ?? '').toString()
        ].join(' ').toLowerCase();
        if (!text.includes(q)) return false;
      }
      if (this.statusFilter && String(l.lipaimp_situacion) !== String(this.statusFilter)) return false;

      return true;
    },

    // ---------- Modal / Form ----------
    openCreateModal() {
      this.isEditing = false;
      this.formData = {
        lipaimp_id: '',
        lipaimp_poliza: '',
        lipaimp_descripcion: '',
        lipaimp_fecha_emision: '',
        lipaimp_fecha_vencimiento: '',
        lipaimp_observaciones: '',
        lipaimp_situacion: '',
        armas: [] // vacío por defecto
      };
      this.showModal = true;
    },
    
    editLicencia(id) {
 const licId = Number(id);
const l = this.licencias.find(x => x.lipaimp_id === id);
    if (!l) return;

    this.licenciaId = licId; // ← MUY IMPORTANTE
    const armas = Array.isArray(l.armas) ? l.armas.map(normalizeArmaInput) : [];

  // Formatea las fechas a 'YYYY-MM-DD'
  const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toISOString().split('T')[0]; // Solo devuelve la fecha en formato 'YYYY-MM-DD'
  };

  this.isEditing = true;
  this.formData = {
    lipaimp_id: l.lipaimp_id || '',
    lipaimp_poliza: l.lipaimp_poliza ?? '',
    lipaimp_descripcion: l.lipaimp_descripcion ?? '',
    lipaimp_fecha_emision: formatDate(l.lipaimp_fecha_emision), // Formato adecuado para la fecha
    lipaimp_fecha_vencimiento: formatDate(l.lipaimp_fecha_vencimiento), // Formato adecuado para la fecha
    lipaimp_observaciones: l.lipaimp_observaciones ?? '', // Asegúrate que aquí no sea null ni undefined
    lipaimp_situacion: l.lipaimp_situacion ?? '',
    armas
  };

  //console.log('Formulario para edición:', this.formData); // Verificar los datos

  this.showModal = true;
  this.cargarDocumentos();
},


// En tu componente Alpine.js, agrega esta propiedad:
isFormBlocked: false,

openModal(id, mode = 'view') {
  //console.log('modo', mode);
  const l = this.licencias.find(x => x.lipaimp_id === id);
  if (!l) { this.swalError('Licencia no encontrada', `ID: ${id}`); return; }
  this.licenciaId = id;

  const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return isNaN(d.getTime()) ? '' : d.toISOString().split('T')[0];
  };

  this.isEditing = (mode === 'edit');
  this.isViewing = (mode === 'view');
  
  // Establecer flag de bloqueo
  this.isFormBlocked = (mode === 'view');
  
  this.formData = {
    lipaimp_id: l.lipaimp_id || '',
    lipaimp_poliza: l.lipaimp_poliza ?? '',
    lipaimp_descripcion: l.lipaimp_descripcion ?? '',
    lipaimp_fecha_emision: formatDate(l.lipaimp_fecha_emision),
    lipaimp_fecha_vencimiento: formatDate(l.lipaimp_fecha_vencimiento),
    lipaimp_observaciones: l.lipaimp_observaciones ?? '',
    lipaimp_situacion: l.lipaimp_situacion ?? '',
    armas: Array.isArray(l.armas) ? l.armas.map(normalizeArmaInput) : []
  };

  this.pendingPdfs = [];
  this.showModal = true;
  
  this.cargarDocumentos();

  // Solo configurar el observer si es modo view
  if (mode === 'view') {
    setTimeout(() => {
      this.setupFormBlocking();
    }, 100);
  }
},

// Nueva función para configurar el bloqueo
setupFormBlocking() {
  const form = document.querySelector('#formLicencia');
  if (!form) return;

  const blockElements = () => {
    if (!this.isFormBlocked) return; // Solo bloquear si el flag está activo
    
    form.querySelectorAll('input, select, textarea, button').forEach(el => {
      // Excluir botones específicos
      if (el.id === 'btnCancelar' || 
          el.getAttribute('@click')?.includes('closeModal') ||
          el.getAttribute('@click')?.includes('removeArma') ||
          el.textContent.toLowerCase().includes('quitar') ||
          el.textContent.toLowerCase().includes('cerrar')) {
        return; // No bloquear estos botones
      }
      
      // Bloquear el resto
      el.setAttribute('disabled', 'disabled');
      el.disabled = true;
      el.style.pointerEvents = 'none';
      el.style.opacity = '0.5';
      el.style.cursor = 'not-allowed';
      el.classList.add('force-disabled');
    });
  };

  // Bloquear elementos existentes
  blockElements();

  // Observer que respeta el flag
  const observer = new MutationObserver((mutations) => {
    if (!this.isFormBlocked) return; // No hacer nada si no está bloqueado
    blockElements();
  });

  observer.observe(form, { childList: true, subtree: true });
},

closeModal() {
  // PRIMERO desactivar el bloqueo
  this.isFormBlocked = false;
  
  // Limpiar cualquier bloqueo existente
  const form = document.querySelector('#formLicencia');
  if (form) {
    form.querySelectorAll('*').forEach(element => {
      if (['INPUT', 'SELECT', 'TEXTAREA', 'BUTTON'].includes(element.tagName)) {
        element.removeAttribute('disabled');
        element.removeAttribute('readonly');
        element.disabled = false;
        element.style.pointerEvents = '';
        element.style.opacity = '';
        element.style.cursor = '';
        element.classList.remove('force-disabled');
      }
    });
  }

  // Cambiar estados
  this.showModal = false;
  this.isEditing = false;
  this.isViewing = false;
  this.isSubmitting = false;
  this.licenciaId = null;
  this.pendingPdfs = [];
  
  this.formData = {
    lipaimp_id: '',
    lipaimp_poliza: '',
    lipaimp_descripcion: '',
    lipaimp_fecha_emision: '',
    lipaimp_fecha_vencimiento: '',
    lipaimp_observaciones: '',
    lipaimp_situacion: '',
    armas: []
  };
},
    eliminarPendiente(index) {
  if (this.pendingPdfs[index]?._url) {
    URL.revokeObjectURL(this.pendingPdfs[index]._url);
  }
  this.pendingPdfs.splice(index, 1);
},
// Agrega esta función a tu componente Alpine.js
// Llama a esta función pasando el ID del documento: eliminarPdf(doc.doclicimport_id)
// Llamar como: eliminarPdf(doc.doclicimport_id)
// dentro del objeto Alpine: return { ..., eliminarPdf: async function (docId) { ... } }
async eliminarPdf(docId) {
  try {
    if (!docId) {
      await Swal.fire('Error', 'ID de documento inválido.', 'error');
      return;
    }

    const confirm = await Swal.fire({
      title: '¿Eliminar documento?',
      text: 'Esta acción no se puede deshacer',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    });
    if (!confirm.isConfirmed) return;

    Swal.fire({ title: 'Eliminando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    // 👇 sin licId
    const url = `/prolicencias/documento/${encodeURIComponent(docId)}`;

    const response = await fetch(url, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      }
    });

    let data = {};
    if (response.headers.get('content-type')?.includes('application/json')) {
      data = await response.json().catch(() => ({}));
    }
    if (!response.ok || data.ok === false) {
      throw new Error(data.message || `Error HTTP ${response.status}`);
    }

    this.documentos = (this.documentos || []).filter(d => Number(d.doclicimport_id) !== Number(docId));

    Swal.fire({ title: '¡Eliminado!', text: data.message || 'Documento eliminado correctamente.', icon: 'success', timer: 1400, showConfirmButton: false });
  } catch (e) {
    console.error(e);
    Swal.fire('Error', e.message || 'Error al eliminar el documento', 'error');
  }
},


    // Validación mínima
validateForm() {
  const { lipaimp_id, lipaimp_situacion, armas } = this.formData;
  const baseOk = (this.isEditing ? true : !!lipaimp_id) && !!lipaimp_situacion;

  // Verificar armas
  if (!this.isEditing) {
    if (!Array.isArray(armas) || armas.length === 0) return false;
    const okArmas = armas.every(a =>
      a.arma_sub_cat && a.arma_modelo && a.arma_calibre && a.arma_empresa &&
      Number(a.arma_largo_canon) > 0 && Number(a.arma_cantidad) > 0
    );
    return baseOk && okArmas;
  }

  return baseOk;
}
,
    isFormValid() { return this.validateForm(); },

    // ---------- Gestión de armas en el form (repetidor) ----------
    addArma() {
      this.formData.armas.push({
        _rowKey: uid(),
        arma_lic_id: null,      // solo en update
        arma_sub_cat: '',
        arma_modelo: '',
        arma_calibre: '',       // 👈 incluido
        arma_empresa: '',
        arma_largo_canon: '',
        arma_cantidad: 1
      });
    },
    removeArma(index) {
      this.formData.armas.splice(index, 1);
    },

    // ---------- CRUD ----------
    
    async handleFormSubmit(e) {
      e.preventDefault();
      if (!this.isFormValid()) {
        this.pushAlert('Completa los campos requeridos (ID en creación, Estado y al menos un arma válida).', 'error');
        return;
      }
      this.isSubmitting = true;
      try {
        if (this.isEditing) {
          await this.updateLicencia();
        } else {
          await this.createLicencia();
        }
      } catch (err) {
        console.error(err);
        await this.swalError('Ocurrió un error al guardar', err.message || '');
      } finally {
        this.isSubmitting = false;
      }
    },
// tu función de subida

// FRONTEND - JavaScript separado en dos funciones

// FRONTEND - JavaScript separado en dos funciones

async createLicencia() {
  // 1) Clonamos y preparamos campos base
  const base = pick(this.formData, [
    'lipaimp_id',
    'lipaimp_poliza',
    'lipaimp_descripcion',
    'lipaimp_fecha_emision',
    'lipaimp_fecha_vencimiento',
    'lipaimp_observaciones',
    'lipaimp_situacion'
  ]);

  // 2) Normalizamos armas para backend
  const armasUI = Array.from(this.formData.armas || []); 
  const armas = armasUI.map(a => normalizeArmaForBackend(a, base.lipaimp_id));

  // 3) Validación defensiva
  for (const [i, a] of armas.entries()) {
    const falta = [];
    if (!a.arma_num_licencia) falta.push('arma_num_licencia');
    if (!a.arma_sub_cat)      falta.push('arma_sub_cat');
    if (!a.arma_modelo)       falta.push('arma_modelo');
    if (!a.arma_calibre)      falta.push('arma_calibre');
    if (!a.arma_empresa)      falta.push('arma_empresa');
    if (!(a.arma_largo_canon >= 0)) falta.push('arma_largo_canon');
    if (!a.arma_cantidad)     falta.push('arma_cantidad');
    if (falta.length) {
      await this.swalError('Faltan datos', `Arma #${i + 1}: ${falta.join(', ')}`);
      return;
    }
  }

  // 4) Construimos FormData SOLO para licencia y armas
  const fd = new FormData();
  fd.append('_token', csrf());
  Object.entries(base).forEach(([k, v]) => fd.append(k, v ?? ''));
  fd.append('lipaimp_situacion', this.formData.lipaimp_situacion);

  // 5) Procesar armas
  armas.forEach((a, i) => {
    fd.append(`armas[${i}][arma_num_licencia]`, a.arma_num_licencia);
    fd.append(`armas[${i}][arma_sub_cat]`, a.arma_sub_cat);
    fd.append(`armas[${i}][arma_modelo]`, a.arma_modelo);
    fd.append(`armas[${i}][arma_calibre]`, a.arma_calibre);
    fd.append(`armas[${i}][arma_empresa]`, a.arma_empresa);
    fd.append(`armas[${i}][arma_largo_canon]`, a.arma_largo_canon);
    fd.append(`armas[${i}][arma_cantidad]`, a.arma_cantidad);
    if (a.arma_lic_id != null) {
      fd.append(`armas[${i}][arma_lic_id]`, a.arma_lic_id);
    }

    // Alias para validaciones legadas
    fd.append(`armas[${i}][arma_subcate_id]`, a.arma_sub_cat);
    fd.append(`armas[${i}][arma_modelo_id]`, a.arma_modelo);
    fd.append(`armas[${i}][arma_calibre_id]`, a.arma_calibre);
    fd.append(`armas[${i}][calibre_id]`, a.arma_calibre);
    fd.append(`armas[${i}][arma_empresa_id]`, a.arma_empresa);
    fd.append(`armas[${i}][empresaimp_id]`, a.arma_empresa);
    fd.append(`armas[${i}][largo_canon]`, a.arma_largo_canon);
    fd.append(`armas[${i}][arma_licencia_id]`, a.arma_num_licencia);
  });

  this.isSubmitting = true;

  try {
    // PASO 1: Crear solo la licencia y armas
    console.log('🔥 PASO 1: Creando licencia y armas...');
    
    const res = await fetch(`${API_BASE}`, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: fd,
      credentials: 'same-origin'
    });

    const responseText = await res.text();
    let data;
    
    try {
      data = JSON.parse(responseText);
    } catch (parseError) {
      console.error('Error parsing JSON:', parseError);
      console.error('Raw response:', responseText);
      throw new Error(`Respuesta inválida del servidor: ${responseText.substring(0, 200)}...`);
    }
    
    if (!res.ok) {
      let errorMessage = 'Error al crear licencia';
      if (data?.errors) {
        const firstError = Object.values(data.errors)[0];
        errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
      } else if (data?.message) {
        errorMessage = data.message;
      }
      
      console.error('Server error:', data);
      await this.swalError('Error al crear', errorMessage);
      return;
    }

    console.log('✅ Licencia creada exitosamente:', data);
    
    // PASO 2: Si hay PDFs, subirlos por separado
    if (this.pendingPdfs && this.pendingPdfs.length > 0) {
      console.log('🔥 PASO 2: Iniciando subida de archivos PDF...');
      console.log('Cantidad de PDFs pendientes:', this.pendingPdfs.length);
      console.log('Estructura de data recibida:', data);
      
      const licenciaId = data.lipaimp_id || data.licencia?.lipaimp_id;
      console.log('ID de licencia extraído:', licenciaId);
      
      if (!licenciaId) {
        console.error('❌ No se pudo obtener el ID de la licencia:', data);
        await this.swalError('Error', 'No se pudo obtener el ID de la licencia para subir archivos');
        return;
      }

      console.log(`🔄 Llamando uploadPdfs con licenciaId: ${licenciaId}`);
      const uploadSuccess = await this.uploadPdfs(licenciaId);
      console.log('Resultado de uploadPdfs:', uploadSuccess);
      
      if (uploadSuccess) {
        console.log('✅ Archivos PDF subidos exitosamente');
      } else {
        console.warn('⚠️ Algunos archivos PDF no se pudieron subir');
        await this.swalError('Advertencia', 'La licencia se creó pero algunos archivos no se pudieron subir');
      }
    } else {
      console.log('ℹ️ No hay PDFs pendientes para subir');
    }

    // Limpiar y cerrar
    this.pendingPdfs = [];
    this.closeModal();
    await this.swalOk('¡Licencia creada exitosamente!');
    window.location.reload();
    
  } catch (error) {
    console.error('Error creating licencia:', error);
    await this.swalError('Error al crear licencia', error.message);
  } finally {
    this.isSubmitting = false;
  }
},

// NUEVA FUNCIÓN para subir PDFs por separado - CON MÁS DEBUG
async uploadPdfs(licenciaId) {
  console.log(`🔄 INICIO uploadPdfs - Licencia: ${licenciaId}, PDFs: ${this.pendingPdfs.length}`);
  
  try {
    // Debug detallado de pendingPdfs
    console.log('📋 Detalle de pendingPdfs:');
    this.pendingPdfs.forEach((item, index) => {
      console.log(`  ${index}:`, {
        type: typeof item,
        isFile: item instanceof File,
        hasFileProperty: item.hasOwnProperty('file'),
        has_fileProperty: item.hasOwnProperty('_file'),
        hasOriginalFileProperty: item.hasOwnProperty('originalFile'),
        keys: Object.keys(item),
        item: item
      });
    });

    // Crear FormData solo para archivos
    const fd = new FormData();
    fd.append('_token', csrf());
    fd.append('licencia_id', licenciaId);

    let validFiles = 0;
    
    this.pendingPdfs.forEach((item, index) => {
      console.log(`📎 Procesando archivo ${index}...`);
      
      // Extraer archivo con más opciones
      let pdfFile = null;
      if (item instanceof File) {
        pdfFile = item;
        console.log(`  ✅ Es File directo`);
      } else if (item.file instanceof File) {
        pdfFile = item.file;
        console.log(`  ✅ Extraído de .file`);
      } else if (item._file instanceof File) {
        pdfFile = item._file;
        console.log(`  ✅ Extraído de ._file`);
      } else if (item.originalFile instanceof File) {
        pdfFile = item.originalFile;
        console.log(`  ✅ Extraído de .originalFile`);
      } else {
        console.log(`  ❌ No se pudo extraer archivo del item:`, item);
      }
      
      if (pdfFile instanceof File) {
        fd.append('pdfs', pdfFile);  // Sin [] porque el backend espera 'pdfs'
        validFiles++;
        console.log(`  ✅ Archivo agregado al FormData:`, {
          name: pdfFile.name,
          size: pdfFile.size,
          type: pdfFile.type
        });
      } else {
        console.warn(`  ❌ Archivo ${index} no es válido`);
      }
    });

    if (validFiles === 0) {
      console.error('❌ No hay archivos válidos para subir');
      return false;
    }

    // Debug del FormData
    console.log('📦 FormData a enviar:');
    for (let [key, value] of fd.entries()) {
      if (value instanceof File) {
        console.log(`  ${key}: File(${value.name}, ${value.size} bytes)`);
      } else {
        console.log(`  ${key}: ${value}`);
      }
    }

    console.log(`🚀 Enviando ${validFiles} archivos a: /prolicencias/${licenciaId}/upload-pdfs`);

    // Llamar endpoint específico para subir archivos
    const res = await fetch(`/prolicencias/${licenciaId}/upload-pdfs`, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: fd,
      credentials: 'same-origin'
    });

    console.log('📡 Respuesta del servidor:');
    console.log('  Status:', res.status);
    console.log('  StatusText:', res.statusText);
    console.log('  Headers:', res.headers);

    const responseText = await res.text();
    console.log('📄 Response text:', responseText);
    
    let data;
    try {
      data = JSON.parse(responseText);
      console.log('📊 Parsed data:', data);
    } catch (parseError) {
      console.error('❌ Error parsing upload response:', parseError);
      console.error('Raw upload response:', responseText);
      return false;
    }

    if (res.ok) {
      console.log('✅ Archivos subidos exitosamente:', data);
      return true;
    } else {
      console.error('❌ Error subiendo archivos:', data);
      return false;
    }

  } catch (error) {
    console.error('💥 Error en uploadPdfs:', error);
    return false;
  }
},

cleanupTempUrls() {
  this.pendingPdfs.forEach(pdf => {
    if (pdf._url) {
      URL.revokeObjectURL(pdf._url);
    }
  });
},

async updateLicencia() {
    const id = this.formData.lipaimp_id;
    if (!id) throw new Error('ID requerido para actualizar');

    // 1) Clonamos y preparamos campos base
    const base = pick(this.formData, [
        'lipaimp_id',
        'lipaimp_poliza',
        'lipaimp_descripcion',
        'lipaimp_fecha_emision',
        'lipaimp_fecha_vencimiento',
        'lipaimp_observaciones',
        'lipaimp_situacion'
    ]);

    // 2) Normalizamos armas para backend
    const armasUI = Array.from(this.formData.armas || []); 
    const armas = armasUI.map(a => normalizeArmaForBackend(a, base.lipaimp_id));

    // 3) Validación defensiva
    for (const [i, a] of armas.entries()) {
        const falta = [];
        if (!a.arma_num_licencia) falta.push('arma_num_licencia');
        if (!a.arma_sub_cat)      falta.push('arma_sub_cat');
        if (!a.arma_modelo)       falta.push('arma_modelo');
        if (!a.arma_calibre)      falta.push('arma_calibre');
        if (!a.arma_empresa)      falta.push('arma_empresa');
        if (!(a.arma_largo_canon >= 0)) falta.push('arma_largo_canon');
        if (!a.arma_cantidad)     falta.push('arma_cantidad');
        if (falta.length) {
            await this.swalError('Faltan datos', `Arma #${i + 1}: ${falta.join(', ')}`);
            return;
        }
    }

    try {
        // 4) Construimos FormData manual para asegurar claves/índices
        const fd = new FormData();
        fd.append('_token', csrf());
        fd.append('_method', 'PUT');

        Object.entries(base).forEach(([k, v]) => fd.append(k, v ?? ''));

        // Procesar armas
        armas.forEach((a, i) => {
            fd.append(`armas[${i}][arma_num_licencia]`, a.arma_num_licencia);
            fd.append(`armas[${i}][arma_sub_cat]`, a.arma_sub_cat);
            fd.append(`armas[${i}][arma_modelo]`, a.arma_modelo);
            fd.append(`armas[${i}][arma_calibre]`, a.arma_calibre);
            fd.append(`armas[${i}][arma_empresa]`, a.arma_empresa);
            fd.append(`armas[${i}][arma_largo_canon]`, a.arma_largo_canon);
            fd.append(`armas[${i}][arma_cantidad]`, a.arma_cantidad);
            if (a.arma_lic_id != null) {
                fd.append(`armas[${i}][arma_lic_id]`, a.arma_lic_id);
            }

            // Alias para validaciones legadas
            fd.append(`armas[${i}][arma_subcate_id]`, a.arma_sub_cat);
            fd.append(`armas[${i}][arma_modelo_id]`, a.arma_modelo);
            fd.append(`armas[${i}][arma_calibre_id]`, a.arma_calibre);
            fd.append(`armas[${i}][calibre_id]`, a.arma_calibre);
            fd.append(`armas[${i}][arma_empresa_id]`, a.arma_empresa);
            fd.append(`armas[${i}][empresaimp_id]`, a.arma_empresa);
            fd.append(`armas[${i}][largo_canon]`, a.arma_largo_canon);
            fd.append(`armas[${i}][arma_licencia_id]`, a.arma_num_licencia);
        });

        // ✅ HEADERS CORREGIDOS - Sin Content-Type con FormData
        const res = await fetch(`${API_BASE}/${encodeURIComponent(id)}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf() // Solo el token CSRF
            },
            body: fd,
            credentials: 'same-origin'
        });

        const data = await res.json().catch(() => ({}));
        
        if (!res.ok) {
            const msg = this.firstError(data) || data?.message || 'Validación fallida';
            await this.swalError('Error al actualizar', msg);
            throw new Error(msg);
        }

        console.log('✅ Licencia actualizada exitosamente');
        console.log('Respuesta del servidor:', data);

        // ✅ OBTENER ID DE LICENCIA CORREGIDO
        // Ahora el controller devuelve lipaimp_id directamente
        const licenciaId = data.lipaimp_id || data.licencia?.lipaimp_id || id;

        // PASO 2: Crear/Subir PDFs nuevos si existen
        let uploadSuccess = true;
        if (this.pendingPdfs && this.pendingPdfs.length > 0) {
            console.log('📄 PASO 2: Creando nuevos archivos PDF...');
            console.log('Cantidad de PDFs nuevos a crear:', this.pendingPdfs.length);
            console.log(`📤 Llamando uploadPdfs para crear PDFs con licenciaId: ${licenciaId}`);
            
            // uploadPdfs() creará los nuevos archivos PDF y sus registros en BD
            uploadSuccess = await this.uploadPdfs(licenciaId);
            console.log('Resultado de creación de PDFs:', uploadSuccess);
        } else {
            console.log('ℹ️ No hay PDFs nuevos para crear');
        }

        // ✅ MENSAJE ÚNICO AL FINAL
        if (uploadSuccess) {
            await this.swalOk('¡Licencia actualizada exitosamente!');
            console.log('✅ Proceso completado exitosamente');
        } else {
            console.warn('⚠️ Algunos archivos PDF no se pudieron subir');
            await this.swalError('Advertencia', 'La licencia se actualizó pero algunos archivos no se pudieron subir');
        }

        // Limpiar y cerrar
        this.pendingPdfs = [];
        this.closeModal();
        window.location.reload();

    } catch (error) {
        console.error('❌ Error en updateLicencia:', error);
        // El error ya se mostró arriba, solo re-lanzamos si es necesario
        throw error;
    }
},

   // Función para eliminar licencia - USAR API_BASE
async deleteLicencia(licenciaId) {
  console.log('Eliminando licencia:', licenciaId);
  
  try {
    // Confirmar eliminación
    const confirmResult = await Swal.fire({
      title: '¿Estás seguro?',
      text: 'Se eliminará la licencia y todos sus archivos. Esta acción no se puede deshacer.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    });

    if (!confirmResult.isConfirmed) {
      return;
    }

    // Mostrar loading
    Swal.fire({
      title: 'Eliminando...',
      text: 'Eliminando licencia y archivos asociados',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    // Hacer petición DELETE - USAR API_BASE en lugar de BASE
    const response = await fetch(`${API_BASE}/${licenciaId}`, {
      method: 'DELETE',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf()
      },
      credentials: 'same-origin'
    });

    const responseText = await response.text();
    let data;

    try {
      data = JSON.parse(responseText);
    } catch (parseError) {
      console.error('Error parsing delete response:', parseError);
      throw new Error(`Respuesta inválida del servidor: ${responseText.substring(0, 100)}...`);
    }

    if (response.ok) {
      // Éxito
      await Swal.fire({
        title: '¡Eliminado!',
        text: 'La licencia y sus archivos han sido eliminados exitosamente.',
        icon: 'success',
        confirmButtonText: 'OK'
      });

      // Recargar página o actualizar lista
      window.location.reload();

    } else {
      // Error del servidor
      console.error('Error del servidor:', data);
      
      let errorMessage = 'Error eliminando licencia';
      if (data?.message) {
        errorMessage = data.message;
      } else if (data?.error) {
        errorMessage = data.error;
      }

      await Swal.fire({
        title: 'Error',
        text: errorMessage,
        icon: 'error',
        confirmButtonText: 'OK'
      });
    }

  } catch (error) {
    console.error('Error eliminando licencia:', error);
    
    await Swal.fire({
      title: 'Error',
      text: 'Ocurrió un error al eliminar la licencia: ' + error.message,
      icon: 'error',
      confirmButtonText: 'OK'
    });
  }
},

    // ---------- Detalles ----------
    viewDetails(id) {
      const licencia = this.licencias.find(l => l.lipaimp_id === id);
      if (licencia) {
        this.currentLicencia = licencia;
        this.showDetailsModal = true;
      }
    },
    closeDetailsModal() {
      this.showDetailsModal = false;
      this.currentLicencia = null;
    },

    // Primer mensaje de error del backend (Laravel validation)
    firstError(data) {
      if (data && data.errors) {
        const firstField = Object.keys(data.errors)[0];
        if (firstField && Array.isArray(data.errors[firstField]) && data.errors[firstField][0]) {
          return data.errors[firstField][0];
        }
      }
      return null;
    }
  };
};

