// resources/js/pagos/administrar.js
// DataTables v2 + Tailwind, SIN jQuery
import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import Swal from 'sweetalert2';

// =========================
// Config & helpers
// =========================
const API_BASE = '/admin/pagos';
const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
const setText = (id, txt) => { const el = document.getElementById(id); if (el) el.textContent = txt; };
const show = (id) => document.getElementById(id)?.classList.remove('hidden');
const hide = (id) => document.getElementById(id)?.classList.add('hidden');

const fmtQ = (n) => 'Q ' + Number(n || 0).toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const toast = (title, icon = 'info') => Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2200, icon, title });

const fetchJSON = async (url, { method = 'GET', body = null, isForm = false } = {}) => {
    const headers = {};
    if (CSRF) headers['X-CSRF-TOKEN'] = CSRF;
    if (!isForm) headers['Content-Type'] = 'application/json';

    const res = await fetch(url, {
        method,
        headers,
        body: isForm ? body : (body ? JSON.stringify(body) : null),
    });
    if (!res.ok) {
        const text = await res.text().catch(() => '');
        throw new Error(`HTTP ${res.status}: ${text || res.statusText}`);
    }
    return res.json();
};

const debounce = (fn, ms = 400) => {
    let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
};

// =========================
// Estado global
// =========================
const state = {
    saldos: [],         // [{metodo_id, metodo, caja_saldo_moneda, caja_saldo_monto_actual}]
    uploadTmp: null,    // {path, headers, rows}
};

// =========================
// DataTables locales (ES)
// =========================
const DT_ES = {
    aria: { sortAscending: ': Activar para ordenar asc', sortDescending: ': Activar para ordenar desc' },
    processing: 'Procesando...',
    search: 'Buscar:',
    lengthMenu: 'Mostrar _MENU_',
    info: 'Mostrando _START_ a _END_ de _TOTAL_',
    infoEmpty: 'Mostrando 0 a 0 de 0',
    infoFiltered: '(filtrado de _MAX_ totales)',
    loadingRecords: 'Cargando...',
    zeroRecords: 'No se encontraron resultados',
    emptyTable: 'No hay datos disponibles',
    paginate: { first: 'Primero', previous: 'Anterior', next: 'Siguiente', last: 'Último' },
};

// =========================
// Stats / dashboard
// =========================
async function loadStats() {
    try {
        const r = await fetchJSON(`${API_BASE}/dashboard-stats`);
        const { saldo_total_gtq = 0, saldos = [], pendientes = 0, ultima_carga = null } = r || {};

        state.saldos = saldos;
        setText('saldoCajaTotalGTQ', fmtQ(saldo_total_gtq));

        const efectivo = saldos.find(s => (s.metodo || '').toLowerCase().includes('efectivo'));
        setText('saldoEfectivoGTQ', fmtQ(efectivo?.caja_saldo_monto_actual || 0));
        setText('contadorPendientes', String(pendientes));
        setText('ultimaCargaEstado', ultima_carga ? new Date(ultima_carga).toLocaleString() : '—');

        fillMetodoSelects();
    } catch (e) {
        toast(`No se pudieron cargar las estadísticas: ${e.message}`, 'error');
    }
}

function fillMetodoSelects() {
    const filtroMetodo = $('#filtroMetodo');
    const egMetodo = $('#egMetodo');
    if (filtroMetodo) {
        filtroMetodo.innerHTML = `<option value="">Todos los métodos</option>` +
            state.saldos.map(m => `<option value="${m.metodo_id}">${m.metodo}</option>`).join('');
    }
    if (egMetodo) {
        egMetodo.innerHTML = state.saldos.map(m => `<option value="${m.metodo_id}">${m.metodo}</option>`).join('');
    }
}

// =========================
/** BANDEJA: DataTable sin jQuery */
// =========================
let dtPendientes;

