/* resources/js/prolicencias/index.js */
window.licenciasManager = function () {
  // -------- helpers --------
  const getJSONFromScript = (id) => {
    const el = document.getElementById(id);
    if (!el) return null;
    try { return JSON.parse(el.textContent || "null"); } catch { return null; }
  };

  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const BASE = (window.PROLICENCIAS_BASE || '/prolicencias').replace(/\/+$/,''); 

  const headersJSON = () => ({
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrf(),
    'Accept': 'application/json'
  });

  const toFormData = (obj) => {
    const fd = new FormData();
    Object.keys(obj).forEach(k => {
      if (obj[k] === undefined || obj[k] === null) return;
      fd.append(k, obj[k]);
    });
    return fd;
  };

  // -------- estado inicial --------
  const initialLicencias = getJSONFromScript('licencias-data') || [];
  const empresas = getJSONFromScript('empresas-data') || [];
  const modelos = getJSONFromScript('modelos-data') || [];


  return {
    // datos
    licencias: initialLicencias,
    empresas,
    modelos,
    alerts: [],
    showModal: false,
    isEditing: false,
    isSubmitting: false,

    // filtros
    searchTerm: '',
    empresaFilter: '',
    modeloFilter: '',
    statusFilter: '',

    // form - CORREGIDO: todos los campos con nombres correctos
    formData: {
      id: null,
      lipaimp_empresa: '',
      lipaimp_modelo: '',
      //lipaimp_marca: '',
      lipaimp_largo_canon: '', // ✅ CORREGIDO: de lipaimp_largo_cañon a lipaimp_largo_canon
      lipaimp_poliza: '',
      lipaimp_numero_licencia: '',
      lipaimp_descripcion: '',
      lipaimp_fecha_emision: '',
      lipaimp_fecha_vencimiento: '',
      lipaimp_observaciones: '',
      lipaimp_situacion: '',
      lipaimp_cantidad_armas: ''
    },

    // ------------- UI helpers -------------
    pushAlert(message, type = 'success', timeout = 3500) {
      const id = Date.now() + Math.random();
      const item = { id, message, type };
      this.alerts.push(item);
      setTimeout(() => this.removeAlert(id), timeout);
    },
    removeAlert(id) {
      this.alerts = this.alerts.filter(a => a.id !== id);
    },

    // ------------- Filtros -------------
    filterLicencias() {
      // función vacía pero necesaria para la reactividad
    },
    clearFilters() {
      this.searchTerm = '';
      this.empresaFilter = '';
      this.modeloFilter = '';
      this.statusFilter = '';
    },
    showLicencia(id) {
      const l = this.licencias.find(x => x.lipaimp_id === id);
      if (!l) return false;

      // search en descripción (case-insensitive)
      if (this.searchTerm) {
        const q = this.searchTerm.toString().toLowerCase();
        const text = (l.lipaimp_descripcion || '').toString().toLowerCase();
        if (!text.includes(q)) return false;
      }
      // empresa
      if (this.empresaFilter && String(l.lipaimp_empresa) !== String(this.empresaFilter)) {
        return false;
      }
      // modelo
      if (this.modeloFilter && String(l.lipaimp_modelo) !== String(this.modeloFilter)) {
        return false;
      }
      // estado
      if (this.statusFilter && String(l.lipaimp_situacion) !== String(this.statusFilter)) {
        return false;
      }
      return true;
    },

    // ------------- Modal / Form -------------
    openCreateModal() {
      this.isEditing = false;
      this.formData = {
        id: null,
        lipaimp_empresa: '',
        lipaimp_modelo: '',
        //lipaimp_marca: '',
        lipaimp_largo_canon: '', // ✅ CORREGIDO
        lipaimp_poliza: '',
        lipaimp_numero_licencia: '',
        lipaimp_descripcion: '',
        lipaimp_fecha_emision: '',
        lipaimp_fecha_vencimiento: '',
        lipaimp_observaciones: '',
        lipaimp_situacion: '',
        lipaimp_cantidad_armas: ''
      };
      this.showModal = true;
    },
    editLicencia(id) {
      const l = this.licencias.find(x => x.lipaimp_id === id);
      if (!l) return;
      this.isEditing = true;
      this.formData = {
        id: l.lipaimp_id,
        lipaimp_empresa: l.lipaimp_empresa ?? '',
        lipaimp_modelo: l.lipaimp_modelo ?? '',
        //lipaimp_marca: l.lipaimp_marca ?? '',
        lipaimp_largo_canon: l.lipaimp_largo_canon ?? '', // ✅ CORREGIDO
        lipaimp_poliza: l.lipaimp_poliza ?? '',
        lipaimp_numero_licencia: l.lipaimp_numero_licencia ?? '',
        lipaimp_descripcion: l.lipaimp_descripcion ?? '',
        lipaimp_fecha_emision: l.lipaimp_fecha_emision ?? '',
        lipaimp_fecha_vencimiento: l.lipaimp_fecha_vencimiento ?? '',
        lipaimp_observaciones: l.lipaimp_observaciones ?? '',
        lipaimp_situacion: l.lipaimp_situacion ?? '',
        lipaimp_cantidad_armas: l.lipaimp_cantidad_armas ?? ''
      };
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
    },
    validateForm() {
      // valida campos requeridos según tu controlador
      const { 
        lipaimp_empresa, 
        lipaimp_modelo,
        lipaimp_largo_canon, // ✅ CORREGIDO
        lipaimp_situacion,
        lipaimp_cantidad_armas 
      } = this.formData;
      
      return !!(lipaimp_empresa && lipaimp_modelo && lipaimp_largo_canon && 
                lipaimp_situacion && lipaimp_cantidad_armas);
    },
    isFormValid() {
      return this.validateForm();
    },

    // ------------- CRUD -------------
    async handleFormSubmit(e) {
      e.preventDefault();
      if (!this.isFormValid()) {
        this.pushAlert('Completa todos los campos requeridos.', 'error');
        return;
      }
      this.isSubmitting = true;
      try {
        if (this.isEditing) {
          await this.updateLicencia();
        } else {
          await this.createLicencia();
        }
        this.closeModal();
      } catch (err) {
        console.error(err);
        this.pushAlert('Ocurrió un error al guardar: ' + err.message, 'error');
      } finally {
        this.isSubmitting = false;
      }
    },

    async createLicencia() {
      const payload = { ...this.formData };
      delete payload.id;
 console.log('Payload enviado:', payload);
      const res = await fetch(`${BASE}`, {
        method: 'POST',
        headers: headersJSON(),
        body: toFormData(payload)
      });

      const ct = res.headers.get('content-type') || '';
      if (!ct.includes('application/json')) {
        if (res.ok) { window.location.reload(); return; }
        throw new Error('Error create (no json)');
      }

      const data = await res.json();
      if (res.ok) {
        const nuevo = data?.licencia || data || {};
        const item = Object.keys(nuevo).length ? nuevo : { ...payload, lipaimp_id: nuevo.lipaimp_id || Date.now() };
        this.licencias.unshift(item);
        this.pushAlert('¡Licencia creada exitosamente!');
      } else {
        const msg = data?.message || data?.errors?.lipaimp_largo_canon?.[0] || 'Validación fallida';
        this.pushAlert(msg, 'error');
        throw new Error(msg);
      }
    },

    async updateLicencia() {
      const id = this.formData.id;
      if (!id) throw new Error('ID requerido para actualizar');
      const payload = { ...this.formData };
      delete payload.id;

      const res = await fetch(`${BASE}/${encodeURIComponent(id)}`, {
        method: 'POST',
        headers: headersJSON(),
        body: (() => {
          const fd = toFormData(payload);
          fd.append('_method', 'PUT');
          return fd;
        })()
      });

      const ct = res.headers.get('content-type') || '';
      if (!ct.includes('application/json')) {
        if (res.ok) { window.location.reload(); return; }
        throw new Error('Error update (no json)');
      }

      const data = await res.json();
      if (res.ok) {
        const actualizado = data?.licencia || data || {};
        this.licencias = this.licencias.map(l =>
          l.lipaimp_id === id ? { ...l, ...actualizado } : l
        );
        this.pushAlert('¡Licencia actualizada exitosamente!');
      } else {
        const msg = data?.message || data?.errors?.lipaimp_largo_canon?.[0] || 'Error al actualizar';
        this.pushAlert(msg, 'error');
        throw new Error(msg);
      }
    },

    async deleteLicencia(id) {
      if (!confirm('¿Seguro que desea eliminar esta licencia?')) return;

      const res = await fetch(`${BASE}/${encodeURIComponent(id)}`, {
        method: 'POST',
        headers: headersJSON(),
        body: (() => {
          const fd = new FormData();
          fd.append('_method', 'DELETE');
          return fd;
        })()
      });

      const ct = res.headers.get('content-type') || '';
      if (!ct.includes('application/json')) {
        if (res.ok) { window.location.reload(); return; }
        this.pushAlert('No se pudo eliminar.', 'error');
        return;
      }

      const data = await res.json();
      if (res.ok) {
        this.licencias = this.licencias.filter(l => l.lipaimp_id !== id);
        this.pushAlert('¡Licencia eliminada exitosamente!');
      } else {
        const msg = data?.message || 'No se pudo eliminar';
        this.pushAlert(msg, 'error');
      }
    },

    // ------------- Función para ver detalles -------------
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
    }
  };
};