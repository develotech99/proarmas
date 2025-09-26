/* ==== MÓDULO DE ANÁLISIS INTELIGENTE ==== */

export default class PaymentAnalyzer {
    constructor() {
        this.confidence = {
            HIGH: 0.8,
            MEDIUM: 0.6,
            LOW: 0.3
        };
    }

    // Analizar calidad de datos extraídos
    analyzeDataQuality(parsed, selectedCuotas) {
        const analysis = {
            overall_confidence: 0,
            issues: [],
            suggestions: [],
            warnings: [],
            validations: {},
            conclusions: []
        };

        // 1. Validar fecha
        const fechaAnalysis = this.analyzeFecha(parsed.fecha);
        analysis.validations.fecha = fechaAnalysis;

        // 2. Validar monto
        const montoAnalysis = this.analyzeMonto(parsed.monto, selectedCuotas);
        analysis.validations.monto = montoAnalysis;

        // 3. Validar referencia
        const refAnalysis = this.analyzeReferencia(parsed.referencia);
        analysis.validations.referencia = refAnalysis;

        // 4. Validar banco
        const bancoAnalysis = this.analyzeBanco(parsed.banco_id, parsed.banco_nombre);
        analysis.validations.banco = bancoAnalysis;

        // 5. Análisis de coherencia general
        const coherenceAnalysis = this.analyzeCoherence(parsed, selectedCuotas);
        analysis.coherence = coherenceAnalysis;

        // 6. Calcular confianza general
        analysis.overall_confidence = this.calculateOverallConfidence(analysis.validations);

        // 7. Generar conclusiones y recomendaciones
        this.generateConclusions(analysis);

        return analysis;
    }

    analyzeFecha(fecha) {
        const analysis = { confidence: 0, status: 'error', message: '', suggestions: [] };

        if (!fecha) {
            analysis.message = 'Fecha no detectada';
            analysis.suggestions.push('Revise que la fecha sea clara en la imagen');
            return analysis;
        }

        const fechaDate = new Date(fecha);
        const ahora = new Date();
        const hace30Dias = new Date(ahora.getTime() - (30 * 24 * 60 * 60 * 1000));
        const enUnHora = new Date(ahora.getTime() + (60 * 60 * 1000));

        if (isNaN(fechaDate.getTime())) {
            analysis.message = 'Formato de fecha inválido';
            analysis.suggestions.push('Ingrese la fecha manualmente en formato correcto');
            return analysis;
        }

        if (fechaDate > enUnHora) {
            analysis.confidence = 0.2;
            analysis.status = 'warning';
            analysis.message = 'La fecha parece estar en el futuro';
            analysis.suggestions.push('Verifique que el año sea correcto');
        } else if (fechaDate < hace30Dias) {
            analysis.confidence = 0.4;
            analysis.status = 'warning';
            analysis.message = 'Comprobante de más de 30 días';
            analysis.suggestions.push('Confirme que sea el comprobante correcto');
        } else {
            analysis.confidence = 0.9;
            analysis.status = 'success';
            analysis.message = 'Fecha válida y reciente';
        }

        return analysis;
    }

    analyzeMonto(monto, selectedCuotas) {
        const analysis = { confidence: 0, status: 'error', message: '', suggestions: [], discrepancy: 0 };

        const montoNum = Number(monto);
        const totalCuotas = selectedCuotas.reduce((sum, c) => sum + c.monto, 0);

        if (!monto || montoNum <= 0) {
            analysis.message = 'Monto no detectado o inválido';
            analysis.suggestions.push('Verifique que el monto sea visible en la imagen');
            return analysis;
        }

        const diferencia = Math.abs(montoNum - totalCuotas);
        const porcentajeDif = (diferencia / totalCuotas) * 100;
        analysis.discrepancy = porcentajeDif;

        if (porcentajeDif === 0) {
            analysis.confidence = 1.0;
            analysis.status = 'success';
            analysis.message = `Monto exacto: ${this.formatCurrency(montoNum)}`;
        } else if (porcentajeDif < 1) {
            analysis.confidence = 0.9;
            analysis.status = 'success';
            analysis.message = `Monto casi exacto (diferencia: ${this.formatCurrency(diferencia)})`;
        } else if (porcentajeDif < 5) {
            analysis.confidence = 0.7;
            analysis.status = 'warning';
            analysis.message = `Diferencia menor del 5% (${this.formatCurrency(diferencia)})`;
            analysis.suggestions.push('Podría ser correcto, pero verifique el monto');
        } else if (montoNum > totalCuotas) {
            analysis.confidence = 0.6;
            analysis.status = 'warning';
            analysis.message = `Pago excedente de ${this.formatCurrency(diferencia)}`;
            analysis.suggestions.push('El excedente se aplicará como anticipo');
        } else {
            analysis.confidence = 0.3;
            analysis.status = 'error';
            analysis.message = `Monto insuficiente (falta ${this.formatCurrency(diferencia)})`;
            analysis.suggestions.push('Verifique el monto o seleccione menos cuotas');
        }

        return analysis;
    }

