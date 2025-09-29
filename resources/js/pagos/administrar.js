// resources/js/pagos/administrar.js
import Swal from "sweetalert2";
import DataTable from "vanilla-datatables";
import "vanilla-datatables/src/vanilla-dataTables.css";

/* =========================
 *  Helpers generales
 * ========================= */

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const getHeaders = () => ({
    "Content-Type": "application/json",
    "X-CSRF-TOKEN": csrfToken
});
const API = "/admin/pagos";

const fmtQ = (n) =>
    "Q " +
    Number(n || 0).toLocaleString("es-GT", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

const swalLoadingOpen = (title = "Procesando...") =>
    Swal.fire({
        title,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });

const swalLoadingClose = () => Swal.close();

const debounce = (fn, ms = 350) => {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), ms);
    };
};

const setTxt = (id, txt) => {
    const el = document.getElementById(id);
    if (el) el.textContent = txt;
};

const abrirModal = (id) => document.getElementById(id)?.classList.remove("hidden");
const cerrarModal = (id) => document.getElementById(id)?.classList.add("hidden");

/* =========================
 *  Estado simple
 * ========================= */
let validarState = { ps: null, venta: null, debia: 0, hizo: 0 };
let uploadTmp = null;

/* =========================
 *  DataTables (vanilla-datatables)
 * ========================= */
let dtPendientes = null;
let dtMovimientos = null;
let dtPreview = null;

const labelsES = {
    placeholder: "Buscar...",
    perPage: "{select} registros por página",
    noRows: "No se encontraron registros",
    info: "Mostrando {start} a {end} de {rows} registros",
};

const initTablaPendientes = () => {
    if (dtPendientes) dtPendientes.destroy();
    dtPendientes = new DataTable("#tablaPendientes", {
        searchable: false,
        sortable: true,
        fixedHeight: true,
        perPage: 10,
        perPageSelect: [5, 10, 20, 50],
        labels: labelsES,
        data: {
            headings: [
                "Fecha",
                "Venta",
                "Cliente",
                "Concepto",
                "Debía",
                "Depositado",
                "Diferencia",
                "Comprobante",
                "Acciones",
            ],
            data: [],
        },
    });
};

const initTablaMovimientos = () => {
    if (dtMovimientos) dtMovimientos.destroy();
    dtMovimientos = new DataTable("#tablaMovimientos", {
        searchable: false,
        sortable: true,
        fixedHeight: true,
        perPage: 10,
        perPageSelect: [5, 10, 20, 50],
        labels: labelsES,
        data: {
            headings: ["Fecha", "Tipo", "Referencia", "Método", "Monto", "Estado"],
            data: [],
        },
    });
};

const initTablaPreview = () => {
    if (dtPreview) dtPreview.destroy();
    dtPreview = new DataTable("#tablaPrevia", {
        searchable: false,
        sortable: true,
        fixedHeight: true,
        perPage: 25,
        perPageSelect: [10, 25, 50, 100],
        labels: labelsES,
        data: {
            headings: ["Fecha", "Descripción", "Referencia", "Monto", "Detectado"],
            data: [],
        },
    });
};

/* =========================
 *  Stats / Dashboard
 *  Espera: { codigo, mensaje, detalle, data:{ saldo_total_gtq, saldos, pendientes, ultima_carga } }
 * ========================= */
