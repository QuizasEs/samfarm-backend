<?php
/**
 * Configuración centralizada de tamaños y estilos de PDF
 */

return [
    'tamaños' => [
        'nota_venta' => [
            'orientacion' => 'P',
            'unidad' => 'mm',
            'formato' => [140, 216], // Media carta
            'margenes' => [10, 10, 10] // izq, arriba, derecha
        ],
        'factura' => [
            'orientacion' => 'P',
            'unidad' => 'mm',
            'formato' => 'A4',
            'margenes' => [15, 15, 15]
        ],
        'orden_compra' => [
            'orientacion' => 'P',
            'unidad' => 'mm',
            'formato' => 'Letter', // Carta
            'margenes' => [20, 20, 20]
        ],
        'cierre_caja' => [
            'orientacion' => 'P',
            'unidad' => 'mm',
            'formato' => [140, 216],
            'margenes' => [10, 10, 10]
        ],
        'reporte_inventario' => [
            'orientacion' => 'L', // Landscape
            'unidad' => 'mm',
            'formato' => 'Letter',
            'margenes' => [15, 15, 15]
        ],
        'informe_general' => [
            'orientacion' => 'P',
            'unidad' => 'mm',
            'formato' => 'Letter',
            'margenes' => [20, 20, 20]
        ]
    ],
    
    'empresa' => [
        'logo_path' => '../storage/',
        'nombre_default' => 'SAMFARM',
        'nit_default' => 'S/N'
    ],
    
    'fuentes' => [
        'titulo' => ['Arial', 'B', 16],
        'subtitulo' => ['Arial', 'B', 12],
        'normal' => ['Arial', '', 10],
        'pequeño' => ['Arial', '', 8]
    ]
];