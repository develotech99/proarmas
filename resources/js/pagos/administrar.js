// Gestión de Movimientos Bancarios - Admin Panel
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
const BANKS = { '1': 'Banrural', '2': 'Banco Industrial' };

// DOM Elements
const uploadZone = document.getElementById('uploadZone');
const archivoInput = document.getElementById('archivoMovimientos');
const uploadContent = document.getElementById('uploadContent');
const fileInfo = document.getElementById('fileInfo');
const fileName = document.getElementById('fileName');
const fileSize = document.getElementById('fileSize');
const bancoSelect = document.getElementById('bancoOrigen');
const fechaInicio = document.getElementById('fechaInicio');
const fechaFin = document.getElementById('fechaFin');
const btnProcesar = document.getElementById('btnProcesar');
const btnVistaPrevia = document.getElementById('btnVistaPrevia');
const btnLimpiar = document.getElementById('btnLimpiar');
const vistaPrevia = document.getElementById('vistaPrevia');
const tablaPrevia = document.getElementById('cuerpoTablaPrevia');
const procesamientoEstado = document.getElementById('procesamientoEstado');

// Estado global
let archivoActual = null;
let datosMovimientos = [];
let estadisticas = {
    validacionesExitosas: 0,
    pendientesValidacion: 0,
    totalMovimientos: 0,
    ultimaCarga: null
};

/* ========== UTILIDADES ========== */
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('es-GT', {
        style: 'currency',
        currency: 'GTQ',
        minimumFractionDigits: 2
    }).format(amount);
};