const CargarStats = async () => {
    try {
        swalLoadingOpen("Cargando estadísticas...");
        const url = `${API}/dashboard-stats`;
        const resp = await fetch(url, { method: "GET" });
        const { codigo, mensaje, data } = await resp.json();
        swalLoadingClose();

        if (codigo === 1) {
            const {
                saldo_total_gtq = 0,
                saldos = [],
                pendientes = 0,
                ultima_carga = null,
            } = data || {};

            setTxt("saldoCajaTotalGTQ", fmtQ(saldo_total_gtq));

            const efectivo = saldos.find((s) =>
                (s.metodo || "").toLowerCase().includes("efectivo")
            );
            setTxt("saldoEfectivoGTQ", fmtQ(efectivo?.caja_saldo_monto_actual || 0));
            setTxt("contadorPendientes", String(pendientes));
            setTxt(
                "ultimaCargaEstado",
                ultima_carga ? new Date(ultima_carga).toLocaleString() : "—"
            );

            // Rellenar selects
            const filtroMetodo = document.getElementById("filtroMetodo");
            const egMetodo = document.getElementById("egMetodo");
            if (filtroMetodo) {
                filtroMetodo.innerHTML =
                    `<option value="">Todos los métodos</option>` +
                    saldos
                        .map((m) => `<option value="${m.metodo_id}">${m.metodo}</option>`)
                        .join("");
            }
            if (egMetodo) {
                egMetodo.innerHTML = saldos
                    .map((m) => `<option value="${m.metodo_id}">${m.metodo}</option>`)
                    .join("");
            }
        } else {
            console.error(mensaje);
        }
    } catch (e) {
        swalLoadingClose();
        console.error("Error stats:", e);
    }
};

/* =========================
 *  Pendientes
 *  Espera: { codigo, mensaje, data:[...] }
 * ========================= */