function initDtPendientes() {
    const el = document.getElementById('tablaPendientes');
    if (!el) return;

    dtPendientes = new DataTable(el, {
        language: DT_ES,
        responsive: true,
        autoWidth: false,
        searching: false,
        processing: true,
        columns: [
            { title: 'Fecha', data: 'fecha', render: v => (v ? new Date(v).toLocaleDateString() : '—') },
            { title: 'Venta', data: 'venta_id', render: v => (v ? `#${v}` : '—') },
            { title: 'Cliente', data: 'cliente', defaultContent: '—' },
            { title: 'Concepto', data: 'concepto', className: 'text-xs text-gray-600', defaultContent: '—' },
            { title: 'Debía', data: 'debia', className: 'text-right', render: v => fmtQ(v) },
            { title: 'Depositado', data: 'depositado', className: 'text-right', render: v => fmtQ(v) },
            {
                title: 'Diferencia',
                data: 'diferencia',
                className: 'text-right',
                render: v => {
                    const n = Number(v || 0);
                    const cls = n === 0 ? 'text-emerald-600' : n > 0 ? 'text-amber-600' : 'text-rose-600';
                    return `<span class="${cls}">${fmtQ(n)}</span>`;
                }
            },
            {
                title: 'Comprobante',
                data: null,
                className: 'text-center',
                render: row => {
                    if (!row?.imagen) return '—';
                    const img = encodeURIComponent(row.imagen);
                    const ref = row.referencia || '';
                    const fecha = row.fecha || '';
                    const monto = row.depositado || 0;
                    return `<button class="text-blue-600 hover:underline btn-ver-comp"
                    data-img="${img}" data-ref="${ref}" data-fecha="${fecha}" data-monto="${monto}">
                    Ver
                  </button>`;
                }
            },
            {
                title: 'Acciones',
                data: null,
                className: 'text-center',
                render: row => {
                    return `<button class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-3 py-1.5 rounded-lg btn-validar"
                    data-ps="${row.ps_id}" data-venta="${row.venta_id}"
                    data-debia="${row.debia}" data-hizo="${row.depositado}">
                    Validar
                  </button>`;
                }
            },
        ],
        data: [], // se carga via loadPendientes()
    });

    // Delegación de eventos del tbody
    el.tBodies[0].addEventListener('click', (ev) => {
        const btn = ev.target.closest('button');
        if (!btn) return;
        if (btn.classList.contains('btn-ver-comp')) {
            const img = decodeURIComponent(btn.getAttribute('data-img') || '');
            const ref = btn.getAttribute('data-ref') || '';
            const fecha = btn.getAttribute('data-fecha') || '';
            const monto = btn.getAttribute('data-monto') || 0;
            openModalComprobante({ img, ref, fecha, monto });
        } else if (btn.classList.contains('btn-validar')) {
            openModalValidar({
                ps: Number(btn.getAttribute('data-ps')),
                venta: Number(btn.getAttribute('data-venta')),
                debia: Number(btn.getAttribute('data-debia') || 0),
                hizo: Number(btn.getAttribute('data-hizo') || 0),
            });
        }
    });

    // Filtros externos
    const buscar = $('#buscarFactura');
    const filtroEstado = $('#filtroEstado');
    if (buscar) buscar.addEventListener('input', debounce(loadPendientes, 400));
    if (filtroEstado) filtroEstado.addEventListener('change', loadPendientes);
}

async function loadPendientes() {
    try {
        const q = $('#buscarFactura')?.value?.trim() || '';
        const estado = $('#filtroEstado')?.value || '';

        const url = new URL(`${API_BASE}/pendientes`, window.location.origin);
        if (q) url.searchParams.set('q', q);
        if (estado) url.searchParams.set('estado', estado);

        const res = await fetchJSON(url.toString());
        const rows = res?.data || [];
        if (!rows.length) show('emptyPendientes'); else hide('emptyPendientes');

        dtPendientes?.clear();
        dtPendientes?.rows?.add(rows);
        dtPendientes?.draw();
    } catch (e) {
        toast(`Error cargando pendientes: ${e.message}`, 'error');
        dtPendientes?.clear().draw();
        show('emptyPendientes');
    }
}

// =========================
/** MOVIMIENTOS: DataTable sin jQuery */
// =========================
let dtMovs;

