// resources/js/usuarios/mapa.js
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import markerIcon2x from "leaflet/dist/images/marker-icon-2x.png";
import markerIcon from "leaflet/dist/images/marker-icon.png";
import markerShadow from "leaflet/dist/images/marker-shadow.png";
import Swal from "sweetalert2";
import { validarFormulario } from "../app";
import vanillaDataTables from "vanilla-datatables";

/* =========================
   LEAFLET (fix Vite)
========================= */
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

/* =========================
   CACHÉ
========================= */
const el = {
  form: document.getElementById("FormRegistroUbicaciones"),
  visitado: document.getElementById("visitado"),
  ventaInfo: document.getElementById("venta_info"),
  grupoFecha: document.getElementById("grupo_fecha_visita"),
  fechaVisita: document.getElementById("fecha_visita"),
  lat: document.getElementById("lat"),
  lng: document.getElementById("lng"),
  btnTomarPos: document.getElementById("btn_tomar_posicion"),
  btnGuardar: document.getElementById("btn_guardar"),
  btnMiUbic: document.getElementById("btn_mi_ubicacion"),
  btnLimpiar: document.getElementById("btn_limpiar_mapa"),
  btnPlotear: document.getElementById("btn_plotear"),
  btnBuscarLugar: document.getElementById("btn_buscar_lugar"),
  inputBusqueda: document.getElementById("busqueda_lugar"),

  map: document.getElementById("map"),
  mapCard: document.getElementById("map_card"),
  btnFullscreen: document.getElementById("btn_fullscreen"),
  iconExpand: document.getElementById("icon_expand"),
  iconCompress: document.getElementById("icon_compress"),
  status: document.getElementById("status_mapa"),
  zoomLevel: document.getElementById("zoom_level"),

  filtroCliente: document.getElementById("filtro_cliente"),
  btnExportar: document.getElementById("btn_exportar"),
  tabla: document.querySelector("table.min-w-full"),
  cuerpoTabla: document.getElementById("tabla_clientes"),
  noData: document.getElementById("no-data-message"),

  // Opcional (si tienes un botón extra para limpiar el formulario)
  btnLimpiarForm: document.getElementById("btn_limpiar"),

  // Texto del botón principal Guardar/Modificar
  btnGuardarTexto: document.getElementById("btn_guardar_texto"),
};

/* =========================
   ESTADO
========================= */
const DEFAULT_CENTER = [14.6349, -90.5069];
const DEFAULT_ZOOM = 13;

let map = null;
let markerEdicion = null;         // marcador draggable para edición puntual
let clientesLayer = null;         // capa con todos los clientes
let clickEnabled = true;
let dt = null;
let datos = [];                   // cache tabla