const renderPendientes = (rows = []) => {

    const isSmall = window.matchMedia('(max-width: 768px)').matches;


    const data = rows.map((r) => {
        const fecha = r.fecha ? new Date(r.fecha).toLocaleDateString() : "—";
        const venta = r.venta_id ? `#${r.venta_id}` : "—";
        const cliente = r.cliente || "—";


        const ventaTotal = Number(r.venta_total || 0);
        const pendienteVenta = Number(r.pendiente_venta || 0);
        const debiaEnvio = Number(r.debia_envio || 0);
        const cuotasSel = Number(r.cuotas_seleccionadas || 0);
        const cuotasTotal = Number(r.cuotas_total_venta || 0);

        const concepto = r.concepto || "—";
        const debia = fmtQ(r.debia);
        const deposito = fmtQ(r.depositado);
        const difNum = Number(r.diferencia || 0);
        const difCls = difNum === 0 ? "text-emerald-600"
            : difNum > 0 ? "text-amber-600"
                : "text-rose-600";
        const dif = `<span class="${difCls}">${fmtQ(difNum)}</span>`;

        const cuotasInfo = (cuotasSel || cuotasTotal)
            ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-indigo-50 text-indigo-700">
           Cuotas: ${cuotasSel || 0}${cuotasTotal ? ` / ${cuotasTotal}` : ""}
         </span>`
            : "";

        const detalleHtml = `
      <div class="space-y-1">
        <div class="font-medium">${concepto}</div>
        <div class="text-[12px] text-gray-500 flex flex-wrap gap-2">
          <span>Venta: <b>${fmtQ(ventaTotal)}</b></span>
          <span>Pend. venta: <b>${fmtQ(pendienteVenta)}</b></span>
          ${debiaEnvio ? `<span>Debía (envío): <b>${fmtQ(debiaEnvio)}</b></span>` : ""}
          ${cuotasInfo}
        </div>
      </div>
    `;

        const comp = r.imagen
            ? `<button class="btn-ver-comp text-blue-600 hover:underline"
           data-img="${r.imagen}"
           data-ref="${r.referencia || ""}"
           data-fecha="${r.fecha || ""}"
           data-monto="${r.depositado || 0}">
           Ver
         </button>`
            : "—";

        const acciones = `
      <div class="flex justify-center">
        <button class="btn-validar bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-3 py-1.5 rounded-lg"
          data-ps="${r.ps_id}" data-venta="${r.venta_id}"
          data-debia="${r.debia}" data-hizo="${r.depositado}">
          Validar
        </button>
      </div>
    `;

        if (isSmall) {
            const cabecera = `
        <div class="text-[12px] text-gray-500">${fecha} • Venta <b>${venta}</b></div>
        <div class="text-[13px] font-medium">${cliente}</div>
      `;
            const totales = `
        <div class="mt-2 text-[12px] text-gray-600 flex flex-wrap gap-x-4 gap-y-1">
          <span>Debía: <b>${debia}</b></span>
          <span>Depósito: <b>${deposito}</b></span>
          <span>Dif.: <b class="${difCls}">${fmtQ(difNum)}</b></span>
          <span>Comprobante: ${comp}</span>
        </div>
      `;
            return [
                cabecera + detalleHtml + totales,
                acciones
            ];
        }

        return [
            fecha,
            venta,
            cliente,
            detalleHtml,
            debia,
            deposito,
            dif,
            comp,
            acciones
        ];
    });

    if (dtPendientes) dtPendientes.destroy();

    const headingsDesktop = [
        "Fecha",
        "Venta",
        "Cliente",
        "Detalle",
        "Debía",
        "Depositado",
        "Diferencia",
        "Comprobante",
        "Acciones",
    ];

    const headingsMobile = [
        "Factura / Cliente / Detalle",
        "Acciones",
    ];

    dtPendientes = new DataTable("#tablaPendientes", {
        searchable: false,
        sortable: true,
        fixedHeight: true,
        perPage: 10,
        perPageSelect: [5, 10, 20, 50],
        labels: labelsES,
        data: {
            headings: isSmall ? headingsMobile : headingsDesktop,
            data,
        },
    });

    const empty = document.getElementById("emptyPendientes");
    if (empty) empty.classList.toggle("hidden", rows.length > 0);
};

const BuscarPendientes = async () => {
    try {
        swalLoadingOpen("Cargando pendientes...");
        const q = document.getElementById("buscarFactura")?.value?.trim() || "";
        const estado = document.getElementById("filtroEstado")?.value || "";
        const url = new URL(`${API}/pendientes`, window.location.origin);
        if (q) url.searchParams.set("q", q);
        if (estado) url.searchParams.set("estado", estado);

        const resp = await fetch(url, { method: "GET" });
        const { codigo, mensaje, data } = await resp.json();
        swalLoadingClose();

        if (codigo === 1) {
            renderPendientes(data || []);
        } else {
            console.error(mensaje);
            renderPendientes([]);
        }
    } catch (e) {
        swalLoadingClose();
        console.error("Error pendientes:", e);
        renderPendientes([]);
    }
};

document.addEventListener("click", (e) => {
    // Ver comprobante
    if (e.target.closest(".btn-ver-comp")) {
        const btn = e.target.closest(".btn-ver-comp");
        const img = decodeURIComponent(btn.dataset.img || "");
        const ref = btn.dataset.ref || "—";
        const fecha = btn.dataset.fecha
            ? new Date(btn.dataset.fecha).toLocaleString()
            : "—";
        const monto = Number(btn.dataset.monto || 0);

        const src = img;
        const imgEl = document.getElementById("imgComprobante");
        const aEl = document.getElementById("btnDescargarComprobante");
        if (imgEl) imgEl.src = src;
        if (aEl) aEl.href = src;

        setTxt("refComprobante", ref);
        setTxt("fechaComprobante", fecha);
        setTxt("montoComprobante", fmtQ(monto));
        abrirModal("modalComprobante");
    }

    // Validar
    if (e.target.closest(".btn-validar")) {
        const btn = e.target.closest(".btn-validar");
        validarState = {
            ps: Number(btn.dataset.ps),
            venta: Number(btn.dataset.venta),
            debia: Number(btn.dataset.debia || 0),
            hizo: Number(btn.dataset.hizo || 0),
        };
        setTxt("mvVenta", `#${validarState.venta}`);
        setTxt("mvDebia", fmtQ(validarState.debia));
        setTxt("mvHizo", fmtQ(validarState.hizo));
        setTxt("mvDif", fmtQ(validarState.hizo - validarState.debia));
        setTxt("mvMetodo", "—");
        abrirModal("modalValidar");
    }

    if (
        e.target.closest("[data-modal-close], [data-close-modal]") ||
        e.target.closest("[data-modal-backdrop]") ||
        (e.target.classList?.contains("bg-black/50") && e.target.closest(".fixed.inset-0.z-50"))
    ) {
        cerrarModal("modalValidar");
        cerrarModal("modalEgreso");
        cerrarModal("modalComprobante");
        cerrarModal("modalDetalleVenta");
    }


});