function computeMonthRange() {
    const val = $('#filtroMes')?.value || '';
    const pad = (n) => String(n).padStart(2, '0');
    if (val) {
        const [y, m] = val.split('-').map(Number);
        const first = new Date(y, m - 1, 1);
        const last = new Date(y, m, 0);
        return {
            from: `${first.getFullYear()}-${pad(first.getMonth() + 1)}-${pad(first.getDate())}`,
            to: `${last.getFullYear()}-${pad(last.getMonth() + 1)}-${pad(last.getDate())}`,
        };
    }
    const now = new Date();
    const first = new Date(now.getFullYear(), now.getMonth(), 1);
    const last = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    return {
        from: `${first.getFullYear()}-${pad(first.getMonth() + 1)}-${pad(first.getDate())}`,
        to: `${last.getFullYear()}-${pad(last.getMonth() + 1)}-${pad(last.getDate())}`,
    };
}

function initDtMovs() {
    const el = document.getElementById('tablaMovimientos');
    if (!el) return;

    dtMovs = new DataTable(el, {
        language: DT_ES,
        responsive: true,
        autoWidth: false,
        searching: false,
        processing: true,
        order: [[0, 'desc']],
        columns: [
            { title: 'Fecha', data: 'cja_fecha', render: v => (v ? new Date(v).toLocaleString() : '—') },
            { title: 'Tipo', data: 'cja_tipo' },
            { title: 'Referencia', data: 'cja_no_referencia', defaultContent: '—' },
            { title: 'Método', data: 'metodo', defaultContent: '—' },
            {
                title: 'Monto',
                data: 'cja_monto',
                className: 'text-right',
                render: (v, _t, row) => {
                    const esIn = ['VENTA', 'DEPOSITO', 'AJUSTE_POS'].includes(row?.cja_tipo || '');
                    return `<span class="${esIn ? 'text-emerald-600' : 'text-rose-600'}">${fmtQ(v)}</span>`;
                }
            },
            { title: 'Estado', data: 'cja_situacion' },
        ],
        data: [], // via loadMovimientos()
    });

    $('#btnFiltrarMovs')?.addEventListener('click', loadMovimientos);
}

async function loadMovimientos() {
    try {
        const metodoId = $('#filtroMetodo')?.value || '';
        const { from, to } = computeMonthRange();
        const url = new URL(`${API_BASE}/movimientos`, window.location.origin);
        url.searchParams.set('from', from);
        url.searchParams.set('to', to);
        if (metodoId) url.searchParams.set('metodo_id', metodoId);

        const r = await fetchJSON(url.toString());
        const rows = r?.data || [];
        const total = Number(r?.total || 0);

        dtMovs?.clear();
        dtMovs?.rows?.add(rows);
        dtMovs?.draw();

        setText('totalMovimientosMes', fmtQ(total));
    } catch (e) {
        toast(`No se pudieron cargar movimientos: ${e.message}`, 'error');
        dtMovs?.clear().draw();
        setText('totalMovimientosMes', fmtQ(0));
    }
}

// =========================
/** PREVIEW CSV/XLSX: DataTable sin jQuery */
// =========================
let dtPreview;

function initDtPreview() {
    const el = document.getElementById('tablaPrevia');
    if (!el) return;

    dtPreview = new DataTable(el, {
        language: DT_ES,
        responsive: true,
        autoWidth: false,
        searching: false,
        order: [[0, 'asc']],
        columns: [
            { title: 'Fecha', data: 'fecha' },
            { title: 'Descripción', data: 'descripcion' },
            { title: 'Referencia', data: 'referencia' },
            { title: 'Monto', data: 'monto', className: 'text-right', render: v => fmtQ(v) },
            { title: 'Detectado', data: 'detectado', defaultContent: '—' },
        ],
        data: [],
    });
}

function renderPreview(rows) {
    const safe = (rows || []).map(r => ({
        fecha: r.fecha || '—',
        descripcion: r.descripcion || '—',
        referencia: r.referencia || '—',
        monto: r.monto ?? 0,
        detectado: r.detectado ?? '—',
    }));
    dtPreview?.clear();
    dtPreview?.rows?.add(safe);
    dtPreview?.draw();
    show('vistaPrevia');
    setText('totalMovimientos', String(safe.length));
}

// =========================
// Modales (sin jQuery)
// =========================
function openModal(sel) { document.querySelector(sel)?.classList.remove('hidden'); }
function closeModal(sel) { document.querySelector(sel)?.classList.add('hidden'); }

function bindModalClose() {
    $$('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-close-modal');
            if (target) closeModal(target);
        });
    });
}

