<?php
// libs/pdf_factura.php
require_once __DIR__ . '/fpdf.php';

class PDFFacturaGenerator {
    /**
     * generate($data, $outputPath)
     * $data = [ 'empresa' => [...], 'factura' => [...], 'items' => [...] ]
     */
    public function generate($data, $outputPath)
    {
        // Cambia tamaño aquí:
        // Por defecto A4:
        $pdf = new FPDF('P', 'mm', 'A4');
        // Para ticket térmico ancho 80mm, usar:
        // $pdf = new FPDF('P', 'mm', array(80, 220));
        // Para papel carta usa 'Letter': new FPDF('P','mm','Letter')

        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 10);

        $empresa = $data['empresa'] ?? [];
        $factura = $data['factura'] ?? [];
        $items = $data['items'] ?? [];

        // Logo
        if (!empty($empresa['ce_logo'])) {
            $logo_path = __DIR__ . '/../' . ltrim($empresa['ce_logo'], '/');
            if (file_exists($logo_path)) {
                $pdf->Image($logo_path, 10, 8, 30);
            }
        }

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 6, $empresa['ce_nombre'] ?? 'Nombre Empresa', 0, 1, 'R');
        $pdf->SetFont('Arial', '', 9);
        if (!empty($empresa['ce_nit'])) $pdf->Cell(0, 5, 'NIT: ' . $empresa['ce_nit'], 0, 1, 'R');
        if (!empty($empresa['ce_direccion'])) $pdf->Cell(0, 5, $empresa['ce_direccion'], 0, 1, 'R');
        if (!empty($empresa['ce_telefono'])) $pdf->Cell(0, 5, 'Tel: ' . $empresa['ce_telefono'], 0, 1, 'R');

        $pdf->Ln(6);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 6, strtoupper($factura['ve_tipo_documento'] ?? 'NOTA DE VENTA'), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 5, 'Nº: ' . ($factura['fa_numero'] ?? ''), 0, 1, 'L');
        $pdf->Cell(0, 5, 'Fecha: ' . ($factura['fa_fecha_emision'] ?? ''), 0, 1, 'L');
        $pdf->Cell(0, 5, 'Cliente: ' . (($factura['cl_nombres'] ?? '') . ' ' . ($factura['cl_apellido_paterno'] ?? '')), 0, 1, 'L');
        $pdf->Ln(4);

        // Tabla items
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(10, 7, '#', 1, 0, 'C');
        $pdf->Cell(90, 7, 'Producto', 1, 0, 'L');
        $pdf->Cell(20, 7, 'Cant', 1, 0, 'C');
        $pdf->Cell(30, 7, 'P.Unit', 1, 0, 'R');
        $pdf->Cell(30, 7, 'Subtotal', 1, 1, 'R');

        $pdf->SetFont('Arial', '', 9);
        $i = 1;
        foreach ($items as $it) {
            $pdf->Cell(10, 6, $i++, 1, 0, 'C');
            $pdf->Cell(90, 6, substr($it['med_nombre_quimico'] ?? 'Producto', 0, 50), 1, 0, 'L');
            $pdf->Cell(20, 6, $it['dv_cantidad'], 1, 0, 'C');
            $pdf->Cell(30, 6, number_format($it['dv_precio_unitario'], 2), 1, 0, 'R');
            $pdf->Cell(30, 6, number_format($it['dv_subtotal'], 2), 1, 1, 'R');
        }

        $pdf->Ln(4);
        $pdf->SetFont('Arial', 'B', 10);
        $total_monto = $factura['fa_monto_total'] ?? ($factura['ve_total'] ?? 0);
        $pdf->Cell(150, 7, 'TOTAL', 0, 0, 'R');
        $pdf->Cell(30, 7, number_format($total_monto, 2), 0, 1, 'R');

        $pdf->Ln(8);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 5, 'Gracias por su compra.', 0, 1, 'C');

        $pdf->Output('F', $outputPath);
    }
}