/* =========================
 *  Aprobar / Rechazar
 *  Espera: { codigo:1, mensaje }
 * ========================= */
const Aprobar = async () => {
    if (!validarState.ps) return;
    try {
        swalLoadingOpen("Aprobando pago...");
        const url = `${API}/aprobar`;
        const body = JSON.stringify({
            ps_id: validarState.ps,
            observaciones: document.getElementById("mvObs")?.value || "",
        });
        const resp = await fetch(url, {
            method: "POST",
            headers: getHeaders(),
            body,
        });
        const { codigo, mensaje } = await resp.json();
        swalLoadingClose();

        if (codigo === 1) {
            await Swal.fire("¡Éxito!", mensaje || "Pago aprobado", "success");
            cerrarModal("modalValidar");
            await CargarStats();
            await BuscarPendientes();
            await CargarMovimientos();
        } else {
            await Swal.fire("Error", mensaje || "No se pudo aprobar", "error");
        }
    } catch (e) {
        swalLoadingClose();
        console.error("Aprobar error:", e);
    }
};

const Rechazar = async () => {
    if (!validarState.ps) return;
    const motivo = document.getElementById("mvObs")?.value || "";
    if (!motivo || motivo.length < 5) {
        return Swal.fire("Atención", "Indica el motivo (mín 5 caracteres).", "info");
    }
    try {
        swalLoadingOpen("Rechazando pago...");
        const resp = await fetch(`${API}/rechazar`, {
            method: "POST",
            headers: getHeaders(),
            body: JSON.stringify({ ps_id: validarState.ps, motivo }),
        });
        const { codigo, mensaje } = await resp.json();
        swalLoadingClose();

        if (codigo === 1) {
            await Swal.fire("¡Éxito!", mensaje || "Pago rechazado", "success");
            cerrarModal("modalValidar");
            await CargarStats();
            await BuscarPendientes();
        } else {
            await Swal.fire("Error", mensaje || "No se pudo rechazar", "error");
        }
    } catch (e) {
        swalLoadingClose();
        console.error("Rechazar error:", e);
    }
};

document.getElementById("btnAprobar")?.addEventListener("click", Aprobar);
document.getElementById("btnRechazar")?.addEventListener("click", Rechazar);

/* =========================
 *  Movimientos
 *  Espera: { codigo:1, data:{ data:[...], total } } o { codigo:1, data:[...] }
 * ========================= */
const rangoMes = () => {
    const val = document.getElementById("filtroMes")?.value || "";
    const pad = (n) => String(n).padStart(2, "0");
    if (val) {
        const [y, m] = val.split("-").map(Number);
        const f = new Date(y, m - 1, 1);
        const l = new Date(y, m, 0);
        return {
            from: `${f.getFullYear()}-${pad(f.getMonth() + 1)}-${pad(f.getDate())}`,
            to: `${l.getFullYear()}-${pad(l.getMonth() + 1)}-${pad(l.getDate())}`,
        };
    }
    const n = new Date();
    const f = new Date(n.getFullYear(), n.getMonth(), 1);
    const l = new Date(n.getFullYear(), n.getMonth() + 1, 0);
    return {
        from: `${f.getFullYear()}-${pad(f.getMonth() + 1)}-${pad(f.getDate())}`,
        to: `${l.getFullYear()}-${pad(l.getMonth() + 1)}-${pad(l.getDate())}`,
    };
};