function openModalComprobante({ img, ref, fecha, monto }) {
    const base = img?.startsWith('http') ? img : (`/storage/${img}`);
    const imgEl = $('#imgComprobante'); if (imgEl) imgEl.src = base || '';
    const link = $('#btnDescargarComprobante'); if (link) link.href = base || '#';
    setText('refComprobante', ref || '—');
    setText('fechaComprobante', fecha ? new Date(fecha).toLocaleString() : '—');
    setText('montoComprobante', fmtQ(monto || 0));
    openModal('#modalComprobante');
}

function openModalValidar({ ps, venta, debia, hizo }) {
    setText('mvVenta', `#${venta}`);
    setText('mvDebia', fmtQ(debia));
    setText('mvHizo', fmtQ(hizo));
    setText('mvDif', fmtQ(hizo - debia));
    setText('mvMetodo', '—');

    const btnA = $('#btnAprobar');
    const btnR = $('#btnRechazar');
    if (btnA) { btnA.setAttribute('data-ps-id', String(ps)); btnA.setAttribute('data-venta-id', String(venta)); }
    if (btnR) { btnR.setAttribute('data-ps-id', String(ps)); btnR.setAttribute('data-venta-id', String(venta)); }
    openModal('#modalValidar');
}

async function aprobarPago() {
    const psId = $('#btnAprobar')?.getAttribute('data-ps-id');
    const obs = $('#mvObs')?.value || '';
    if (!psId) return;
    try {
        const r = await fetchJSON(`${API_BASE}/aprobar`, { method: 'POST', body: { ps_id: Number(psId), observaciones: obs } });
        if (!r?.ok) throw new Error(r?.msg || 'Error desconocido');
        toast('Pago aprobado', 'success');
        closeModal('#modalValidar');
        await Promise.all([loadStats(), loadPendientes(), loadMovimientos()]);
    } catch (e) { toast(`No se pudo aprobar: ${e.message}`, 'error'); }
}

async function rechazarPago() {
    const psId = $('#btnRechazar')?.getAttribute('data-ps-id');
    const motivo = $('#mvObs')?.value || '';
    if (!psId) return;
    if (!motivo || motivo.length < 5) return toast('Indica el motivo (mín 5 caracteres).', 'error');
    try {
        const r = await fetchJSON(`${API_BASE}/rechazar`, { method: 'POST', body: { ps_id: Number(psId), motivo } });
        if (!r?.ok) throw new Error(r?.msg || 'Error desconocido');
        toast('Pago rechazado', 'success');
        closeModal('#modalValidar');
        await Promise.all([loadStats(), loadPendientes()]);
    } catch (e) { toast(`No se pudo rechazar: ${e.message}`, 'error'); }
}

// =========================
// Egresos
// =========================
function openModalEgreso() { openModal('#modalEgreso'); }

async function guardarEgreso() {
    const egFecha = $('#egFecha')?.value || '';
    const egMetodo = $('#egMetodo')?.value || '';
    const egMonto = $('#egMonto')?.value || '';
    const egMotivo = $('#egMotivo')?.value || '';
    const egReferencia = $('#egReferencia')?.value || '';
    const egArchivo = $('#egArchivo')?.files?.[0] || null;

    if (!egMetodo || !egMonto || !egMotivo) return toast('Completa método, monto y motivo.', 'error');

    const fd = new FormData();
    if (egFecha) fd.append('fecha', egFecha);
    fd.append('metodo_id', egMetodo);
    fd.append('monto', egMonto);
    fd.append('motivo', egMotivo);
    if (egReferencia) fd.append('referencia', egReferencia);
    if (egArchivo) fd.append('archivo', egArchivo);

    try {
        const r = await fetchJSON(`${API_BASE}/egresos`, { method: 'POST', body: fd, isForm: true });
        if (!r?.ok) throw new Error(r?.msg || 'Error desconocido');
        toast('Egreso registrado', 'success');
        closeModal('#modalEgreso');
        await Promise.all([loadStats(), loadMovimientos()]);
    } catch (e) {
        toast(`No se pudo registrar egreso: ${e.message}`, 'error');
    }
}