    analyzeReferencia(referencia) {
        const analysis = { confidence: 0, status: 'error', message: '', suggestions: [] };

        if (!referencia) {
            analysis.message = 'Referencia no detectada';
            analysis.suggestions.push('Busque el número de autorización en el comprobante');
            return analysis;
        }

        const ref = String(referencia).trim();

        if (ref.length < 6) {
            analysis.confidence = 0.2;
            analysis.status = 'warning';
            analysis.message = 'Referencia muy corta';
            analysis.suggestions.push('Las referencias suelen tener al menos 6 caracteres');
        } else if (ref.length >= 6 && ref.length <= 12) {
            analysis.confidence = 0.8;
            analysis.status = 'success';
            analysis.message = 'Referencia con longitud estándar';
        } else if (ref.length > 12) {
            analysis.confidence = 0.6;
            analysis.status = 'warning';
            analysis.message = 'Referencia inusualmente larga';
            analysis.suggestions.push('Verifique que sea solo el número de referencia');
        }

        // Validar formato (solo números y letras)
        if (!/^[A-Z0-9-]+$/i.test(ref)) {
            analysis.confidence *= 0.7;
            analysis.suggestions.push('La referencia contiene caracteres especiales');
        }

        return analysis;
    }

    analyzeBanco(bancoId, bancoNombre) {
        const analysis = { confidence: 0, status: 'error', message: '', suggestions: [] };

        if (!bancoId || !bancoNombre) {
            analysis.message = 'Banco no detectado';
            analysis.suggestions.push('Seleccione manualmente el banco destino');
            return analysis;
        }

        analysis.confidence = 0.9;
        analysis.status = 'success';
        analysis.message = `Banco identificado: ${bancoNombre}`;

        return analysis;
    }

    analyzeCoherence(parsed, selectedCuotas) {
        const coherence = {
            score: 0,
            issues: [],
            observations: []
        };

        // Coherencia fecha-monto
        if (parsed.fecha && parsed.monto) {
            const fechaDate = new Date(parsed.fecha);
            const esFinDeSemana = fechaDate.getDay() === 0 || fechaDate.getDay() === 6;
            const hora = fechaDate.getHours();

            if (esFinDeSemana && hora >= 9 && hora <= 17) {
                coherence.observations.push('Transacción en fin de semana en horario bancario');
            }

            if (hora < 6 || hora > 22) {
                coherence.observations.push('Transacción en horario no convencional');
            }
        }

        // Coherencia monto-concepto
        const montoNum = Number(parsed.monto);
        if (montoNum > 10000 && parsed.concepto && parsed.concepto.toLowerCase().includes('gasto')) {
            coherence.observations.push('Monto elevado para concepto de gastos');
        }

        coherence.score = coherence.issues.length === 0 ? 0.8 : 0.5;
        return coherence;
    }

    calculateOverallConfidence(validations) {
        const weights = { fecha: 0.2, monto: 0.4, referencia: 0.2, banco: 0.2 };
        let totalWeight = 0;
        let weightedSum = 0;

        for (const [field, validation] of Object.entries(validations)) {
            if (weights[field] && validation.confidence !== undefined) {
                weightedSum += validation.confidence * weights[field];
                totalWeight += weights[field];
            }
        }

        return totalWeight > 0 ? weightedSum / totalWeight : 0;
    }