const renderMovimientos = (rows = []) => {
    const data = rows.map((r) => {
        const fecha = r.cja_fecha ? new Date(r.cja_fecha).toLocaleString() : "—";
        const tipo = r.cja_tipo || "—";
        const ref = r.cja_no_referencia || "—";
        const metodo = r.metodo || "—";
        const esIn = ["VENTA", "DEPOSITO", "AJUSTE_POS"].includes(tipo);
        const monto = `<span class="${esIn ? "text-emerald-600" : "text-rose-600"}">${fmtQ(
            r.cja_monto
        )}</span>`;
        const est = r.cja_situacion || "—";
        return [fecha, tipo, ref, metodo, monto, est];
    });

    if (dtMovimientos) dtMovimientos.destroy();
    dtMovimientos = new DataTable("#tablaMovimientos", {
        searchable: false,
        sortable: true,
        fixedHeight: true,
        perPage: 10,
        perPageSelect: [5, 10, 20, 50],
        labels: labelsES,
        data: {
            headings: ["Fecha", "Tipo", "Referencia", "Método", "Monto", "Estado"],
            data,
        },
    });
};

const CargarMovimientos = async () => {
    try {
        swalLoadingOpen("Cargando movimientos...");
        const metodoId = document.getElementById("filtroMetodo")?.value || "";
        const { from, to } = rangoMes();
        const url = new URL(`${API}/movimientos`, window.location.origin);
        url.searchParams.set("from", from);
        url.searchParams.set("to", to);
        if (metodoId) url.searchParams.set("metodo_id", metodoId);

        const resp = await fetch(url, { method: "GET" });
        const { codigo, mensaje, data } = await resp.json();
        swalLoadingClose();

        if (codigo === 1) {
            const rows = Array.isArray(data?.movimientos) ? data.movimientos
                : Array.isArray(data) ? data
                    : [];
            const total = Number(data?.total ?? 0);
            renderMovimientos(rows);
            setTxt("totalMovimientosMes", fmtQ(total));
        } else {
            console.error(mensaje);
            renderMovimientos([]);
            setTxt("totalMovimientosMes", fmtQ(0));
        }
    } catch (e) {
        swalLoadingClose();
        console.error("Error movs:", e);
        renderMovimientos([]);
        setTxt("totalMovimientosMes", fmtQ(0));
    }
};

document.getElementById("btnFiltrarMovs")?.addEventListener("click", CargarMovimientos);

/* =========================
 *  Egresos
 *  Espera: { codigo:1, mensaje }
 * ========================= */
const AbrirEgreso = () => abrirModal("modalEgreso");
document.getElementById("btnAbrirEgreso")?.addEventListener("click", AbrirEgreso);

document.getElementById("btnGuardarEgreso")?.addEventListener("click", async (e) => {
    e.preventDefault();

    // Validación mínima (método, monto, motivo)
    const egMetodo = document.getElementById("egMetodo")?.value;
    const egMonto = document.getElementById("egMonto")?.value;
    const egMotivo = document.getElementById("egMotivo")?.value;
    if (!egMetodo || !egMonto || !egMotivo) {
        return Swal.fire("Campos vacíos", "Completa método, monto y motivo.", "info");
    }

    try {
        swalLoadingOpen("Guardando egreso...");
        const form = document.getElementById("formEgreso");
        const fd = new FormData(form);
        const resp = await fetch(`${API}/egresos`, { method: "POST", body: fd });
        const { codigo, mensaje } = await resp.json();
        swalLoadingClose();

        if (codigo === 1) {
            await Swal.fire("¡Éxito!", mensaje || "Egreso registrado", "success");
            cerrarModal("modalEgreso");
            form.reset();
            await CargarStats();
            await CargarMovimientos();
        } else {
            await Swal.fire("Error", mensaje || "No se pudo registrar", "error");
        }
    } catch (e) {
        swalLoadingClose();
        console.error("Egreso error:", e);
    }
});

/* =========================
 *  Upload Estado de Cuenta
 *  Preview: { codigo:1, data:{ path, headers, rows } }
 *  Procesar: { codigo:1, mensaje }
 * ========================= */
const zone = document.getElementById("uploadZone");
const inputFile = document.getElementById("archivoMovimientos");
const btnPreview = document.getElementById("btnVistaPrevia");
const btnProcesar = document.getElementById("btnProcesar");
const btnLimpiar = document.getElementById("btnLimpiar");
const fileInfo = document.getElementById("fileInfo");
const uploadContent = document.getElementById("uploadContent");
const fileName = document.getElementById("fileName");
const fileSize = document.getElementById("fileSize");
const bancoOrigen = document.getElementById("bancoOrigen");

