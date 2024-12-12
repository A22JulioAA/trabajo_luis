<?php
require('fpdf/fpdf.php');
include('db.php');

function addSection($pdf, $title, $headers, $data)
{
    // Fondo y estilo para el título de la sección
    $pdf->SetFillColor(0, 123, 255); // Fondo azul
    $pdf->SetTextColor(255, 255, 255); // Texto blanco
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(190, 10, $title, 0, 1, 'C', true);
    $pdf->Ln(5);

    // Calcular ancho de las columnas
    $numColumns = count($headers);
    $columnWidth = 190 / $numColumns;

    // Encabezados
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(0, 123, 255); // Fondo azul
    $pdf->SetTextColor(255, 255, 255); // Texto blanco

    foreach ($headers as $header) {
        $pdf->Cell($columnWidth, 10, $header, 1, 0, 'C', true);
    }
    $pdf->Ln();

    // Filas de datos
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(0, 0, 0); // Texto negro

    $rowIndex = 0;
    foreach ($data as $row) {
        $pdf->SetFillColor(240, 240, 240); // Fondo gris claro para filas alternas
        $fill = $rowIndex % 2 === 0; // Alterna colores de fondo

        foreach ($row as $col) {
            // Acorta los textos si son muy largos
            $text = strlen((string)$col) > 20 ? substr((string)$col, 0, 20) . '...' : (string)$col;
            $pdf->Cell($columnWidth, 10, $text, 1, 0, 'C', $fill);
        }
        $pdf->Ln();
        $rowIndex++;
    }
    $pdf->Ln(10);
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Título principal con estilo
$pdf->SetFillColor(0, 123, 255);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(190, 15, 'Informe Agencia Gestion Viajes', 0, 1, 'C', true);
$pdf->Ln(10);

// Fecha y hora de creación con separación visual
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0); // Texto negro
$pdf->SetFillColor(240, 240, 240); // Fondo gris claro
$pdf->Cell(190, 10, 'Fecha de creacion: ' . date('d/m/Y H:i:s'), 0, 1, 'C', true);
$pdf->Ln(10);

// Introducción con margen y estilo
$pdf->SetFont('Arial', '', 12);
$introduccion = "Este informe proporciona un resumen detallado de la gestion de la agencia de viajes. Incluye informacion sobre los viajes disponibles, reservas realizadas, numeros de contacto asociados a clientes, y cancelaciones registradas. El objetivo es ofrecer una vision clara y completa de las operaciones recientes de la agencia.";
$pdf->MultiCell(0, 10, $introduccion, 0, 'J');
$pdf->Ln(10);

// Tablas de datos
$tables = [
    [
        'query' => 'SELECT * FROM cliente',
        'title' => 'Lista de Clientes',
        'headers' => ['ID', 'Nombre', 'Apellido', 'DNI', 'Email'],
    ],
    [
        'query' => 'SELECT * FROM viaje',
        'title' => 'Lista de Viajes',
        'headers' => ['ID', 'Origen', 'Destino', 'Fecha Salida', 'Fecha Llegada', 'Precio', 'Max. Pasajeros']
    ],
    [
        'query' => 'SELECT * FROM reserva',
        'title' => 'Lista de Reservas',
        'headers' => ['ID', 'ID Cliente', 'ID Viaje', 'Fecha Reserva', 'Cantidad Pagada', 'Num. Plazas Reservadas', 'Cancelada']
    ],
    [
        'query' => 'SELECT * FROM telefono',
        'title' => 'Lista de Telefonos',
        'headers' => ['ID', 'Número']
    ],
    [
        'query' => 'SELECT * FROM cancelacion',
        'title' => 'Lista de Cancelaciones',
        'headers' => ['ID', 'Fecha Cancelacion', 'Penalización', 'ID Reserva']
    ]
];

foreach ($tables as $table) {
    $query = $pdo->query($table['query']);
    $data = $query->fetchAll(PDO::FETCH_ASSOC);
    addSection($pdf, $table['title'], $table['headers'], $data);
}

$pdf->Output('I', 'Reportes.pdf');
