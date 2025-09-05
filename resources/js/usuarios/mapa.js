// resources/js/usuarios/mapa.js
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Fix de iconos con Vite
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

// --------- Selectores ----------
const $ = (id) => document.getElementById(id);
const elMap = () => $('map');
const inpLat = () => $('lat');
const inpLng = () => $('lng');
const btnMiUbicacion = () => $('btn_mi_ubicacion');
const btnLimpiar = () => $('btn_limpiar_mapa');
const btnTomarPosicion = () => $('btn_tomar_posicion');
const btnPlotear = () => $('btn_plotear');
const btnBuscarLugar = () => $('btn_buscar_lugar');
const inputBusqueda = () => $('busqueda_lugar');
const statusMapa = () => $('status_mapa');
const zoomLevel = () => $('zoom_level');

// NUEVOS selectores para fullscreen
const btnFullscreen = () => $('btn_fullscreen');
const iconExpand = () => $('icon_expand');
const iconCompress = () => $('icon_compress');
const mapCard = () => $('map_card');

// --------- Estado ----------
const DEFAULT_CENTER = [14.6349, -90.5069];
const DEFAULT_ZOOM = 13;

let map;
let marker = null;
let clickEnabled = true; // click en mapa siempre actualiza por defecto

// --------- Map HUD (overlay + progress) ----------
function mountMapHUD() {
  const mapWrap = document.getElementById('map')?.parentElement; // el contenedor directo del #map
  if (!mapWrap) return;
  const toCard = mapWrap.closest('.rounded-2xl') || mapWrap; // card del mapa
  if (!toCard) return;

  // Solo montar una vez
  if (document.getElementById('map-hud-overlay')) return;

  // Aseguramos posicionamiento relativo
  if (!['relative', 'absolute', 'fixed'].includes(getComputedStyle(toCard).position)) {
    toCard.style.position = 'relative';
  }

  // Overlay centrado
  const overlay = document.createElement('div');
  overlay.id = 'map-hud-overlay';
  overlay.style.cssText = `
    position:absolute; inset:0; display:flex; align-items:center; justify-content:center;
    background:rgba(0,0,0,.2); backdrop-filter:blur(4px);
    opacity:0; transition:opacity .2s ease;
    pointer-events:none;
  `;
  overlay.innerHTML = `
    <div id="map-hud-box"
         style="pointer-events:auto; border-radius:1rem; padding:.9rem 1.2rem;
                background:rgba(255,255,255,.92); color:#0f172a;
                border:1px solid rgba(203,213,225,.8); box-shadow:0 10px 30px rgba(0,0,0,.12);
                display:flex; align-items:center; gap:.6rem;">
      <svg style="height:20px;width:20px;animation:spin 1s linear infinite;color:#4f46e5" viewBox="0 0 24 24" fill="none">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v3a5 5 0 00-5 5H4z"></path>
      </svg>
      <div>
        <div id="map-hud-title" style="font-size:.9rem; font-weight:600;">Trabajando…</div>
        <div id="map-hud-sub" style="font-size:.75rem; color:#64748b;">Espera un momento</div>
      </div>
    </div>
  `;

  // Barra de progreso superior
  const progress = document.createElement('div');
  progress.id = 'map-hud-progress';
  progress.style.cssText = `
    position:absolute; left:0; right:0; top:0; height:3px; overflow:hidden;
    opacity:0; transition:opacity .2s ease;
  `;
  const bar = document.createElement('div');
  bar.id = 'map-hud-bar';
  bar.style.cssText = `
    height:100%;
    background:linear-gradient(90deg,#6366f1,#06b6d4,#a21caf);
    width:40%;
    animation:progress-move 1.1s linear infinite;
    transform:translateX(-100%);
  `;
  progress.appendChild(bar);

  // keyframes
  const style = document.createElement('style');
  style.textContent = `
    @keyframes spin { from {transform:rotate(0)} to {transform:rotate(360deg)} }
    @keyframes progress-move {
      0% { transform:translateX(-100%); }
      100% { transform:translateX(300%); }
    }
  `;

  toCard.appendChild(progress);
  toCard.appendChild(overlay);
  document.head.appendChild(style);
}
function hudShow(title = 'Procesando…', sub = 'Por favor espera') {
  mountMapHUD();
  const overlay = document.getElementById('map-hud-overlay');
  const progress = document.getElementById('map-hud-progress');
  const t = document.getElementById('map-hud-title');
  const s = document.getElementById('map-hud-sub');
  if (t) t.textContent = title;
  if (s) s.textContent = sub;
  if (overlay) overlay.style.opacity = '1';
  if (progress) progress.style.opacity = '1';
}
function hudOk(msg = 'Listo') {
  const t = document.getElementById('map-hud-title');
  const s = document.getElementById('map-hud-sub');
  if (t) t.textContent = msg;
  if (s) s.textContent = ' ';
  const box = document.getElementById('map-hud-box');
  if (box) {
    box.style.boxShadow = '0 0 0 4px rgba(16,185,129,.3), 0 10px 30px rgba(0,0,0,.12)';
    setTimeout(() => (box.style.boxShadow = '0 10px 30px rgba(0,0,0,.12)'), 400);
  }
}
function hudError(msg = 'Error al obtener datos') {
  const t = document.getElementById('map-hud-title');
  const s = document.getElementById('map-hud-sub');
  if (t) t.textContent = msg;
  if (s) s.textContent = 'Intenta de nuevo';
  const box = document.getElementById('map-hud-box');
  if (box) {
    box.style.boxShadow = '0 0 0 4px rgba(244,63,94,.28), 0 10px 30px rgba(0,0,0,.12)';
    setTimeout(() => (box.style.boxShadow = '0 10px 30px rgba(0,0,0,.12)'), 500);
  }
}
function hudHide() {
  const overlay = document.getElementById('map-hud-overlay');
  const progress = document.getElementById('map-hud-progress');
  if (overlay) overlay.style.opacity = '0';
  if (progress) progress.style.opacity = '0';
}