const enableUploadActions = (ok) => {
    if (btnPreview) btnPreview.disabled = !ok;
    if (btnProcesar) btnProcesar.disabled = !ok || !uploadTmp;
};

const resetUpload = () => {
    if (inputFile) inputFile.value = "";
    uploadTmp = null;
    fileInfo?.classList.add("hidden");
    uploadContent?.classList.remove("hidden");
    enableUploadActions(false);
    document.getElementById("vistaPrevia")?.classList.add("hidden");
    setTxt("totalMovimientos", "0");
};

const onFileSelected = () => {
    const f = inputFile?.files?.[0];
    if (!f) return;
    if (fileName) fileName.textContent = f.name;
    if (fileSize) fileSize.textContent = `${(f.size / (1024 * 1024)).toFixed(2)} MB`;
    fileInfo?.classList.remove("hidden");
    uploadContent?.classList.add("hidden");
    enableUploadActions(true);
};

zone?.addEventListener("click", () => inputFile?.click());
zone?.addEventListener("dragover", (e) => {
    e.preventDefault();
    zone.classList.add("dragover");
});
zone?.addEventListener("dragleave", () => zone.classList.remove("dragover"));
zone?.addEventListener("drop", (e) => {
    e.preventDefault();
    zone.classList.remove("dragover");
    if (e.dataTransfer.files.length) {
        inputFile.files = e.dataTransfer.files;
        onFileSelected();
    }
});
inputFile?.addEventListener("change", onFileSelected);

btnPreview?.addEventListener("click", async () => {
    const f = inputFile?.files?.[0];
    if (!f) return Swal.fire("Archivo faltante", "Selecciona un archivo.", "info");

    try {
        swalLoadingOpen("Subiendo archivo...");
        const fd = new FormData();
        fd.append("archivo", f);
        if (bancoOrigen?.value) fd.append("banco_id", bancoOrigen.value);

        fd.append("_token", csrfToken);
        const resp = await fetch(`${API}/movs/upload`, { method: "POST", body: fd });
        const { codigo, mensaje, data } = await resp.json();
        swalLoadingClose();

        if (codigo === 1 && data?.path) {
            uploadTmp = data;
            // Render preview
            const rows = (data.rows || []).map((r) => [
                r.fecha || "—",
                r.descripcion || "—",
                r.referencia || "—",
                fmtQ(r.monto ?? 0),
                r.detectado ?? "—",
            ]);

            if (dtPreview) dtPreview.destroy();
            dtPreview = new DataTable("#tablaPrevia", {
                searchable: false,
                sortable: true,
                fixedHeight: true,
                perPage: 25,
                perPageSelect: [10, 25, 50, 100],
                labels: labelsES,
                data: {
                    headings: ["Fecha", "Descripción", "Referencia", "Monto", "Detectado"],
                    data: rows,
                },
            });

            document.getElementById("vistaPrevia")?.classList.remove("hidden");
            setTxt("totalMovimientos", String(rows.length));
            enableUploadActions(true);
        } else {
            await Swal.fire("Error", mensaje || "No se pudo previsualizar", "error");
        }
    } catch (e) {
        swalLoadingClose();
        console.error("Preview error:", e);
    }
});

btnProcesar?.addEventListener("click", async () => {
    if (!uploadTmp?.path) {
        return Swal.fire("Primero la vista previa", "Genera la vista previa.", "info");
    }
    try {
        swalLoadingOpen("Procesando archivo...");
        const fi = document.getElementById("fechaInicio")?.value || "";
        const ff = document.getElementById("fechaFin")?.value || "";
        const body = JSON.stringify({
            archivo_path: uploadTmp.path,
            banco_id: bancoOrigen?.value ? Number(bancoOrigen.value) : undefined,
            fecha_inicio: fi || undefined,
            fecha_fin: ff || undefined,
        });

        const resp = await fetch(`${API}/movs/procesar`, {
            method: "POST",
            headers: getHeaders(),
            body,
        });
        const { codigo, mensaje, data } = await resp.json();
        swalLoadingClose();

        if (codigo === 1) {
            await Swal.fire("¡Éxito!", mensaje || "Estado de cuenta registrado", "success");

            const ecId = data?.ec_id;
            if (ecId) {
                await ConciliarAutomatico(ecId);
            }

            await CargarStats();
        } else {
            await Swal.fire("Error", mensaje || "No se pudo procesar", "error");
        }
    } catch (e) {
        swalLoadingClose();
        console.error("Procesar error:", e);
    }
});