const formatDate = (dateString) => {
    try {
        // Manejar diferentes formatos de fecha
        let date;
        if (dateString.includes('/')) {
            const parts = dateString.split('/');
            // Asumiendo DD/MM/YYYY
            date = new Date(parts[2], parts[1] - 1, parts[0]);
        } else {
            date = new Date(dateString);
        }

        return date.toLocaleDateString('es-GT', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    } catch (e) {
        return dateString;
    }
};

const showSuccess = (title, text) => {
    Swal.fire({
        icon: 'success',
        title: title,
        text: text,
        confirmButtonColor: '#10B981'
    });
};

const showError = (title, text) => {
    Swal.fire({
        icon: 'error',
        title: title,
        text: text,
        confirmButtonColor: '#EF4444'
    });
};

const showLoading = (title) => {
    Swal.fire({
        title: title,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
};

/* ========== MANEJO DE ARCHIVOS ========== */
const updateFileInfo = (file) => {
    if (!file) return;

    archivoActual = file;
    fileName.textContent = file.name;
    fileSize.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB`;

    uploadContent.classList.add('hidden');
    fileInfo.classList.remove('hidden');

    // Habilitar botones
    btnVistaPrevia.removeAttribute('disabled');
    updateProcessButton();
};

const updateProcessButton = () => {
    const hasFile = archivoActual !== null;
    const hasBanco = bancoSelect.value !== '';
    const hasFechas = fechaInicio.value !== '' && fechaFin.value !== '';

    if (hasFile && hasBanco && hasFechas) {
        btnProcesar.removeAttribute('disabled');
    } else {
        btnProcesar.setAttribute('disabled', 'disabled');
    }
};

const limpiarFormulario = () => {
    archivoActual = null;
    datosMovimientos = [];
    archivoInput.value = '';
    bancoSelect.value = '';
    fechaInicio.value = '';
    fechaFin.value = '';

    uploadContent.classList.remove('hidden');
    fileInfo.classList.add('hidden');
    vistaPrevia.classList.add('hidden');

    btnVistaPrevia.setAttribute('disabled', 'disabled');
    btnProcesar.setAttribute('disabled', 'disabled');

    updateEstadoProcesamiento('Esperando archivo...');
};

/* ========== PROCESAMIENTO DE CSV ========== */
const parseCSV = (file) => {
    return new Promise((resolve, reject) => {
        Papa.parse(file, {
            header: true,
            skipEmptyLines: true,
            encoding: 'UTF-8',
            complete: (results) => {
                if (results.errors.length > 0) {
                    console.warn('Errores en CSV:', results.errors);
                }
                resolve(results.data);
            },
            error: reject
        });
    });
};

const procesarMovimientos = (rawData) => {
    const movimientos = [];

    rawData.forEach((row, index) => {
        try {
            // Mapear columnas del CSV guatemalteco
            const fecha = row['Fecha'] || row['fecha'] || '';
            const descripcion = row['Descripción'] || row['Descripcion'] || row['descripcion'] || '';
            const referencia = row['Referencia'] || row['referencia'] || row['Secuencial'] || '';
            const debito = row['Débito (-)'] || row['Debito (-)'] || row['debito'] || '0';
            const credito = row['Crédito (+)'] || row['Credito (+)'] || row['credito'] || '0';

            // Limpiar y convertir montos
            const montoDebito = parseFloat(debito.toString().replace(/[^0-9.-]/g, '')) || 0;
            const montoCredito = parseFloat(credito.toString().replace(/[^0-9.-]/g, '')) || 0;

            // Solo procesar movimientos con crédito (depósitos)
            if (montoCredito > 0) {
                movimientos.push({
                    id: index,
                    fecha: fecha.trim(),
                    descripcion: descripcion.trim(),
                    referencia: referencia.toString().trim(),
                    monto: montoCredito,
                    tipo: 'CREDITO',
                    estado: 'PENDIENTE',
                    coincidencias: []
                });
            }
        } catch (error) {
            console.warn(`Error procesando fila ${index}:`, error);
        }
    });

    return movimientos;
};

/* ========== VALIDACIÓN AUTOMÁTICA ========== */
const validarPagosAutomaticamente = async (movimientos) => {
    showLoading('Validando pagos automáticamente...');

    try {
        const response = await fetch('/admin/movimientos/validar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({
                movimientos: movimientos,
                banco_id: bancoSelect.value,
                fecha_inicio: fechaInicio.value,
                fecha_fin: fechaFin.value
            })
        });

        const result = await response.json();

        if (result.codigo === 1) {
            Swal.close();

            // Mostrar resultados de la validación
            await Swal.fire({
                icon: 'success',
                title: '¡Validación Completada!',
                html: `
                    <div class="text-left">
                        <div class="bg-green-50 p-4 rounded-lg mb-4">
                            <h4 class="font-semibold text-green-800 mb-2">Resultados:</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span>✅ Pagos Validados:</span>
                                    <span class="font-bold text-green-600">${result.data.validados}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>⏳ Pendientes:</span>
                                    <span class="font-bold text-yellow-600">${result.data.pendientes}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>📊 Total Procesados:</span>
                                    <span class="font-bold text-blue-600">${result.data.procesados}</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600">Los pagos han sido actualizados automáticamente.</p>
                    </div>
                `,
                confirmButtonColor: '#10B981'
            });

            // Actualizar estadísticas
            actualizarEstadisticas();
            cargarHistorial();

        } else {
            Swal.close();
            showError('Error de Validación', result.mensaje || 'No se pudieron validar los movimientos');
        }

    } catch (error) {
        console.error('Error validando pagos:', error);
        Swal.close();
        showError('Error de Conexión', 'No se pudo conectar con el servidor');
    }
};

/* ========== UI UPDATES ========== */
const updateEstadoProcesamiento = (mensaje, tipo = 'info') => {
    if (!procesamientoEstado) return;

    let icon, colorClass;
    switch (tipo) {
        case 'success':
            icon = `<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>`;
            colorClass = 'text-green-600';
            break;
        case 'error':
            icon = `<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>`;
            colorClass = 'text-red-600';
            break;
        case 'processing':
            icon = `<svg class="w-5 h-5 text-blue-500 processing-animation" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>`;
            colorClass = 'text-blue-600';
            break;
        default:
            icon = `<svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>`;
            colorClass = 'text-gray-500';
    }

    procesamientoEstado.innerHTML = `
        <div class="flex items-center ${colorClass}">
            ${icon}
            <span class="text-sm ml-3">${mensaje}</span>
        </div>
    `;
};

const renderVistaPrevia = (movimientos) => {
    if (!tablaPrevia) return;

    tablaPrevia.innerHTML = '';

    movimientos.slice(0, 50).forEach((mov, index) => {
        const row = document.createElement('tr');
        row.className = index % 2 === 0 ? 'bg-white' : 'bg-gray-50';

        row.innerHTML = `
            <td class="px-4 py-3 text-sm text-gray-900">${formatDate(mov.fecha)}</td>
            <td class="px-4 py-3 text-sm text-gray-900">${mov.descripcion}</td>
            <td class="px-4 py-3 text-sm font-mono text-blue-600">${mov.referencia}</td>
            <td class="px-4 py-3 text-sm font-semibold text-green-600">${formatCurrency(mov.monto)}</td>
            <td class="px-4 py-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    Pendiente
                </span>
            </td>
        `;

        tablaPrevia.appendChild(row);
    });

    if (movimientos.length > 50) {
        const moreRow = document.createElement('tr');
        moreRow.innerHTML = `
            <td colspan="5" class="px-4 py-3 text-center text-sm text-gray-500">
                ... y ${movimientos.length - 50} movimientos más
            </td>
        `;
        tablaPrevia.appendChild(moreRow);
    }

    vistaPrevia.classList.remove('hidden');
};

const actualizarEstadisticas = async () => {
    try {
        const response = await fetch('/admin/movimientos/estadisticas');
        const result = await response.json();

        if (result.codigo === 1) {
            const stats = result.data;

            document.getElementById('validacionesExitosas').textContent = stats.validados || 0;
            document.getElementById('pendientesValidacion').textContent = stats.pendientes || 0;
            document.getElementById('totalMovimientos').textContent = stats.total || 0;
            document.getElementById('ultimaCarga').textContent = stats.ultimaCarga || '—';
        }
    } catch (error) {
        console.warn('Error cargando estadísticas:', error);
    }
};

const cargarHistorial = async () => {
    try {
        const response = await fetch('/admin/movimientos/historial');
        const result = await response.json();

        if (result.codigo === 1) {
            const historial = document.getElementById('historialCargas');
            if (!historial) return;

            historial.innerHTML = '';

            if (result.data.length === 0) {
                historial.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V6a2 2 0 012-2h5.5L16 8.5V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-sm">No hay cargas recientes</p>
                    </div>
                `;
                return;
            }

            result.data.forEach(carga => {
                const div = document.createElement('div');
                div.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
                div.innerHTML = `
                    <div>
                        <p class="text-sm font-medium text-gray-900">${BANKS[carga.banco_id] || 'Banco'}</p>
                        <p class="text-xs text-gray-500">${formatDate(carga.fecha)}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-blue-600">${carga.registros} movimientos</p>
                        <p class="text-xs text-green-600">${carga.validados} validados</p>
                    </div>
                `;
                historial.appendChild(div);
            });
        }
    } catch (error) {
        console.warn('Error cargando historial:', error);
    }
};

