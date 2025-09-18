import './bootstrap';

import Alpine from 'alpinejs';
import Swal from 'sweetalert2';

window.Swal = Swal;
window.Alpine = Alpine;

Alpine.start();

/**
 * Validación simple de formularios
 * @param {HTMLFormElement} formulario 
 * @param {string[]} excepciones 
 * @returns {boolean} 
 */
export const validarFormulario = (formulario, excepciones = []) => {
    const elements = formulario.querySelectorAll("input, select, textarea");
    let hayErrores = false;
    let primerCampoInvalido = null;


    elements.forEach(element => {
        element.style.borderColor = '';
        element.style.borderWidth = '';
        element.style.backgroundColor = '';
        element.style.boxShadow = '';
        element.classList.remove('error-field');
    });

    elements.forEach(element => {
        const estaVacio = !element.value.trim();
        const estaExcluido = excepciones.includes(element.id) || excepciones.includes(element.name);
        
        if (estaVacio && !estaExcluido) {
            element.style.borderColor = '#3b82f6'; 
            element.style.borderWidth = '2px';
            element.style.backgroundColor = '#eff6ff'; 
            element.style.boxShadow = '0 0 0 2px rgba(59, 130, 246, 0.12)'; 
            element.classList.add('error-field');
            
            // Marcar label si existe
            const label = formulario.querySelector(`label[for="${element.id}"]`);
            if (label) {
                label.style.color = '#1d4ed8'; 
                label.style.fontWeight = '500'; 
            }
            
            hayErrores = true;
            if (!primerCampoInvalido) {
                primerCampoInvalido = element;
            }
            

            element.addEventListener('input', function limpiar() {
                element.style.borderColor = '';
                element.style.borderWidth = '';
                element.style.backgroundColor = '';
                element.style.boxShadow = '';
                element.classList.remove('error-field');
                
                const label = formulario.querySelector(`label[for="${element.id}"]`);
                if (label) {
                    label.style.color = '';
                    label.style.fontWeight = '';
                }
                element.removeEventListener('input', limpiar);
            });
        }
    });

    if (hayErrores) {
        const nombreCampo = primerCampoInvalido.id || primerCampoInvalido.name || 'Campo';
        const label = formulario.querySelector(`label[for="${primerCampoInvalido.id}"]`);
        const textoLabel = label?.textContent?.trim() || nombreCampo;

        Swal.fire({
            icon: 'error',
            title: 'Revisa los datos',
            text: `Falta completar: ${textoLabel}`,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Entendido'
        });

        primerCampoInvalido.focus();
        primerCampoInvalido.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        return false;
    }

    return true;
};

window.validarFormulario = validarFormulario;