// Botón → loading spinner inline
function btnSetLoading(btn, loading = true, labelWhile = 'Buscando…') {
  if (!btn) return;
  if (loading) {
    btn.dataset._prev = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `
      <span class="inline-flex items-center gap-2">
        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v3a5 5 0 00-5 5H4z"></path>
        </svg>
        ${labelWhile}
      </span>`;
  } else {
    btn.disabled = false;
    if (btn.dataset._prev) btn.innerHTML = btn.dataset._prev;
  }
}

// --------- Helpers de mapa ----------
function setStatus(text) {
  if (statusMapa()) statusMapa().textContent = text;
}
function fillInputs(lat, lng) {
  if (inpLat()) inpLat().value = lat;
  if (inpLng()) inpLng().value = lng;
}
function setMarker(lat, lng, title = 'Ubicación') {
  if (!marker) {
    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
    marker.on('dragend', (ev) => {
      const { lat, lng } = ev.target.getLatLng();
      fillInputs(lat, lng);
      marker.bindPopup(`<b>${title}</b><br>Lat: ${lat}<br>Lng: ${lng}`);
      setStatus(`Marcador movido: ${lat.toFixed(6)}, ${lng.toFixed(6)}`);
    });
  } else {
    marker.setLatLng([lat, lng]);
  }
  marker.bindPopup(`<b>${title}</b><br>Lat: ${lat}<br>Lng: ${lng}`).openPopup();
}
function flyTo(lat, lng, zoom = 16) {
  map.flyTo([lat, lng], zoom, { duration: 0.5 });
}
function clearMarker() {
  if (marker) {
    map.removeLayer(marker);
    marker = null;
  }
  setStatus('Marcador eliminado.');
}

// --------- Capas base ----------
function baseLayers() {
  const satelite = L.tileLayer(
    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
    { attribution: 'Tiles © Esri', maxZoom: 19 }
  );
  const etiquetas = L.tileLayer(
    'https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}',
    { attribution: 'Labels © Esri', maxZoom: 19 }
  );
  return { satelite, etiquetas };
}