/* ========== EVENT LISTENERS ========== */
// Upload Zone Events
uploadZone?.addEventListener('click', () => {
    archivoInput?.click();
});

uploadZone?.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('dragover');
});

uploadZone?.addEventListener('dragleave', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
});

uploadZone?.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        updateFileInfo(files[0]);
    }
});

// File Input Change
archivoInput?.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        updateFileInfo(e.target.files[0]);
    }
});

// Form Controls
bancoSelect?.addEventListener('change', updateProcessButton);
fechaInicio?.addEventListener('change', updateProcessButton);
fechaFin?.addEventListener('change', updateProcessButton);

// Vista Previa Button
btnVistaPrevia?.addEventListener('click', async () => {
    if (!archivoActual) {
        showError('Error', 'No hay archivo seleccionado');
        return;
    }

    updateEstadoProcesamiento('Procesando vista previa...', 'processing');

    try {
        const data = await parseCSV(archivoActual);
        datosMovimientos = procesarMovimientos(data);

        if (datosMovimientos.length === 0) {
            updateEstadoProcesamiento('No se encontraron movimientos válidos', 'error');
            showError('Sin Datos', 'No se encontraron movimientos de crédito en el archivo');
            return;
        }

        renderVistaPrevia(datosMovimientos);
        updateEstadoProcesamiento(`${datosMovimientos.length} movimientos listos para procesar`, 'success');

    } catch (error) {
        console.error('Error procesando archivo:', error);
        updateEstadoProcesamiento('Error procesando archivo', 'error');
        showError('Error de Archivo', 'No se pudo procesar el archivo. Verifique el formato.');
    }
});

// Procesar Button
btnProcesar?.addEventListener('click', async () => {
    if (datosMovimientos.length === 0) {
        showError('Sin Datos', 'Primero debe generar la vista previa del archivo');
        return;
    }

    const confirmed = await Swal.fire({
        title: '¿Procesar movimientos?',
        html: `
            <div class="text-left">
                <p class="mb-4">Se procesarán <strong>${datosMovimientos.length} movimientos</strong> del banco <strong>${BANKS[bancoSelect.value]}</strong></p>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-sm"><strong>Período:</strong> ${fechaInicio.value} a ${fechaFin.value}</p>
                    <p class="text-sm"><strong>Total a validar:</strong> ${formatCurrency(datosMovimientos.reduce((sum, m) => sum + m.monto, 0))}</p>
                </div>
                <p class="mt-4 text-sm text-gray-600">Esta acción buscará coincidencias automáticamente y validará los pagos correspondientes.</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, procesar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10B981'
    });

    if (confirmed.isConfirmed) {
        updateEstadoProcesamiento('Validando pagos...', 'processing');
        await validarPagosAutomaticamente(datosMovimientos);
    }
});

// Limpiar Button
btnLimpiar?.addEventListener('click', async () => {
    const confirmed = await Swal.fire({
        title: '¿Limpiar formulario?',
        text: 'Se perderán todos los datos cargados',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#6B7280'
    });

    if (confirmed.isConfirmed) {
        limpiarFormulario();
    }
});

/* ========== INICIALIZACIÓN ========== */
document.addEventListener('DOMContentLoaded', () => {
    actualizarEstadisticas();
    cargarHistorial();

    // Establecer fechas por defecto (último mes)
    const hoy = new Date();
    const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);

    if (fechaInicio) fechaInicio.value = inicioMes.toISOString().split('T')[0];
    if (fechaFin) fechaFin.value = hoy.toISOString().split('T')[0];
});

console.log('Sistema de gestión de movimientos bancarios cargado ✅');