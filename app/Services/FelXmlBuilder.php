<?php

namespace App\Services;

use App\Models\Facturacion;
use Carbon\Carbon;
use DOMDocument;
use DateTime;

class FelXmlBuilder
{
    /**
     * Genera el XML del DTE según el esquema de la SAT
     *
     * @param array $datos Datos de la factura
     * @return string XML generado
     */
    public function generarXmlFactura(array $datos): string
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        // Elemento raíz GTDocumento
        $root = $xml->createElementNS('http://www.sat.gob.gt/dte/fel/0.2.0', 'dte:GTDocumento');
        $root->setAttribute('xmlns:dte', 'http://www.sat.gob.gt/dte/fel/0.2.0');
        $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttribute('Version', '0.1');
        $xml->appendChild($root);

        // SAT
        $sat = $xml->createElement('dte:SAT');
        $sat->setAttribute('ClaseDocumento', 'dte');
        $root->appendChild($sat);

        // DTE
        $dte = $xml->createElement('dte:DTE');
        $dte->setAttribute('ID', 'DatosCertificados');
        $sat->appendChild($dte);

        // DatosEmision
        $datosEmision = $xml->createElement('dte:DatosEmision');
        $datosEmision->setAttribute('ID', 'DatosEmision');
        $dte->appendChild($datosEmision);

        // Agregar secciones
        $this->agregarDatosGenerales($xml, $datosEmision, $datos);
        $this->agregarEmisor($xml, $datosEmision);
        $this->agregarReceptor($xml, $datosEmision, $datos);
        $this->agregarFrases($xml, $datosEmision);
        $this->agregarItems($xml, $datosEmision, $datos['items']);
        $this->agregarTotales($xml, $datosEmision, $datos['totales']);