const ConciliarAutomatico = async (ecId) => {
    try {
        swalLoadingOpen("Conciliando pagos...");
        const resp = await fetch(`${API}/conciliar`, {
            method: "POST",
            headers: getHeaders(),
            body: JSON.stringify({ ec_id: ecId })
        });
        const { codigo, mensaje, data } = await resp.json();
        swalLoadingClose();

        if (codigo === 1) {
            const { matches = [], no_match = [] } = data;

            // Mostrar sección de conciliación
            const seccion = document.getElementById('seccionConciliacion');
            const matchesDiv = document.getElementById('matchesList');
            const noMatchDiv = document.getElementById('noMatchList');

            if (seccion) seccion.classList.remove('hidden');

            // Renderizar coincidencias
            if (matches.length > 0) {
                matchesDiv.innerHTML = `
                    <h4 class="font-semibold text-emerald-700 mb-2">✓ Coincidencias (${matches.length})</h4>
                    ${matches.map(m => `
                        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <p class="font-semibold text-sm">Venta #${m.venta_id}</p>
                                    <p class="text-xs text-gray-600">Ref: ${m.banco_ref}</p>
                                    <p class="text-xs text-gray-600">Fecha banco: ${m.banco_fecha || '—'}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-emerald-700">${fmtQ(m.banco_monto)}</p>
                                    <span class="text-xs px-2 py-1 bg-emerald-600 text-white rounded">${m.confianza}</span>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                `;
            } else {
                matchesDiv.innerHTML = '<p class="text-sm text-gray-500">No se encontraron coincidencias automáticas.</p>';
            }

            // Renderizar sin coincidencia
            if (no_match.length > 0) {
                noMatchDiv.innerHTML = `
                    <h4 class="font-semibold text-amber-700 mb-2 mt-4">⚠ Sin coincidencia (${no_match.length})</h4>
                    ${no_match.map(n => `
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <p class="font-semibold text-sm">Venta #${n.venta_id}</p>
                                    <p class="text-xs text-gray-600">Ref cliente: ${n.ps_referencia || 'Sin ref'}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-amber-700">${fmtQ(n.ps_monto)}</p>
                                    <span class="text-xs px-2 py-1 bg-amber-600 text-white rounded">MANUAL</span>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                `;
            }

            await BuscarPendientes();

            seccion.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    } catch (e) {
        swalLoadingClose();
        console.error("Conciliar error:", e);
    }
};

btnLimpiar?.addEventListener("click", resetUpload);

/* =========================
 *  Filtros y refresco
 * ========================= */
document.getElementById("buscarFactura")?.addEventListener("input", debounce(BuscarPendientes, 350));
document.getElementById("filtroEstado")?.addEventListener("change", BuscarPendientes);

document.getElementById("btnRefrescar")?.addEventListener("click", async () => {
    await CargarStats();
    await BuscarPendientes();
    await CargarMovimientos();
    await Swal.fire({
        title: "Actualizado",
        text: "Datos refrescados",
        icon: "success",
        timer: 1200,
        showConfirmButton: false,
    });
});

document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
        ["modalValidar", "modalEgreso", "modalComprobante", "modalDetalleVenta"].forEach(cerrarModal);
    }
});

/* =========================
 *  Init
 * ========================= */
const Init = async () => {
    initTablaPendientes();
    initTablaMovimientos();
    initTablaPreview();
    await CargarStats();
    await BuscarPendientes();
    await CargarMovimientos();
};
document.addEventListener("DOMContentLoaded", Init);