/* =========================
   UTILES
========================= */
const toastLoading = (title = "Procesando…", text = "Por favor espere") => {
  Swal.fire({ title, text, icon: "info", allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
};
const toastClose = () => Swal.close();
const setStatus = (msg) => { if (el.status) el.status.textContent = msg; };

const setEditMode = (on = false) => {
  // Cambia texto/color del botón principal
  if (el.btnGuardarTexto) el.btnGuardarTexto.textContent = on ? "Modificar" : "Guardar";
  if (el.btnGuardar) {
    el.btnGuardar.classList.toggle("bg-emerald-600", !on);
    el.btnGuardar.classList.toggle("bg-indigo-600", on);
    el.btnGuardar.classList.toggle("hover:bg-emerald-500", !on);
    el.btnGuardar.classList.toggle("hover:bg-indigo-500", on);
  }
  // Marcar el formulario con un flag
  if (el.form) el.form.dataset.editing = on ? "1" : "0";
};

/* =========================
   MAPA
========================= */
const initMapa = () => {
  if (!el.map) return;

  const satelite = L.tileLayer(
    "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
    { attribution: "Tiles © Esri", maxZoom: 19 }
  );
  const etiquetas = L.tileLayer(
    "https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}",
    { attribution: "Labels © Esri", maxZoom: 19 }
  );

  map = L.map(el.map, {
    center: DEFAULT_CENTER,
    zoom: DEFAULT_ZOOM,
    layers: [satelite, etiquetas],
    zoomControl: true,
  });

  clientesLayer = L.layerGroup().addTo(map);

  map.on("click", (e) => {
    if (!clickEnabled) return;
    const { lat, lng } = e.latlng;
    setMarkerEdicion(lat, lng, "Ubicación seleccionada");
    el.lat.value = lat;
    el.lng.value = lng;
    setStatus(`Marcado: ${lat.toFixed(6)}, ${lng.toFixed(6)}`);
  });

  map.on("zoomend", () => {
    if (el.zoomLevel) el.zoomLevel.textContent = `Zoom: ${map.getZoom()}`;
  });
  if (el.zoomLevel) el.zoomLevel.textContent = `Zoom: ${map.getZoom()}`;

  // marcador inicial de referencia (editable)
  setMarkerEdicion(DEFAULT_CENTER[0], DEFAULT_CENTER[1], "Ciudad de Guatemala");
  setStatus("Haz clic en el mapa o usa los botones para ubicar al cliente.");
};

const setMarkerEdicion = (lat, lng, title = "Ubicación") => {
  if (!map) return;
  if (!markerEdicion) {
    markerEdicion = L.marker([lat, lng], { draggable: true }).addTo(map);
    markerEdicion.on("dragend", (ev) => {
      const { lat, lng } = ev.target.getLatLng();
      el.lat.value = lat;
      el.lng.value = lng;
      markerEdicion.bindPopup(`<b>${title}</b><br>Lat: ${lat}<br>Lng: ${lng}`);
      setStatus(`Marcador movido: ${lat.toFixed(6)}, ${lng.toFixed(6)}`);
    });
  } else {
    markerEdicion.setLatLng([lat, lng]);
  }
  markerEdicion.bindPopup(`<b>${title}</b><br>Lat: ${lat}<br>Lng: ${lng}`).openPopup();
};

const flyTo = (lat, lng, zoom = 16) => map && map.flyTo([lat, lng], zoom, { duration: 0.5 });

const clearMarkerEdicion = () => {
  if (markerEdicion) {
    map.removeLayer(markerEdicion);
    markerEdicion = null;
  }
  setStatus("Marcador eliminado.");
};

const miUbicacion = () => {
  if (!navigator.geolocation) { setStatus("Tu navegador no soporta geolocalización."); return; }
  toastLoading("Obteniendo tu ubicación…", "Usando geolocalización del navegador");
  navigator.geolocation.getCurrentPosition(
    ({ coords }) => {
      const { latitude, longitude, accuracy } = coords;
      const precise = accuracy && accuracy < 150;
      const lat = precise ? latitude : DEFAULT_CENTER[0];
      const lng = precise ? longitude : DEFAULT_CENTER[1];
      setMarkerEdicion(lat, lng, "Mi ubicación");
      el.lat.value = lat; el.lng.value = lng;
      flyTo(lat, lng, 16);
      setStatus(precise ? `Ubicación precisa (~${Math.round(accuracy)}m).` : "Ubicación poco precisa; usando centro por defecto.");
      toastClose();
    },
    () => {
      setMarkerEdicion(DEFAULT_CENTER[0], DEFAULT_CENTER[1], "Ubicación predeterminada");
      el.lat.value = DEFAULT_CENTER[0]; el.lng.value = DEFAULT_CENTER[1];
      flyTo(DEFAULT_CENTER[0], DEFAULT_CENTER[1], DEFAULT_ZOOM);
      setStatus("No se pudo obtener la ubicación. Usando ubicación predeterminada.");
      toastClose();
    },
    { enableHighAccuracy: true, timeout: 8000 }
  );
};

const buscarLugar = async () => {
  const q = (el.inputBusqueda?.value || "").trim();
  if (!q) { setStatus("Ingresa un lugar para buscar."); return; }
  toastLoading("Buscando lugar…", "Consultando geocodificador");
  try {
    const url = `https://nominatim.openstreetmap.org/search?format=geojson&limit=5&accept-language=es&q=${encodeURIComponent(q)}`;
    const res = await fetch(url, { headers: { "Accept-Language": "es" } });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    if (!data.features?.length) { setStatus("Sin resultados."); return; }
    const f = data.features[0];
    const [lng, lat] = f.geometry.coordinates;
    const nombre = (f.properties?.display_name || "Ubicación");
    setMarkerEdicion(lat, lng, nombre);
    el.lat.value = lat; el.lng.value = lng;
    flyTo(lat, lng, 17);
    setStatus(`Resultado: ${nombre}`);
  } catch (e) {
    console.error(e);
    setStatus("Error al buscar el lugar.");
  } finally {
    toastClose();
  }
};

const plotearDesdeInputs = () => {
  const lat = parseFloat(el.lat?.value || "");
  const lng = parseFloat(el.lng?.value || "");
  if (Number.isFinite(lat) && Number.isFinite(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
    setMarkerEdicion(lat, lng, "Ubicación ingresada");
    flyTo(lat, lng, 17);
    setStatus("Ploteado desde los campos.");
  } else {
    setStatus("Coordenadas inválidas. Revisa latitud/longitud.");
  }
};

const toggleClickMode = () => {
  clickEnabled = !clickEnabled;
  setStatus(clickEnabled ? "Modo selección activado: haz clic en el mapa para marcar." : "Modo selección desactivado.");
};

/* =========================
   FULLSCREEN
========================= */
const enterFullscreen = async () => { if (!document.fullscreenElement) await el.mapCard?.requestFullscreen(); };
const exitFullscreen = async () => { if (document.fullscreenElement) await document.exitFullscreen(); };
const toggleFullscreen = async () => { try { document.fullscreenElement ? await exitFullscreen() : await enterFullscreen(); } catch { } };

const onFullscreenChange = () => {
  const inFS = !!document.fullscreenElement;
  el.mapCard?.classList.toggle("in-fullscreen", inFS);
  el.iconExpand?.classList.toggle("hidden", inFS);
  el.iconCompress?.classList.toggle("hidden", !inFS);
  if (map) { map.invalidateSize(); setTimeout(() => map.invalidateSize(), 150); }
  setStatus(inFS ? "Mapa en pantalla completa." : "Mapa en modo normal.");
  document.body.style.overflow = inFS ? "hidden" : "";

  _mountModalForFullscreen(inFS);
};

/* =========================
   FORM VISITA/VENTA
========================= */
const toggleFechaVisita = () => {
  const v = el.visitado?.value || "";
  const mostrar = v === "1" || v === "2";
  el.grupoFecha?.classList.toggle("hidden", !mostrar);
  if (el.fechaVisita) el.fechaVisita.disabled = !mostrar;
};
const toggleVentaInfo = () => {
  if (!el.ventaInfo || !el.visitado) return;
  el.ventaInfo.classList.toggle("hidden", el.visitado.value !== "2");
};

/* =========================
   API: LISTAR / GUARDAR / ELIMINAR
========================= */
const obtenerDatos = async () => buscarAPI();

const buscarAPI = async () => {
  toastLoading("Buscando Registros…");
  try {
    const resp = await fetch("/api/ubicaciones", { headers: { Accept: "application/json" } });
    const json = await resp.json();
    if (json?.codigo === 1) {
      datos = Array.isArray(json.data) ? json.data : [];
      pintarTabla(datos);
      pintarClientesEnMapa(datos);  // <== pinta todos los clientes
    } else {
      await Swal.fire("Error", json?.mensaje || "No se pudo obtener la información", "error");
      datos = [];
      pintarTabla([]);
      pintarClientesEnMapa([]);
    }
  } catch (err) {
    console.error(err);
    await Swal.fire("Error", "Error de conexión", "error");
    datos = [];
    pintarTabla([]);
    pintarClientesEnMapa([]);
  } finally {
    toastClose();
  }
};

const guardarRegistro = async (e) => {
  e.preventDefault();
  if (!el.form || !el.visitado) return;

  const excepciones = ["ubi_id", "busqueda_lugar"];
  const s = (el.visitado.value || "").trim();
  if (s === "1") excepciones.push("cantidad_vendida", "descripcion_venta");
  if (s === "3") excepciones.push("cantidad_vendida", "descripcion_venta", "fecha_visita");

  if (!validarFormulario(el.form, excepciones)) {
    Swal.fire({ title: "Campos vacíos", text: "Debe llenar todos los campos", icon: "info" });
    return;
  }

  const fd = new FormData(el.form);
  const id = (fd.get("ubi_id") || "").toString().trim();

  // *** CONVERTIR FormData a objeto normal ***
  const formDataObj = {};
  for (let [key, value] of fd.entries()) {
    formDataObj[key] = value;
  }

  console.log("=== DEBUGGING FORM DATA ===");
  console.log("ID encontrado:", id);
  console.log("Datos a enviar:", formDataObj);

  let url = "/api/ubicaciones";
  let method = "POST";
  if (id) {
    url = `/api/ubicaciones/${id}`;
    method = "PUT";
  }

  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

  try {
    toastLoading(id ? "Actualizando información…" : "Guardando información…");

    const resp = await fetch(url, {
      method,
      headers: {
        "X-CSRF-TOKEN": token,
        "Accept": "application/json",
        "Content-Type": "application/json"
      },
      body: JSON.stringify(formDataObj)
    });

    const data = await resp.json().catch(() => ({}));
    console.log("Respuesta del servidor:", data);

    const { codigo, mensaje, detalle } = data || {};

    if (codigo === 1) {
      await Swal.fire("Éxito", mensaje || "Operación realizada correctamente", "success");
      el.form.reset();
      toggleVentaInfo(); toggleFechaVisita();
      setEditMode(false);
      await obtenerDatos();
    } else {
      const msg = detalle ? `${mensaje || "Error"}` : (mensaje || "Error al procesar la información");
      await Swal.fire("Error", msg, "error");
    }
  } catch (error) {
    console.error("Error completo:", error);
    Swal.fire("Error", "Error de conexión", "error");
  } finally {
    toastClose();
  }
};
const eliminarRegistro = async (ubiId) => {
  if (!ubiId) return;
  const ok = await Swal.fire({
    title: "¿Eliminar cliente?",
    text: "Esta acción no se puede deshacer.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#dc2626",
  });
  if (!ok.isConfirmed) return;

  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

  try {
    toastLoading("Eliminando…");
    const resp = await fetch(`/api/ubicaciones/${ubiId}`, {
      method: "DELETE",
      headers: { "X-CSRF-TOKEN": token, Accept: "application/json" },
    });
    const data = await resp.json().catch(() => ({}));
    if (data?.codigo === 1) {
      await Swal.fire("Listo", data?.mensaje || "Registro eliminado", "success");
      await obtenerDatos(); // repinta tabla y mapa sin el cliente
    } else {
      await Swal.fire("Error", data?.mensaje || "No se pudo eliminar", "error");
    }
  } catch (e) {
    console.error(e);
    Swal.fire("Error", "Error de conexión", "error");
  } finally {
    toastClose();
  }
};

/* =========================
   MODAL: helpers + refs + abrir
========================= */
const _fmtMoney = (n) => new Intl.NumberFormat('es-GT', { style: 'currency', currency: 'GTQ' }).format(Number(n || 0));
const _estadoTxt = (e) => e == 2 ? '🟢 Compró' : e == 1 ? '🟡 No compró' : '🔴 No visitado';

const modal = {
  wrap: document.getElementById('cliente_modal'),
  close: document.getElementById('cm_close'),
  nombre: document.getElementById('cm_nombre'),
  sub: document.getElementById('cm_sub'),
  loading: document.getElementById('cm_loading'),
  vPane: document.getElementById('cm_visitas_pane'),
  hPane: document.getElementById('cm_hist_pane'),
  vTb: document.getElementById('cm_visitas_tb'),
  hTb: document.getElementById('cm_hist_tb'),
  stat: {
    visitas: document.getElementById('cm_visitas'),
    compras: document.getElementById('cm_compras'),
    nocompra: document.getElementById('cm_nocompra'),
    novisitado: document.getElementById('cm_novisitado'),
    total: document.getElementById('cm_total'),
  },
  tabV: document.getElementById('tab_visitas'),
  tabH: document.getElementById('tab_hist'),
  addVis: document.getElementById('cm_add_visita'),
  btnVerModal: document.getElementById('btn_ver_modal'),
};

modal._originalParent = modal.wrap?.parentElement || null;

const _mountModalForFullscreen = (inFS) => {
  if (!modal.wrap) return;
  if (inFS && document.fullscreenElement === el.mapCard) {
    if (modal.wrap.parentElement !== el.mapCard) {
      el.mapCard.appendChild(modal.wrap);
    }
  } else {
    if (modal._originalParent && modal.wrap.parentElement !== modal._originalParent) {
      modal._originalParent.appendChild(modal.wrap);
    }
  }
};

const abrirModalCliente = async (userId) => {
  if (!userId || !modal.wrap) return;

  _mountModalForFullscreen(!!document.fullscreenElement);
  modal.wrap.classList.remove('hidden');
  modal.loading.classList.remove('hidden');
  modal.vPane.classList.add('hidden');
  modal.hPane.classList.add('hidden');

  modal.vTb.innerHTML = '';
  modal.hTb.innerHTML = '';

  modal.stat.visitas.textContent = '—';
  modal.stat.compras.textContent = '—';
  modal.stat.nocompra.textContent = '—';
  modal.stat.novisitado.textContent = '—';
  modal.stat.total.textContent = '—';

  modal.nombre.textContent = 'Cargando…';
  modal.sub.textContent = '';

  try {
    const resp = await fetch(`/api/ubicaciones/${userId}/detalle`, { headers: { Accept: 'application/json' } });
    const j = await resp.json();
    if (j?.codigo !== 1) throw new Error(j?.mensaje || 'Error');

    const d = j.data;
    modal.nombre.textContent = d.name || `Cliente #${userId}`;
    modal.sub.textContent = `Última visita: ${d.ult_visita_fecha ?? '—'}`;

    modal.stat.visitas.textContent = d.total_visitas ?? 0;
    modal.stat.compras.textContent = d.total_compras ?? 0;
    modal.stat.nocompra.textContent = d.total_no_compra ?? 0;
    modal.stat.novisitado.textContent = d.total_no_visitado ?? 0;
    modal.stat.total.textContent = _fmtMoney(d.total_venta);

    modal.vTb.innerHTML = (d.visitas || []).map(v => `
      <tr>
        <td class="px-4 py-2">${v.visita_fecha ?? '—'}</td>
        <td class="px-4 py-2">${_estadoTxt(Number(v.visita_estado))}</td>
        <td class="px-4 py-2">${_fmtMoney(v.visita_venta)}</td>
        <td class="px-4 py-2">${v.visita_descripcion ?? '—'}</td>
      </tr>
    `).join('');

    modal.hTb.innerHTML = (d.historial || []).map(h => `
      <tr>
        <td class="px-4 py-2">${h.hist_fecha_actualizacion}</td>
        <td class="px-4 py-2">${_estadoTxt(h.hist_estado_anterior)} → ${_estadoTxt(h.hist_estado_nuevo)}</td>
        <td class="px-4 py-2">${_fmtMoney(h.hist_total_venta_anterior)} → ${_fmtMoney(h.hist_total_venta_nuevo)}</td>
        <td class="px-4 py-2">${h.hist_descripcion ?? '—'}</td>
      </tr>
    `).join('');

    modal.loading.classList.add('hidden');
    modal.vPane.classList.remove('hidden');
  } catch (e) {
    console.error(e);
    modal.loading.textContent = 'Error al cargar.';
  }
};

/* =========================
   MAPA: PINES VISIBLES (no puntito)
========================= */
const _pinIcon = (estado = 3, label = "") => {
  const cfg = {
    2: { bg: "#10b981", ring: "rgba(16,185,129,.35)" }, // verde (compró)
    1: { bg: "#f59e0b", ring: "rgba(245,158,11,.35)" }, // ámbar (visitado/no compró)
    3: { bg: "#ef4444", ring: "rgba(239,68,68,.35)" },  // rojo (no visitado)
  }[Number(estado) || 3];

  const safeLabel = (label || "").toString().slice(0, 2).toUpperCase();

  const html = `
    <div style="position:relative; width:34px; height:48px; transform:translateZ(0);">
      <div style="position:absolute; left:50%; bottom:-4px; width:26px; height:10px; transform:translateX(-50%);
                  background: radial-gradient(closest-side, rgba(2,6,23,.25), rgba(2,6,23,0));
                  border-radius:9999px;"></div>

      <div style="
        position:absolute; left:50%; top:0; transform:translateX(-50%);
        width:28px; height:28px; border-radius:9999px;
        background:${cfg.bg};
        box-shadow:0 8px 22px rgba(2,6,23,.35);
        display:grid; place-items:center; color:#fff; font-weight:800; font-size:12px;
      ">
        ${safeLabel ? safeLabel : "•"}
      </div>

      <div style="
        position:absolute; left:50%; top:22px; transform:translateX(-50%) rotate(45deg);
        width:14px; height:14px; background:${cfg.bg};
        box-shadow:3px 3px 12px rgba(2,6,23,.25);
      "></div>

      <div style="
        position:absolute; left:50%; top:-6px; transform:translateX(-50%);
        width:38px; height:38px; border-radius:9999px; box-shadow:0 0 0 8px ${cfg.ring};
      "></div>
    </div>
  `;

  return L.divIcon({
    html,
    className: "pin-marker",
    iconSize: [34, 48],
    iconAnchor: [17, 46],      // punta del pin
    popupAnchor: [0, -38],
  });
};

const etiquetaEstadoHTML = (estado) => {
  const e = Number(estado);
  if (e === 1) return '<span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200">Visitado/No comprado</span>';
  if (e === 2) return '<span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">Visitado/Comprado</span>';
  return '<span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-200">No visitado</span>';
};
const coordsFmt = (lat, lng) => (!lat || !lng) ? "—" : `${Number(lat).toFixed(6)}, ${Number(lng).toFixed(6)}`;

const pintarClientesEnMapa = (lista) => {
  if (!clientesLayer) return;
  clientesLayer.clearLayers();

  const bounds = [];
  (lista || []).forEach((r) => {
    const lat = parseFloat(r.ubi_latitud);
    const lng = parseFloat(r.ubi_longitud);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

    const icon = _pinIcon(r.visita_estado, r.name?.[0]);
    const m = L.marker([lat, lng], { icon });

    // Click directo en el pin => abrir modal del cliente
    m.on("click", () => abrirModalCliente(r.user_id));
    // (Opcional) click derecho para abrir popup con acciones
    m.on("contextmenu", () => m.openPopup());

    const popupHtml = `
      <div class="text-sm">
        <button data-action="ver" data-user="${r.user_id}"
                class="font-semibold text-indigo-600 hover:underline">${r.name ?? "—"}</button>
        <div class="mt-1">${Number(r.visita_estado) === 2 ? '🟢 Visitado/Comprado' : Number(r.visita_estado) === 1 ? '🟡 Visitado/No comprado' : '🔴 No visitado'}</div>
        <div class="mt-1 text-slate-600">${r.ubi_descripcion ?? "—"}</div>
        <div class="mt-2 flex gap-2">
          <button data-action="modificar"
                  data-ubi="${r.ubi_id ?? ''}"
                  data-user="${r.user_id}"
                  data-dir="${r.ubi_descripcion ?? ''}"
                  data-lat="${r.ubi_latitud ?? ''}"
                  data-lng="${r.ubi_longitud ?? ''}"
                  data-estado="${r.visita_estado ?? ''}"
                  data-fecha="${r.visita_fecha ?? ''}"
                  data-venta="${r.visita_venta ?? ''}"
                  data-desc="${r.visita_descripcion ?? ''}"
                  class="px-2 py-1 rounded bg-indigo-600 text-white text-xs">Modificar</button>
          <button data-action="eliminar" data-ubi="${r.ubi_id ?? ""}"
                  class="px-2 py-1 rounded bg-rose-600 text-white text-xs">Eliminar</button>
        </div>
      </div>
    `;

    m.bindPopup(popupHtml);
    clientesLayer.addLayer(m);
    bounds.push([lat, lng]);
  });

  if (bounds.length) {
    const b = L.latLngBounds(bounds);
    map.fitBounds(b.pad(0.15));
  }
};

/* =========================
   TABLA
========================= */
const filaHTML = (r) => `
  <tr>
    <td class="px-6 py-4">${r.name ?? "—"}</td>
    <td class="px-6 py-4 hidden md:table-cell">${r.user_empresa ?? "Sin Empresa"}</td>
    <td class="px-6 py-4">${etiquetaEstadoHTML(r.visita_estado ?? 3)}</td>
    <td class="px-6 py-4 hidden lg:table-cell">${r.ubi_descripcion ?? "—"}</td>
    <td class="px-6 py-4 hidden xl:table-cell">${coordsFmt(r.ubi_latitud, r.ubi_longitud)}</td>
    <td class="px-6 py-4">
      <div class="flex items-center gap-2">
        <button type="button"
          class="px-2 py-1 rounded-md bg-indigo-600 text-white text-xs hover:bg-indigo-500"
          data-action="ver" data-user="${r.user_id}">Ver</button>

        <button type="button"
          class="px-2 py-1 rounded-md bg-emerald-600 text-white text-xs hover:bg-emerald-500"
          data-action="modificar"
          data-ubi="${r.ubi_id ?? ""}"
          data-user="${r.user_id}"
          data-dir="${r.ubi_descripcion ?? ""}"
          data-lat="${r.ubi_latitud ?? ""}"
          data-lng="${r.ubi_longitud ?? ""}"
          data-estado="${r.visita_estado ?? ""}"
          data-fecha="${r.visita_fecha ?? ""}"
          data-venta="${r.visita_venta ?? ""}"
          data-desc="${r.visita_descripcion ?? ""}"
        >Modificar</button>

        <button type="button"
          class="px-2 py-1 rounded-md bg-rose-600 text-white text-xs hover:bg-rose-500"
          data-action="eliminar" data-ubi="${r.ubi_id ?? ""}">Eliminar</button>
      </div>
    </td>
  </tr>
`;

const pintarTabla = (filas) => {
  if (!el.tabla || !el.cuerpoTabla) return;

  if (!filas.length) {
    el.cuerpoTabla.innerHTML = "";
    el.noData?.classList.remove("hidden");
  } else {
    el.noData?.classList.add("hidden");
    el.cuerpoTabla.innerHTML = filas.map(filaHTML).join("");
  }

  if (dt) { dt.destroy(); dt = null; }

  dt = new vanillaDataTables(el.tabla, {
    perPage: 10,
    perPageSelect: [10, 20, 50],
    searchable: false, // usaremos input externo
    sortable: true,
    nextPrev: true,
    labels: {
      placeholder: "Buscar…",
      perPage: "{select} por página",
      noRows: "No hay registros",
      info: "Mostrando {start}–{end} de {rows}",
    },
  });

  // Padding y look Tailwind
  const wrapper = el.tabla.closest(".dataTable-wrapper");
  if (wrapper) {
    wrapper.classList.add("p-4", "sm:p-6", "space-y-3");
    wrapper.querySelector(".dataTable-top")?.classList.add("pb-2");
    wrapper.querySelector(".dataTable-bottom")?.classList.add("pt-2");
    wrapper.querySelector(".dataTable-pagination")?.classList.add("flex", "items-center", "gap-2");
  }

  // Buscador externo
  if (el.filtroCliente) {
    el.filtroCliente.oninput = (e) => dt.search((e.target.value || "").trim());
  }
};

/* =========================
   FORM: CARGAR PARA EDITAR
========================= */
const cargarEnFormulario = (d) => {
  const f = el.form;
  if (!f) return;

  f.querySelector("#ubi_id").value = d.ubi || "";
  f.querySelector("#cliente_id").value = d.user || "";
  f.querySelector("#direccion").value = d.dir || "";
  f.querySelector("#visitado").value = d.estado || "";

  const fecha = d.fecha ? String(d.fecha).slice(0, 10) : "";
  f.querySelector("#fecha_visita").value = fecha;

  f.querySelector("#lat").value = d.lat || "";
  f.querySelector("#lng").value = d.lng || "";
  f.querySelector("#cantidad_vendida").value = d.venta || "";
  f.querySelector("#descripcion_venta").value = d.desc || "";

  f.querySelector("#visitado").dispatchEvent(new Event("change", { bubbles: true }));

  const lat = parseFloat(d.lat), lng = parseFloat(d.lng);
  if (!isNaN(lat) && !isNaN(lng)) { setMarkerEdicion(lat, lng, "Ubicación del cliente"); flyTo(lat, lng, 17); }

  setEditMode(true);
  f.scrollIntoView({ behavior: "smooth", block: "start" });
};

/* =========================
   CSV
========================= */
const exportarCSV = () => {
  if (!datos.length) return Swal.fire("Info", "No hay datos para exportar", "info");
  const header = ["Cliente", "Empresa", "Estado", "Dirección", "Latitud", "Longitud"];
  const estadoTxt = (e) => e == 1 ? "Visitado/No comprado" : e == 2 ? "Visitado/Comprado" : e == 3 ? "No visitado" : "—";
  const lineas = datos.map((r) => [
    r.name ?? "",
    r.user_empresa ?? "Sin Empresa",
    estadoTxt(Number(r.visita_estado ?? 3)),
    r.ubi_descripcion ?? "",
    r.ubi_latitud ?? "",
    r.ubi_longitud ?? "",
  ]);
  const csv = [header.join(","), ...lineas.map((a) => a.map((v) => `"${String(v).replaceAll('"', '""')}"`).join(","))].join("\n");
  const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url; a.download = "clientes_georreferenciados.csv";
  document.body.appendChild(a); a.click(); document.body.removeChild(a);
  URL.revokeObjectURL(url);
};

/* =========================
   DELEGADO GLOBAL (tabla + popups)
========================= */
const onClickGlobal = (e) => {
  const btn = e.target.closest("button[data-action]");
  if (!btn) return;

  const action = btn.getAttribute("data-action");

  if (action === "modificar") {
    const d = {
      ubi: btn.dataset.ubi,
      user: btn.dataset.user,
      dir: btn.dataset.dir,
      lat: btn.dataset.lat,
      lng: btn.dataset.lng,
      estado: btn.dataset.estado,
      fecha: btn.dataset.fecha,
      venta: btn.dataset.venta,
      desc: btn.dataset.desc,
    };
    cargarEnFormulario(d);
    return;
  }

  if (action === "eliminar") {
    const id = btn.dataset.ubi;
    eliminarRegistro(id);
    return;
  }

  if (action === "ver") {
    const userId = btn.dataset.user;
    abrirModalCliente(userId);
    return;
  }
};

/* =========================
   LISTENERS (al final)
========================= */
document.addEventListener("DOMContentLoaded", () => {
  // Mapa
  initMapa();
  setEditMode(false);

  // Cargar datos (tabla + pines)
  obtenerDatos();

  // Acciones mapa
  el.btnMiUbic?.addEventListener("click", miUbicacion);
  el.btnLimpiar?.addEventListener("click", clearMarkerEdicion);
  el.btnPlotear?.addEventListener("click", plotearDesdeInputs);
  el.btnTomarPos?.addEventListener("click", toggleClickMode);

  // Buscar lugar + Enter
  el.btnBuscarLugar?.addEventListener("click", buscarLugar);
  el.inputBusqueda?.addEventListener("keydown", (e) => {
    if (e.key === "Enter") { e.preventDefault(); buscarLugar(); }
  });

  // (Opcional) botón para limpiar formulario si existe
  el.btnLimpiarForm?.addEventListener("click", () => {
    el.form?.reset();
    toggleVentaInfo(); toggleFechaVisita();
    setEditMode(false);
    // clearMarkerEdicion(); // si quieres, descomenta para quitar el pin de edición
  });

  // Fullscreen
  el.btnFullscreen?.addEventListener("click", toggleFullscreen);
  document.addEventListener("fullscreenchange", onFullscreenChange);
  document.addEventListener("webkitfullscreenchange", onFullscreenChange);
  document.addEventListener("mozfullscreenchange", onFullscreenChange);
  document.addEventListener("MSFullscreenChange", onFullscreenChange);

  // Formulario
  el.visitado?.addEventListener("change", () => { toggleVentaInfo(); toggleFechaVisita(); });
  toggleFechaVisita();
  el.form?.addEventListener("submit", guardarRegistro);

  // Delegado global para botones (tabla y popups de marcadores)
  document.addEventListener("click", onClickGlobal);

  // Export
  el.btnExportar?.addEventListener("click", exportarCSV);

  // Modal: listeners
  modal.btnVerModal?.addEventListener('click', () => {
    const first = (Array.isArray(datos) && datos[0]) ? datos[0].user_id : null;
    if (first) abrirModalCliente(first);
  });
  modal.close?.addEventListener('click', () => modal.wrap.classList.add('hidden'));
  modal.wrap?.addEventListener('click', (e) => { if (e.target === modal.wrap) modal.wrap.classList.add('hidden'); });
  modal.tabV?.addEventListener('click', () => { modal.vPane.classList.remove('hidden'); modal.hPane.classList.add('hidden'); });
  modal.tabH?.addEventListener('click', () => { modal.hPane.classList.remove('hidden'); modal.vPane.classList.add('hidden'); });
  modal.addVis?.addEventListener('click', () => {
    document.getElementById('FormRegistroUbicaciones')?.scrollIntoView({ behavior: 'smooth' });
    modal.wrap.classList.add('hidden');
  });
});