        return $xml->saveXML();
    }

    protected function agregarDatosGenerales($xml, $parent, $datos)
    {
        $dg = $xml->createElement('dte:DatosGenerales');
        $dg->setAttribute('Tipo', 'FACT');
        $dg->setAttribute('FechaHoraEmision', (new DateTime())->format('Y-m-d\TH:i:s'));
        $dg->setAttribute('CodigoMoneda', 'GTQ');
        $parent->appendChild($dg);
    }

    protected function agregarEmisor($xml, $parent)
    {
        $nit = str_replace('-', '', config('fel.emisor.nit'));

        $emisor = $xml->createElement('dte:Emisor');
        $emisor->setAttribute('NITEmisor', $nit);
        $emisor->setAttribute('NombreEmisor', config('fel.emisor.nombre'));
        $emisor->setAttribute('CodigoEstablecimiento', '1');
        $emisor->setAttribute('NombreComercial', config('fel.emisor.nombre_comercial'));
        $emisor->setAttribute('AfiliacionIVA', config('fel.emisor.afiliacion_iva'));

        // DireccionEmisor
        $direccion = $xml->createElement('dte:DireccionEmisor');
        $direccion->appendChild($xml->createElement('dte:Direccion', config('fel.emisor.direccion')));
        $direccion->appendChild($xml->createElement('dte:CodigoPostal', config('fel.emisor.codigo_postal')));
        $direccion->appendChild($xml->createElement('dte:Municipio', config('fel.emisor.municipio')));
        $direccion->appendChild($xml->createElement('dte:Departamento', config('fel.emisor.departamento')));
        $direccion->appendChild($xml->createElement('dte:Pais', config('fel.emisor.pais')));
        $emisor->appendChild($direccion);

        $parent->appendChild($emisor);
    }

    protected function agregarReceptor($xml, $parent, $datos)
    {
        $receptor = $xml->createElement('dte:Receptor');

        $nitReceptor = strtoupper(str_replace('-', '', $datos['receptor']['nit']));

        $receptor->setAttribute('IDReceptor', $nitReceptor);
        $receptor->setAttribute('NombreReceptor', $datos['receptor']['nombre']);

        // DireccionReceptor (opcional pero recomendado)
        if (!empty($datos['receptor']['direccion'])) {
            $direccion = $xml->createElement('dte:DireccionReceptor');
            $direccion->appendChild($xml->createElement('dte:Direccion', $datos['receptor']['direccion']));
            $direccion->appendChild($xml->createElement('dte:CodigoPostal', '01001'));
            $direccion->appendChild($xml->createElement('dte:Municipio', 'Guatemala'));
            $direccion->appendChild($xml->createElement('dte:Departamento', 'Guatemala'));
            $direccion->appendChild($xml->createElement('dte:Pais', 'GT'));
            $receptor->appendChild($direccion);
        }

        $parent->appendChild($receptor);
    }

    protected function agregarFrases($xml, $parent)
    {
        $frases = $xml->createElement('dte:Frases');

        $frase = $xml->createElement('dte:Frase');
        $frase->setAttribute('TipoFrase', '1'); // 1 = Sujeto a pagos trimestrales IVA
        $frase->setAttribute('CodigoEscenario', '1');
        $frases->appendChild($frase);

        $parent->appendChild($frases);
    }

    protected function agregarItems($xml, $parent, $items)
    {
        $itemsElement = $xml->createElement('dte:Items');

        foreach ($items as $index => $item) {
            $itemElement = $xml->createElement('dte:Item');
            $itemElement->setAttribute('NumeroLinea', $index + 1);
            $itemElement->setAttribute('BienOServicio', 'B'); // B = Bien, S = Servicio

            $cantidad = (float) $item['cantidad'];
            $precioUnitario = (float) $item['precio_unitario'];
            $descuento = (float) ($item['descuento'] ?? 0);

            // Precio total del item
            $precioTotal = $cantidad * $precioUnitario;
            $totalConDescuento = $precioTotal - $descuento;

            // Calcular base gravable (sin IVA) y el IVA
            $montoGravable = $totalConDescuento / 1.12;
            $montoIva = $totalConDescuento - $montoGravable;

            $itemElement->appendChild($xml->createElement('dte:Cantidad', number_format($cantidad, 2, '.', '')));
            $itemElement->appendChild($xml->createElement('dte:UnidadMedida', 'UNI'));
            $itemElement->appendChild($xml->createElement('dte:Descripcion', htmlspecialchars($item['descripcion'], ENT_XML1)));
            $itemElement->appendChild($xml->createElement('dte:PrecioUnitario', number_format($precioUnitario, 2, '.', '')));
            $itemElement->appendChild($xml->createElement('dte:Precio', number_format($precioTotal, 2, '.', '')));
            $itemElement->appendChild($xml->createElement('dte:Descuento', number_format($descuento, 2, '.', '')));

            // Impuestos
            $impuestos = $xml->createElement('dte:Impuestos');
            $impuesto = $xml->createElement('dte:Impuesto');
            $impuesto->appendChild($xml->createElement('dte:NombreCorto', 'IVA'));
            $impuesto->appendChild($xml->createElement('dte:CodigoUnidadGravable', '1'));
            $impuesto->appendChild($xml->createElement('dte:MontoGravable', number_format($montoGravable, 2, '.', '')));
            $impuesto->appendChild($xml->createElement('dte:MontoImpuesto', number_format($montoIva, 2, '.', '')));
            $impuestos->appendChild($impuesto);
            $itemElement->appendChild($impuestos);

            $itemElement->appendChild($xml->createElement('dte:Total', number_format($totalConDescuento, 2, '.', '')));

            $itemsElement->appendChild($itemElement);
        }

        $parent->appendChild($itemsElement);
    }

    protected function agregarTotales($xml, $parent, $totales)
    {
        $totalesElement = $xml->createElement('dte:Totales');

        // TotalImpuestos
        $totalImpuestos = $xml->createElement('dte:TotalImpuestos');
        $totalImpuesto = $xml->createElement('dte:TotalImpuesto');
        $totalImpuesto->setAttribute('NombreCorto', 'IVA');
        $totalImpuesto->setAttribute('TotalMontoImpuesto', number_format($totales['iva'], 2, '.', ''));
        $totalImpuestos->appendChild($totalImpuesto);
        $totalesElement->appendChild($totalImpuestos);

        $totalesElement->appendChild($xml->createElement('dte:GranTotal', number_format($totales['total'], 2, '.', '')));

        $parent->appendChild($totalesElement);
    }

    public function generarXmlAnulacion(Facturacion $factura): string
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        // Elemento raíz GTAnulacionDocumento
        $root = $xml->createElementNS('http://www.sat.gob.gt/dte/fel/0.1.0', 'anu:GTAnulacionDocumento');
        $root->setAttribute('xmlns:anu', 'http://www.sat.gob.gt/dte/fel/0.1.0');
        $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttribute('Version', '0.1');
        $xml->appendChild($root);

        $sat = $xml->createElement('anu:SAT');
        $root->appendChild($sat);

        // AnulacionDTE
        $anulacionDte = $xml->createElement('anu:AnulacionDTE');
        $anulacionDte->setAttribute('ID', 'DatosCertificados');
        $sat->appendChild($anulacionDte);

        // DatosGenerales
        $datosGenerales = $xml->createElement('anu:DatosGenerales');
        $datosGenerales->setAttribute('ID', 'DatosAnulacion');
        $datosGenerales->setAttribute('NumeroDocumentoAAnular', $factura->fac_uuid);
        $datosGenerales->setAttribute('NITEmisor', str_replace('-', '', config('fel.emisor.nit')));
        $datosGenerales->setAttribute('IDReceptor', strtoupper(str_replace('-', '', $factura->fac_nit_receptor)));

        $fechaEmision = Carbon::parse($factura->fac_fecha_emision)->format('Y-m-d\TH:i:s.000-06:00');
        $fechaAnulacion = now()->format('Y-m-d\TH:i:s.000-06:00');

        $datosGenerales->setAttribute('FechaEmisionDocumentoAnular', $fechaEmision);
        $datosGenerales->setAttribute('FechaHoraAnulacion', $fechaAnulacion);
        $datosGenerales->setAttribute('MotivoAnulacion', 'Solicitud del emisor');

        $anulacionDte->appendChild($datosGenerales);

        return $xml->saveXML();
    }
}