// --------- Init ----------
function initMap() {
  const { satelite, etiquetas } = baseLayers();

  map = L.map(elMap(), {
    center: DEFAULT_CENTER,
    zoom: DEFAULT_ZOOM,
    layers: [satelite, etiquetas],
    zoomControl: true,
  });

  map.on('click', (e) => {
    if (!clickEnabled) return;
    const { lat, lng } = e.latlng;
    setMarker(lat, lng, 'Ubicación seleccionada');
    fillInputs(lat, lng);
    setStatus(`Marcado: ${lat.toFixed(6)}, ${lng.toFixed(6)}`);
  });

  map.on('zoomend', () => {
    if (zoomLevel()) zoomLevel().textContent = `Zoom: ${map.getZoom()}`;
  });
  if (zoomLevel()) zoomLevel().textContent = `Zoom: ${map.getZoom()}`;

  setMarker(DEFAULT_CENTER[0], DEFAULT_CENTER[1], 'Ciudad de Guatemala');
  setStatus('Haz clic en el mapa o usa los botones para ubicar al cliente.');
}

// --------- Mi ubicación ----------
function goToMyLocation() {
  if (!navigator.geolocation) {
    setStatus('Tu navegador no soporta geolocalización.');
    return;
  }
  hudShow('Obteniendo tu ubicación…', 'Usando geolocalización del navegador');
  setStatus('Obteniendo tu ubicación…');

  navigator.geolocation.getCurrentPosition(
    ({ coords }) => {
      const { latitude, longitude, accuracy } = coords;
      const precise = accuracy && accuracy < 150;
      const lat = precise ? latitude : DEFAULT_CENTER[0];
      const lng = precise ? longitude : DEFAULT_CENTER[1];

      setMarker(lat, lng, 'Mi ubicación');
      fillInputs(lat, lng);
      flyTo(lat, lng, 16);

      hudOk(precise ? 'Ubicación precisa' : 'Ubicación aproximada');
      setStatus(
        precise
          ? `Ubicación precisa (~${Math.round(accuracy)}m).`
          : 'Ubicación poco precisa; usando centro por defecto.'
      );
      setTimeout(() => hudHide(), 500);
    },
    () => {
      setMarker(DEFAULT_CENTER[0], DEFAULT_CENTER[1], 'Ubicación predeterminada');
      fillInputs(DEFAULT_CENTER[0], DEFAULT_CENTER[1]);
      flyTo(DEFAULT_CENTER[0], DEFAULT_CENTER[1], DEFAULT_ZOOM);

      hudError('No se pudo obtener tu ubicación');
      setStatus('No se pudo obtener la ubicación. Usando ubicación predeterminada.');
      setTimeout(() => hudHide(), 800);
    },
    { enableHighAccuracy: true, timeout: 8000 }
  );
}

// --------- Buscar por nombre (Nominatim) ----------
async function searchPlace() {
  const btn = btnBuscarLugar();
  const input = inputBusqueda();
  const q = (input?.value || '').trim();

  if (!q) {
    setStatus('Ingresa un lugar para buscar.');
    return;
  }

  hudShow('Buscando lugar…', 'Consultando geocodificador');
  btnSetLoading(btn, true, 'Buscando…');
  setStatus('Buscando…');

  try {
    const url = `https://nominatim.openstreetmap.org/search?format=geojson&limit=5&accept-language=es&q=${encodeURIComponent(q)}`;
    const res = await fetch(url, { headers: { 'Accept-Language': 'es' } });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();

    if (!data.features || data.features.length === 0) {
      hudError('Sin resultados');
      setStatus('Sin resultados.');
      return;
    }

    const f = data.features[0];
    const [lng, lat] = f.geometry.coordinates;
    const p = f.properties || {};
    const nombre = p.display_name || p.name || p.street || p.city || 'Ubicación';

    setMarker(lat, lng, nombre);
    fillInputs(lat, lng);
    flyTo(lat, lng, 17);

    hudOk('Ubicación encontrada');
    setStatus(`Resultado: ${nombre}`);
  } catch (e) {
    console.error(e);
    hudError('Error al buscar');
    setStatus('Error al buscar el lugar.');
  } finally {
    setTimeout(() => hudHide(), 400);
    btnSetLoading(btn, false);
  }
}

