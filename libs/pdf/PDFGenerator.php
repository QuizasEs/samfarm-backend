<?php
require_once dirname(__DIR__) . '/fpdf/fpdf.php';
require_once "./models/mainModel.php";

$ins_main = new mainModel();
/**
 * Generador unificado de PDFs para SAMFARM
 * Genera PDFs en memoria sin guardar en disco
 */
class PDFGenerator extends FPDF
{
    protected $config;
    protected $empresa_data;
    protected $tipo_documento;
    
    public function __construct($tipo_documento = 'nota_venta')
    {
        $this->config = require __DIR__ . '/config/pdf_config.php';
        $this->tipo_documento = $tipo_documento;
        
        // Cargar configuración de empresa
        $this->cargarDatosEmpresa();
        
        // Obtener configuración de tamaño
        $size_config = $this->config['tamaños'][$tipo_documento] ?? $this->config['tamaños']['nota_venta'];
        
        parent::__construct(
            $size_config['orientacion'],
            $size_config['unidad'],
            $size_config['formato']
        );
        
        // Aplicar márgenes
        $this->SetMargins(
            $size_config['margenes'][0],
            $size_config['margenes'][1],
            $size_config['margenes'][2]
        );
        
        $this->SetAutoPageBreak(true, 15);
    }
    
    /**
     * Cargar datos de empresa desde BD
     */
    protected function cargarDatosEmpresa()
    {
        try {
            require_once dirname(dirname(__DIR__)) . '/models/mainModel.php';
            $db = $ins_main::conectar();
            $stmt = $db->query("SELECT * FROM configuracion_empresa ORDER BY ce_id DESC LIMIT 1");
            $this->empresa_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$this->empresa_data) {
                $this->empresa_data = [
                    'ce_nombre' => $this->config['empresa']['nombre_default'],
                    'ce_nit' => $this->config['empresa']['nit_default'],
                    'ce_direccion' => '',
                    'ce_telefono' => '',
                    'ce_correo' => '',
                    'ce_logo' => null
                ];
            }
        } catch (Exception $e) {
            error_log("Error cargando datos empresa: " . $e->getMessage());
            $this->empresa_data = [];
        }
    }
    
    /**
     * Generar encabezado con logo y datos de empresa
     */
    public function generarEncabezado()
    {
        $logo_x = $this->GetX();
        $logo_y = $this->GetY();
        
        // Logo si existe
        if (!empty($this->empresa_data['ce_logo'])) {
            $logo_path = $this->config['empresa']['logo_path'] . $this->empresa_data['ce_logo'];
            if (file_exists($logo_path)) {
                $this->Image($logo_path, $logo_x, $logo_y, 30);
            }
        }
        
        // Datos empresa (derecha)
        $this->SetFont(...$this->config['fuentes']['subtitulo']);
        $this->SetXY($logo_x + 100, $logo_y);
        $this->Cell(0, 6, $this->utf8_decode($this->empresa_data['ce_nombre']), 0, 1, 'R');
        
        $this->SetFont(...$this->config['fuentes']['pequeño']);
        $this->Cell(0, 5, "NIT: " . $this->empresa_data['ce_nit'], 0, 1, 'R');
        
        if (!empty($this->empresa_data['ce_direccion'])) {
            $this->Cell(0, 5, $this->utf8_decode($this->empresa_data['ce_direccion']), 0, 1, 'R');
        }
        
        $this->Ln(5);
        $this->Line($this->GetX(), $this->GetY(), $this->GetPageWidth() - 10, $this->GetY());
        $this->Ln(3);
    }
    
    /**
     * Helper para decodificar UTF-8
     */
    protected function utf8_decode($str)
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str);
    }
    
    /**
     * Generar y enviar PDF al navegador (sin guardar en disco)
     * @param string $nombre_archivo - Nombre del archivo a mostrar
     * @param string $modo - 'I' inline, 'D' download
     */
    public function enviar($nombre_archivo = 'documento.pdf', $modo = 'I')
    {
        $this->Output($modo, $nombre_archivo);
    }
    
    /**
     * Obtener contenido PDF como string (para enviar por email, etc)
     */
    public function obtenerContenido()
    {
        return $this->Output('S');
    }
}