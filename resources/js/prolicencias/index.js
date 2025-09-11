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

  const BASE = (window.PROLICENCIAS_BASE || '/prolicencias').replace(/\/+$/,''); 

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

  // Normaliza una fila de arma que pueda venir del backend (nombres viejos/relaciones) para MOSTRAR en UI
  const normalizeArmaInput = (raw = {}) => ({
    _rowKey: uid(),
    // id para UPDATE (en create será null/undefined)
    arma_lic_id: raw.arma_lic_id ?? raw.id ?? null,

    // nombres NUEVOS (caen back a nombres viejos o relaciones si vienen así)
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

  // Normaliza un arma de la UI para ENVIAR al backend según tu DDL pro_armas_licenciadas
  const normalizeArmaForBackend = (raw = {}, licId) => {
    return {
      arma_num_licencia: Number(licId),
      arma_sub_cat:      raw.arma_sub_cat != null && raw.arma_sub_cat !== '' ? Number(raw.arma_sub_cat) : null,
      arma_modelo:       raw.arma_modelo  != null && raw.arma_modelo  !== '' ? Number(raw.arma_modelo)  : null,
      arma_empresa:      raw.arma_empresa != null && raw.arma_empresa !== '' ? Number(raw.arma_empresa) : null,
      arma_largo_canon:  raw.arma_largo_canon != null && raw.arma_largo_canon !== '' ? Number(raw.arma_largo_canon) : null,
      arma_cantidad:     raw.arma_cantidad != null && raw.arma_cantidad !== '' ? Number(raw.arma_cantidad) : 1,
      // Para UPDATE opcionalmente podrías incluir arma_lic_id si existe:
      ...(raw.arma_lic_id != null ? { arma_lic_id: Number(raw.arma_lic_id) } : {})
    };
  };

  // ---------- estado inicial ----------
  const initialLicencias = getJSONFromScript('licencias-data') || [];

  return {
    // data
    licencias: initialLicencias,
    alerts: [],
    showModal: false,
    isEditing: false,
    isSubmitting: false,
    showDetailsModal: false,
    currentLicencia: null,

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
      const l = this.licencias.find(x => x.lipaimp_id === id);
      if (!l) return;

      // Normaliza armas que vengan del backend (nombres nuevos/viejos o relaciones)
      const armas = Array.isArray(l.armas) ? l.armas.map(normalizeArmaInput) : [];

      this.isEditing = true;
      this.formData = {
        lipaimp_id: l.lipaimp_id,
        lipaimp_poliza: l.lipaimp_poliza ?? '',
        lipaimp_descripcion: l.lipaimp_descripcion ?? '',
        lipaimp_fecha_emision: l.lipaimp_fecha_emision ?? '',
        lipaimp_fecha_vencimiento: l.lipaimp_fecha_vencimiento ?? '',
        lipaimp_observaciones: l.lipaimp_observaciones ?? '',
        lipaimp_situacion: l.lipaimp_situacion ?? '',
        armas
      };
      this.showModal = true;
    },

    closeModal() {
      this.showModal = false;
    },

    // Validación mínima (si la vista no sobreescribe en x-init, usamos esta)
    validateForm() {
      const { lipaimp_id, lipaimp_situacion, armas } = this.formData;
      const baseOk = (this.isEditing ? true : !!lipaimp_id) && !!lipaimp_situacion;

      if (!this.isEditing) {
        if (!Array.isArray(armas) || armas.length === 0) return false;
        const okArmas = armas.every(a =>
          a.arma_sub_cat && a.arma_modelo && a.arma_empresa &&
          Number(a.arma_largo_canon) > 0 && Number(a.arma_cantidad) > 0
        );
        return baseOk && okArmas;
      }
      return baseOk;
    },
    isFormValid() { return this.validateForm(); },

    // ---------- Gestión de armas en el form (repetidor) ----------
    addArma() {
      this.formData.armas.push({
        _rowKey: uid(),
        arma_lic_id: null,      // solo en update
        arma_sub_cat: '',
        arma_modelo: '',
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

      const armasUI = Array.from(this.formData.armas || []); 
      const armas = armasUI.map(a => normalizeArmaForBackend(a, base.lipaimp_id));


      for (const [i, a] of armas.entries()) {
        const falta = [];
        if (!a.arma_num_licencia) falta.push('arma_num_licencia');
        if (!a.arma_sub_cat)      falta.push('arma_sub_cat');
        if (!a.arma_modelo)       falta.push('arma_modelo');
        if (!a.arma_empresa)      falta.push('arma_empresa');
        if (!(a.arma_largo_canon >= 0)) falta.push('arma_largo_canon');
        if (!a.arma_cantidad)     falta.push('arma_cantidad');
        if (falta.length) {
          await this.swalError('Faltan datos', `Arma #${i + 1}: ${falta.join(', ')}`);
          return;
        }
      }

      // 4) Construimos FormData manual para asegurar claves/índices
      const fd = new FormData();
      fd.append('_token', csrf());
      Object.entries(base).forEach(([k, v]) => fd.append(k, v ?? ''));
      armas.forEach((a, i) => {
  // ===== claves NUEVAS (DDL actual) =====
  fd.append(`armas[${i}][arma_num_licencia]`, a.arma_num_licencia);
  fd.append(`armas[${i}][arma_sub_cat]`, a.arma_sub_cat);
  fd.append(`armas[${i}][arma_modelo]`, a.arma_modelo);
  fd.append(`armas[${i}][arma_empresa]`, a.arma_empresa);
  fd.append(`armas[${i}][arma_largo_canon]`, a.arma_largo_canon);
  fd.append(`armas[${i}][arma_cantidad]`, a.arma_cantidad);
  if (a.arma_lic_id != null) {
    fd.append(`armas[${i}][arma_lic_id]`, a.arma_lic_id);
  }

  // ===== ALIAS para validaciones LEGADAS =====
  // subcategoría
  fd.append(`armas[${i}][arma_subcate_id]`, a.arma_sub_cat);
  // modelo
  fd.append(`armas[${i}][arma_modelo_id]`, a.arma_modelo);
  // empresa (varios alias comunes)
  fd.append(`armas[${i}][arma_empresa_id]`, a.arma_empresa);
  fd.append(`armas[${i}][empresaimp_id]`, a.arma_empresa);
  // largo cañón
  fd.append(`armas[${i}][largo_canon]`, a.arma_largo_canon);
  // licencia
  fd.append(`armas[${i}][arma_licencia_id]`, a.arma_num_licencia);
});

// for (const [k, v] of fd.entries()) {
//   console.log('FD ->', k, v);
// }
const res = await fetch(`${BASE}`, {
  method: 'POST',
  headers: headersJSON(), // no seteamos Content-Type con FormData
  body: fd,
  credentials: 'same-origin'
});
      const data = await res.json();
      console.log('data',data)
     

if (data?.errors) console.log('validation errors keys:', Object.keys(data.errors));
      if (res.ok) {
        this.closeModal();
        await this.swalOk('¡Licencia creada exitosamente!');
        window.location.reload();
      } else {
        const msg = this.firstError(data) || data?.message || 'Validación fallida';
        await this.swalError('Error al crear', msg);
        throw new Error(msg);
      }
    },

    async updateLicencia() {
      const id = this.formData.lipaimp_id;
      if (!id) throw new Error('ID requerido para actualizar');

      // 1) Campos editables de la licencia
      const base = pick(this.formData, [
        'lipaimp_poliza',
        'lipaimp_descripcion',
        'lipaimp_fecha_emision',
        'lipaimp_fecha_vencimiento',
        'lipaimp_observaciones',
        'lipaimp_situacion'
      ]);

      // 2) Normalizamos armas (incluimos arma_lic_id si existe para que el backend haga upsert)
      const armasUI = Array.from(this.formData.armas || []);
      const armas = armasUI.map(a => normalizeArmaForBackend(a, id));

      // 3) Validación defensiva
      for (const [i, a] of armas.entries()) {
        const falta = [];
        if (!a.arma_num_licencia) falta.push('arma_num_licencia');
        if (!a.arma_sub_cat)      falta.push('arma_sub_cat');
        if (!a.arma_modelo)       falta.push('arma_modelo');
        if (!a.arma_empresa)      falta.push('arma_empresa');
        if (!(a.arma_largo_canon >= 0)) falta.push('arma_largo_canon');
        if (!a.arma_cantidad)     falta.push('arma_cantidad');
        if (falta.length) {
          await this.swalError('Faltan datos', `Arma #${i + 1}: ${falta.join(', ')}`);
          return;
        }
      }

      // 4) FormData
      const fd = new FormData();
      fd.append('_token', csrf());
      fd.append('_method', 'PUT');
      Object.entries(base).forEach(([k, v]) => fd.append(k, v ?? ''));
      armas.forEach((a, i) => {
  // ===== claves NUEVAS (DDL actual) =====
  fd.append(`armas[${i}][arma_num_licencia]`, a.arma_num_licencia);
  fd.append(`armas[${i}][arma_sub_cat]`, a.arma_sub_cat);
  fd.append(`armas[${i}][arma_modelo]`, a.arma_modelo);
  fd.append(`armas[${i}][arma_empresa]`, a.arma_empresa);
  fd.append(`armas[${i}][arma_largo_canon]`, a.arma_largo_canon);
  fd.append(`armas[${i}][arma_cantidad]`, a.arma_cantidad);
  if (a.arma_lic_id != null) {
    fd.append(`armas[${i}][arma_lic_id]`, a.arma_lic_id);
  }

  // ===== ALIAS para validaciones LEGADAS =====
  // subcategoría
  fd.append(`armas[${i}][arma_subcate_id]`, a.arma_sub_cat);
  // modelo
  fd.append(`armas[${i}][arma_modelo_id]`, a.arma_modelo);
  // empresa (varios alias comunes)
  fd.append(`armas[${i}][arma_empresa_id]`, a.arma_empresa);
  fd.append(`armas[${i}][empresaimp_id]`, a.arma_empresa);
  // largo cañón
  fd.append(`armas[${i}][largo_canon]`, a.arma_largo_canon);
  // licencia
  fd.append(`armas[${i}][arma_licencia_id]`, a.arma_num_licencia);
});

      const res = await fetch(`${BASE}/${encodeURIComponent(id)}`, {
        method: 'POST',
        headers: headersJSON(),
        body: fd,
        credentials: 'same-origin'
      });

      const ct = res.headers.get('content-type') || '';
      if (!ct.includes('application/json')) {
        if (res.ok) { window.location.reload(); return; }
        throw new Error('Error update (no json)');
      }

      const data = await res.json();
      if (res.ok) {
        this.closeModal();
        await this.swalOk('¡Licencia actualizada exitosamente!');
        window.location.reload();
      } else {
        const msg = this.firstError(data) || data?.message || 'Error al actualizar';
        await this.swalError('Error al actualizar', msg);
        throw new Error(msg);
      }
    },

    async deleteLicencia(id) {
      const confirmed = await this.swalConfirmDelete('¿Seguro que desea eliminar esta licencia?', 'Esta acción no se puede deshacer.');
      if (!confirmed) return;

      const fd = new FormData();
      fd.append('_method', 'DELETE');
      fd.append('_token', csrf());

      const res = await fetch(`${BASE}/${encodeURIComponent(id)}`, {
        method: 'POST',
        headers: headersJSON(),
        body: fd,
        credentials: 'same-origin'
      });

      const ct = res.headers.get('content-type') || '';
      if (!ct.includes('application/json')) {
        if (res.ok) { window.location.reload(); return; }
        await this.swalError('No se pudo eliminar');
        return;
      }

      const data = await res.json();
      if (res.ok) {
        await this.swalOk('¡Licencia eliminada exitosamente!');
        window.location.reload();
      } else {
        const msg = data?.message || 'No se pudo eliminar';
        await this.swalError('Error al eliminar', msg);
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