// =========================
// Upload Estado de Cuenta
// =========================
function bindUpload() {
    const zone = $('#uploadZone');
    const input = $('#archivoMovimientos');
    const btnPreview = $('#btnVistaPrevia');
    const btnProcesar = $('#btnProcesar');
    const btnLimpiar = $('#btnLimpiar');
    const fileInfo = $('#fileInfo');
    const uploadContent = $('#uploadContent');
    const fileName = $('#fileName');
    const fileSize = $('#fileSize');
    const bancoOrigen = $('#bancoOrigen');

    const enableActions = (enabled) => {
        if (btnPreview) btnPreview.disabled = !enabled;
        if (btnProcesar) btnProcesar.disabled = !enabled || !state.uploadTmp;
    };

    const resetUpload = () => {
        if (input) input.value = '';
        state.uploadTmp = null;
        fileInfo?.classList.add('hidden');
        uploadContent?.classList.remove('hidden');
        enableActions(false);
        hide('vistaPrevia');
        setText('totalMovimientos', '0');
    };

    const onFileSelected = () => {
        const f = input?.files?.[0];
        if (!f) return;
        if (fileName) fileName.textContent = f.name;
        if (fileSize) fileSize.textContent = `${(f.size / (1024 * 1024)).toFixed(2)} MB`;
        fileInfo?.classList.remove('hidden');
        uploadContent?.classList.add('hidden');
        enableActions(true);
    };

    if (zone && input) {
        zone.addEventListener('click', () => input.click());
        zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('dragover'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
        zone.addEventListener('drop', (e) => {
            e.preventDefault(); zone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                onFileSelected();
            }
        });
        input.addEventListener('change', onFileSelected);
    }

    btnPreview?.addEventListener('click', async () => {
        const f = input?.files?.[0];
        if (!f) return toast('Selecciona un archivo primero.', 'error');

        const fd = new FormData();
        fd.append('archivo', f);
        if (bancoOrigen?.value) fd.append('banco_id', bancoOrigen.value);

        try {
            const r = await fetchJSON(`${API_BASE}/movs/upload`, { method: 'POST', body: fd, isForm: true });
            if (!r?.path) throw new Error('Respuesta inválida del servidor');
            state.uploadTmp = r;
            renderPreview(r.rows || []);
            show('vistaPrevia');
            enableActions(true);
            setText('totalMovimientos', String((r.rows || []).length));
        } catch (e) {
            toast(`No se pudo previsualizar: ${e.message}`, 'error');
        }
    });

    btnProcesar?.addEventListener('click', async () => {
        if (!state.uploadTmp?.path) return toast('Primero genera la vista previa.', 'error');
        const fi = $('#fechaInicio')?.value || '';
        const ff = $('#fechaFin')?.value || '';

        const body = {
            archivo_path: state.uploadTmp.path,
            banco_id: bancoOrigen?.value ? Number(bancoOrigen.value) : undefined,
            fecha_inicio: fi || undefined,
            fecha_fin: ff || undefined,
        };

        try {
            const r = await fetchJSON(`${API_BASE}/movs/procesar`, { method: 'POST', body });
            if (!r?.ok && !r?.ec_id) throw new Error(r?.msg || 'Respuesta inválida');
            toast('Estado de cuenta registrado para conciliación.', 'success');
            await loadStats();
        } catch (e) {
            toast(`Error al procesar: ${e.message}`, 'error');
        }
    });

    btnLimpiar?.addEventListener('click', resetUpload);
}

// =========================
// Bind botones globales
// =========================
function bindGlobalButtons() {
    $('#btnAbrirEgreso')?.addEventListener('click', openModalEgreso);
    $('#btnGuardarEgreso')?.addEventListener('click', guardarEgreso);
    $('#btnAprobar')?.addEventListener('click', aprobarPago);
    $('#btnRechazar')?.addEventListener('click', rechazarPago);
    $('#btnRefrescar')?.addEventListener('click', async () => {
        await Promise.all([loadStats(), loadPendientes(), loadMovimientos()]);
        toast('Refrescado', 'success');
    });
    bindModalClose();
}

// =========================
// Init
// =========================
document.addEventListener('DOMContentLoaded', async () => {
    // DataTables inits
    initDtPendientes();
    initDtMovs();
    initDtPreview();

    // Upload + botones
    bindGlobalButtons();
    bindUpload();

    // Primeras cargas
    await loadStats();
    await loadPendientes();
    await loadMovimientos();
});
