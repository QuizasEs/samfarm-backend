<?php
/**
 * Clase para leer archivos Excel (.xlsx) sin necesidad de PHPExcel
 * Utiliza ZipArchive para leer el formato OpenXML
 */
class ExcelReader
{
    private $filePath;
    private $sharedStrings = [];
    private $sheetData = [];
    
    /**
     * Constructor
     * @param string $filePath Ruta al archivo Excel
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }
    
    /**
     * Leer el archivo Excel y retornar los datos
     * @param int $sheetIndex Índice de la hoja (0 = primera)
     * @return array Datos de la hoja
     */
    public function read($sheetIndex = 0)
    {
        if (!file_exists($this->filePath)) {
            throw new Exception("El archivo no existe: " . $this->filePath);
        }
        
        $zip = new ZipArchive;
        if ($zip->open($this->filePath) !== true) {
            throw new Exception("No se pudo abrir el archivo Excel");
        }
        
        // Leer sharedStrings.xml para obtener textos compartidos
        $this->sharedStrings = $this->readSharedStrings($zip);
        
        // Leer la hoja de cálculo
        $sheetName = sprintf('xl/worksheets/sheet%d.xml', $sheetIndex + 1);
        $this->sheetData = $this->readSheet($zip, $sheetName);
        
        $zip->close();
        
        return $this->sheetData;
    }
    
    /**
     * Leer cadenas compartidas
     */
    private function readSharedStrings($zip)
    {
        $strings = [];
        
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml === false) {
            return $strings;
        }
        
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($sharedStringsXml);
        
        if ($xml && isset($xml->si)) {
            foreach ($xml->si as $si) {
                $text = '';
                if (isset($si->t)) {
                    $text = (string)$si->t;
                } elseif (isset($si->r)) {
                    // Texto con formato parcial
                    foreach ($si->r as $r) {
                        $text .= (string)$r->t;
                    }
                }
                $strings[] = $text;
            }
        }
        
        return $strings;
    }
    
    /**
     * Leer una hoja de cálculo
     */
    private function readSheet($zip, $sheetName)
    {
        $sheetXml = $zip->getFromName($sheetName);
        if ($sheetXml === false) {
            throw new Exception("No se encontró la hoja de cálculo: " . $sheetName);
        }
        
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($sheetXml);
        
        if (!$xml || !isset($xml->sheetData)) {
            return [];
        }
        
        $data = [];
        $rowIndex = 0;
        
        foreach ($xml->sheetData->row as $row) {
            $rowData = [];
            $colIndex = 0;
            
            foreach ($row->c as $cell) {
                $cellValue = $this->getCellValue($cell);
                
                // Calcular el índice de columna
                $ref = (string)$cell['r'];
                preg_match('/^([A-Z]+)(\d+)$/', $ref, $matches);
                if ($matches) {
                    $colLetter = $matches[1];
                    $colIndex = $this->columnLetterToIndex($colLetter);
                }
                
                $rowData[$colIndex] = $cellValue;
                $colIndex++;
            }
            
            $data[$rowIndex] = $rowData;
            $rowIndex++;
        }
        
        return $data;
    }
    
    /**
     * Obtener el valor de una celda
     */
    private function getCellValue($cell)
    {
        $type = isset($cell['t']) ? (string)$cell['t'] : '';
        $value = isset($cell->v) ? (string)$cell->v : '';
        
        if ($type === 's') {
            // Shared string
            $index = (int)$value;
            return isset($this->sharedStrings[$index]) ? $this->sharedStrings[$index] : '';
        } elseif ($type === 'b') {
            // Boolean
            return $value === '1' ? true : false;
        } elseif ($type === 'e') {
            // Error
            return $value;
        } elseif (empty($type)) {
            // Número o fórmula
            return $value;
        }
        
        return $value;
    }
    
    /**
     * Convertir letra de columna a índice numérico
     * A = 0, B = 1, ..., Z = 25, AA = 26, ...
     */
    private function columnLetterToIndex($letter)
    {
        $index = 0;
        $length = strlen($letter);
        
        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($letter[$i]) - ord('A') + 1);
        }
        
        return $index - 1;
    }
    
    /**
     * Convertir datos de matriz asociativa a array indexado numéricamente
     * @return array
     */
    public function toArray()
    {
        $result = [];
        
        foreach ($this->sheetData as $row) {
            $result[] = array_values($row);
        }
        
        return $result;
    }
    
    /**
     * Obtener los encabezados de la primera fila
     * @return array
     */
    public function getHeaders()
    {
        if (empty($this->sheetData)) {
            return [];
        }
        
        $firstRow = reset($this->sheetData);
        return array_values($firstRow);
    }
    
    /**
     * Obtener datos con encabezados como claves asociativas
     * @return array
     */
    public function toAssociativeArray()
    {
        if (empty($this->sheetData)) {
            return [];
        }
        
        $headers = array_values(reset($this->sheetData));
        $result = [];
        
        $rows = array_slice($this->sheetData, 1);
        foreach ($rows as $row) {
            $rowData = array_values($row);
            $assocRow = [];
            
            foreach ($headers as $index => $header) {
                $assocRow[$header] = isset($rowData[$index]) ? $rowData[$index] : '';
            }
            
            $result[] = $assocRow;
        }
        
        return $result;
    }
    
    /**
     * Convertir número de Excel (fecha serial) a fecha PHP
     * @param float $excelDate Número de fecha de Excel
     * @return string|null
     */
    public static function excelDateToPHP($excelDate)
    {
        if (!is_numeric($excelDate)) {
            return null;
        }
        
        // Las fechas de Excel comienzan el 1 de enero de 1900
        $excelDate = (float)$excelDate;
        
        // Excel tiene un error: considera 1900 como año bisiesto
        // Entonces restamos 1 día para fechas posteriores al 28/02/1900
        if ($excelDate > 60) {
            $excelDate -= 1;
        }
        
        $timestamp = ($excelDate - 25569) * 86400;
        return date('Y-m-d', $timestamp);
    }
}