    generateConclusions(analysis) {
        const { overall_confidence, validations } = analysis;

        if (overall_confidence >= this.confidence.HIGH) {
            analysis.conclusions.push('✅ Comprobante parece auténtico y completo');
            analysis.conclusions.push('🎯 Recomendación: Proceder con el pago');
        } else if (overall_confidence >= this.confidence.MEDIUM) {
            analysis.conclusions.push('⚠️ Comprobante parcialmente válido');
            analysis.conclusions.push('🔍 Recomendación: Revisar datos marcados como advertencia');
        } else {
            analysis.conclusions.push('❌ Comprobante requiere revisión');
            analysis.conclusions.push('✋ Recomendación: Ingresar datos manualmente');
        }

        // Conclusiones específicas por campo
        if (validations.monto?.discrepancy > 5) {
            analysis.conclusions.push('💰 Diferencia significativa en monto detectada');
        }

        if (validations.fecha?.status === 'warning') {
            analysis.conclusions.push('📅 Revisar fecha del comprobante');
        }

        if (validations.referencia?.confidence < 0.5) {
            analysis.conclusions.push('🔢 Verificar número de referencia');
        }
    }

    formatCurrency(amount) {
        return 'Q ' + Number(amount).toLocaleString('es-GT', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Generar reporte visual del análisis
    generateAnalysisReport(analysis) {
        const { overall_confidence, validations, conclusions } = analysis;

        let reportHTML = `
            <div class="analysis-report bg-white border border-gray-200 rounded-xl p-6 mt-4">
                <div class="flex items-center mb-4">
                    <div class="bg-blue-500 p-2 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h5 class="text-lg font-semibold text-gray-800">Análisis del Comprobante</h5>
                </div>
                
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Confianza General</span>
                        <span class="text-sm font-semibold ${this.getConfidenceColor(overall_confidence)}">${Math.round(overall_confidence * 100)}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full ${this.getConfidenceBarColor(overall_confidence)}" style="width: ${overall_confidence * 100}%"></div>
                    </div>
                </div>
        `;

        // Detalles por campo
        for (const [field, validation] of Object.entries(validations)) {
            const icon = this.getStatusIcon(validation.status);
            const color = this.getStatusColor(validation.status);

            reportHTML += `
                <div class="border-l-4 ${color} pl-4 mb-3">
                    <div class="flex items-center">
                        ${icon}
                        <span class="font-medium capitalize ml-2">${field}:</span>
                        <span class="ml-2 text-gray-600">${validation.message}</span>
                    </div>
                    ${validation.suggestions && validation.suggestions.length > 0 ?
                    `<div class="text-xs text-gray-500 mt-1">• ${validation.suggestions.join(' • ')}</div>` : ''
                }
                </div>
            `;
        }

        // Conclusiones
        if (conclusions.length > 0) {
            reportHTML += `
                <div class="bg-gray-50 rounded-lg p-4 mt-4">
                    <h6 class="font-semibold text-gray-800 mb-2">Conclusiones:</h6>
                    <ul class="space-y-1">
                        ${conclusions.map(c => `<li class="text-sm text-gray-700">${c}</li>`).join('')}
                    </ul>
                </div>
            `;
        }

        reportHTML += '</div>';
        return reportHTML;
    }

    getConfidenceColor(confidence) {
        if (confidence >= this.confidence.HIGH) return 'text-green-600';
        if (confidence >= this.confidence.MEDIUM) return 'text-yellow-600';
        return 'text-red-600';
    }

    getConfidenceBarColor(confidence) {
        if (confidence >= this.confidence.HIGH) return 'bg-green-500';
        if (confidence >= this.confidence.MEDIUM) return 'bg-yellow-500';
        return 'bg-red-500';
    }

    getStatusIcon(status) {
        switch (status) {
            case 'success':
                return '<svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>';
            case 'warning':
                return '<svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>';
            default:
                return '<svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>';
        }
    }

    getStatusColor(status) {
        switch (status) {
            case 'success': return 'border-green-400';
            case 'warning': return 'border-yellow-400';
            default: return 'border-red-400';
        }
    }
}

const paymentAnalyzer = new PaymentAnalyzer();