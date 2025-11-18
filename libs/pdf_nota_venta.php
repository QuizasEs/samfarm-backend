<?php


require_once __DIR__ . '/facturas/fpdf.php'; // si usas distribución clásica FPDF

class NotaVentaPDF {
    protected $pdf;
    public function __construct(){
        $this->pdf = new FPDF('P','mm','A4');
        $this->pdf->SetAutoPageBreak(true,10);
    }

    /**
     * Genera y envía PDF al navegador (o lo guarda en servidor)
     * $config should contain:
     * - empresa: ['ce_nombre','ce_nit','ce_direccion','ce_telefono','ce_correo','ce_logo']
     * - venta: ['ve_numero_documento','ve_fecha','items'=>[], 'subtotal','total','metodo_pago']
     * - usuario: nombre
     */
    public function render($config, $outputMode = 'I', $filename = 'nota_venta.pdf') {
        $empresa = $config['empresa'] ?? [];
        $venta = $config['venta'] ?? [];

        $this->pdf->AddPage();
        // Header empresa
        if (!empty($empresa['ce_logo']) && file_exists($empresa['ce_logo'])) {
            $this->pdf->Image($empresa['ce_logo'],10,8,30);
        }
        $this->pdf->SetFont('Arial','B',12);
        $this->pdf->Cell(0,6, $empresa['ce_nombre'] ?? 'Nombre Empresa',0,1,'R');
        $this->pdf->SetFont('Arial','',9);
        $this->pdf->Cell(0,5, 'NIT: '.($empresa['ce_nit'] ?? ''),0,1,'R');
        $this->pdf->Cell(0,5, $empresa['ce_direccion'] ?? '',0,1,'R');
        $this->pdf->Ln(8);

        // Título y datos venta
        $this->pdf->SetFont('Arial','B',11);
        $this->pdf->Cell(0,6, 'NOTA DE VENTA',0,1,'C');
        $this->pdf->SetFont('Arial','',9);
        $this->pdf->Cell(0,5, 'Nº: '.($venta['ve_numero_documento'] ?? ''),0,1,'L');
        $this->pdf->Cell(0,5, 'Fecha: '.($venta['ve_fecha'] ?? date('Y-m-d H:i:s')),0,1,'L');
        $this->pdf->Cell(0,5, 'Usuario: '.($venta['usuario'] ?? ''),0,1,'L');
        $this->pdf->Ln(6);

        // Tabla items
        $this->pdf->SetFont('Arial','B',9);
        $this->pdf->Cell(10,7,'#',1,0,'C');
        $this->pdf->Cell(90,7,'Producto',1,0,'L');
        $this->pdf->Cell(20,7,'Cant',1,0,'C');
        $this->pdf->Cell(30,7,'P.Unit',1,0,'R');
        $this->pdf->Cell(30,7,'Subtotal',1,1,'R');
        $this->pdf->SetFont('Arial','',9);

        $i=1;
        foreach($venta['items'] as $it){
            $this->pdf->Cell(10,6,$i++,1,0,'C');
            $this->pdf->Cell(90,6,substr($it['nombre'] ?? 'Producto',0,50),1,0,'L');
            $this->pdf->Cell(20,6,$it['cantidad'],1,0,'C');
            $this->pdf->Cell(30,6,number_format($it['precio'],2),1,0,'R');
            $this->pdf->Cell(30,6,number_format($it['subtotal'],2),1,1,'R');
        }

        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial','B',10);
        $this->pdf->Cell(150,7,'SUBTOTAL',0,0,'R');
        $this->pdf->Cell(30,7,number_format($venta['subtotal'] ?? 0,2),0,1,'R');
        $this->pdf->Cell(150,7,'TOTAL',0,0,'R');
        $this->pdf->Cell(30,7,number_format($venta['total'] ?? 0,2),0,1,'R');

        // Output
        $this->pdf->Output($outputMode, $filename);
    }
}