// --------- Plotear desde inputs ----------
function plotFromInputs() {
  const lat = parseFloat(inpLat()?.value || '');
  const lng = parseFloat(inpLng()?.value || '');
  if (
    Number.isFinite(lat) &&
    Number.isFinite(lng) &&
    lat >= -90 && lat <= 90 &&
    lng >= -180 && lng <= 180
  ) {
    setMarker(lat, lng, 'Ubicación ingresada');
    flyTo(lat, lng, 17);
    setStatus('Ploteado desde los campos.');
  } else {
    setStatus('Coordenadas inválidas. Revisa latitud/longitud.');
  }
}

// --------- Toggle Click Mode (opcional) ----------
function toggleClickMode() {
  clickEnabled = !clickEnabled;
  if (clickEnabled) {
    setStatus('Modo selección activado: haz clic en el mapa para marcar.');
    btnTomarPosicion()?.classList.add('ring', 'ring-indigo-300');
  } else {
    setStatus('Modo selección desactivado.');
    btnTomarPosicion()?.classList.remove('ring', 'ring-indigo-300');
  }
}

// --------- Pantalla completa (Fullscreen API) ----------
function getFullscreenTarget() {
  // Hacemos fullscreen del CARD para que el botón siga visible
  return mapCard() || elMap();
}
function isFullscreen() {
  return document.fullscreenElement === getFullscreenTarget();
}
async function enterFullscreen() {
  const target = getFullscreenTarget();
  if (!target) return;
  if (!document.fullscreenElement) {
    await target.requestFullscreen();
  }
}
async function exitFullscreen() {
  if (document.fullscreenElement) {
    await document.exitFullscreen();
  }
}
async function toggleFullscreen() {
  try {
    if (!document.fullscreenElement) {
      await enterFullscreen();
    } else {
      await exitFullscreen();
    }
  } catch (err) {
    console.error('Fullscreen error:', err);
    setStatus('No se pudo cambiar a pantalla completa.');
  }
}
function onFullscreenChange() {
  // Recalcular Leaflet tras cambiar layout
  setTimeout(() => {
    if (map) map.invalidateSize();
  }, 60);

  // Cambiar iconos
  const exp = iconExpand();
  const comp = iconCompress();
  const inFS = !!document.fullscreenElement;
  if (exp && comp) {
    exp.classList.toggle('hidden', inFS);
    comp.classList.toggle('hidden', !inFS);
  }

  // Tooltip y estado
  const btn = btnFullscreen();
  if (btn) btn.title = inFS ? 'Salir de pantalla completa' : 'Pantalla completa';
  setStatus(inFS ? 'Mapa en pantalla completa.' : 'Mapa en modo normal.');
}

// --------- Bind UI ----------
function bindUI() {
  btnMiUbicacion()?.addEventListener('click', goToMyLocation);
  btnLimpiar()?.addEventListener('click', clearMarker);
  btnPlotear()?.addEventListener('click', plotFromInputs);
  btnBuscarLugar()?.addEventListener('click', searchPlace);
  inputBusqueda()?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      searchPlace();
    }
  });
  btnTomarPosicion()?.addEventListener('click', toggleClickMode);

  // NUEVO: botón fullscreen
  btnFullscreen()?.addEventListener('click', toggleFullscreen);
}

// --------- Bootstrap ----------
document.addEventListener('DOMContentLoaded', () => {
  if (!elMap()) return;
  initMap();
  bindUI();
  // Monta HUD para que esté listo (invisible hasta que lo uses)
  mountMapHUD();

  // NUEVO: escuchar cambios de fullscreen
  document.addEventListener('fullscreenchange', onFullscreenChange);
});
