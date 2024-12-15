package org.example;

import javax.swing.*;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.io.FileOutputStream;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.Statement;

import com.itextpdf.kernel.colors.Color;
import com.itextpdf.kernel.colors.DeviceRgb;
import com.itextpdf.kernel.colors.ColorConstants;
import com.itextpdf.kernel.pdf.PdfDocument;
import com.itextpdf.kernel.pdf.PdfWriter;
import com.itextpdf.kernel.pdf.canvas.draw.SolidLine;
import com.itextpdf.layout.Document;
import com.itextpdf.layout.element.Paragraph;
import com.itextpdf.layout.element.Table;
import com.itextpdf.layout.element.Cell;
import com.itextpdf.layout.element.LineSeparator;

public class InformesPanel extends JPanel {
    public InformesPanel() {
        setLayout(null);

        JLabel lblDescripcion = new JLabel("Genera un informe completo sobre la gestión de la agencia.\n" +
                "Incluye datos sobre clientes, teléfonos, viajes, reservas y cancelaciones.");
        lblDescripcion.setBounds(20, 20, 500, 40);
        add(lblDescripcion);

        JButton btnGenerarInforme = new JButton("Generar Informe PDF");
        btnGenerarInforme.setBounds(20, 80, 200, 30);
        add(btnGenerarInforme);

        btnGenerarInforme.addActionListener(new ActionListener() {
            @Override
            public void actionPerformed(ActionEvent e) {
                generarInformePDF();
            }
        });
    }

    private void generarInformePDF() {
        try {
            Connection connection = DriverManager.getConnection(
                    "jdbc:postgresql://localhost:5432/agencia_viajes",
                    "postgres",
                    "abc123."
            );

            JFileChooser fileChooser = new JFileChooser();
            fileChooser.setDialogTitle("Seleccionar ubicación para guardar el informe");
            fileChooser.setSelectedFile(new java.io.File("InformeAgencia.pdf"));

            int result = fileChooser.showSaveDialog(this);
            if (result != JFileChooser.APPROVE_OPTION) {
                JOptionPane.showMessageDialog(this, "No se seleccionó un archivo para guardar.");
                return;
            }

            String destino = fileChooser.getSelectedFile().getAbsolutePath();

            PdfWriter writer = new PdfWriter(destino);
            PdfDocument pdfDoc = new PdfDocument(writer);
            Document document = new Document(pdfDoc);

            document.add(new Paragraph("Informe Completo de la Agencia de Viajes")
                    .setFontSize(18));
            document.add(new Paragraph("Fecha: " + new java.util.Date()).setFontSize(12));
            document.add(new Paragraph("\n"));

            String descripcion = "Genera un informe completo sobre la gestión de la agencia.\n" +
                    "Incluye datos sobre clientes, teléfonos, viajes, reservas y cancelaciones.";

            document.add(new Paragraph("Descripción del informe:"));
            document.add(new Paragraph(descripcion).setFontSize(12));

            SolidLine solidLine = new SolidLine();
            document.add(new LineSeparator(solidLine));

            document.add(new Paragraph("\n"));

            generarSeccionPDF(document, connection, "Clientes", "SELECT id_cliente, nombre, apellidos, dni, correo_electronico FROM CLIENTE",
                    new String[]{"id_cliente", "nombre", "apellidos", "dni", "correo_electronico"},
                    new String[]{"ID Cliente", "Nombre Completo", "Apellidos", "DNI", "Correo Electrónico"});

            generarSeccionPDF(document, connection, "Teléfonos", "SELECT id_cliente, telefono FROM TELEFONO",
                    new String[]{"id_cliente", "telefono"},
                    new String[]{"ID Cliente", "Teléfono"});

            generarSeccionPDF(document, connection, "Viajes", "SELECT id_viaje, origen, destino, fecha_salida, fecha_llegada, precio, max_plazas FROM VIAJE",
                    new String[]{"id_viaje", "origen", "destino", "fecha_salida", "fecha_llegada", "precio", "max_plazas"},
                    new String[]{"ID Viaje", "Origen", "Destino", "Fecha Salida", "Fecha Llegada", "Precio", "Max. Plazas"});

            generarSeccionPDF(document, connection, "Reservas", "SELECT id_reserva, id_cliente, id_viaje, fecha_reserva, cantidad_pagada, num_plazas_reservadas, cancelada FROM RESERVA",
                    new String[]{"id_reserva", "id_cliente", "id_viaje", "fecha_reserva", "cantidad_pagada", "num_plazas_reservadas", "cancelada"},
                    new String[]{"ID Reserva", "ID Cliente", "ID Viaje", "Fecha Reserva", "Cantidad Pagada", "Plazas Reservadas", "Cancelada"});

            generarSeccionPDF(document, connection, "Cancelaciones", "SELECT id_cancelacion, fecha_cancelacion, penalizacion, id_reserva FROM CANCELACION",
                    new String[]{"id_cancelacion", "fecha_cancelacion", "penalizacion", "id_reserva"},
                    new String[]{"ID Cancelación", "Fecha Cancelación", "Penalización", "ID Reserva"});

            document.close();

            JOptionPane.showMessageDialog(this, "Informe generado con éxito: " + destino);
        } catch (Exception ex) {
            JOptionPane.showMessageDialog(this, "Error al generar el informe: " + ex.getMessage(), "Error", JOptionPane.ERROR_MESSAGE);
            ex.printStackTrace();
        }
    }

    private void generarSeccionPDF(Document document, Connection connection, String titulo, String query, String[] columnas, String[] encabezados) throws Exception {
        document.add(new Paragraph(titulo).setFontSize(14));
        document.add(new Paragraph("\n"));

        Table table = new Table(columnas.length);

        Color azulClaro = new DeviceRgb(0, 123, 255);
        Color grisOscuro = new DeviceRgb(169, 169, 169);
        Color grisClaro = new DeviceRgb(211, 211, 211);
        Color blanco = ColorConstants.WHITE;

        for (String encabezado : encabezados) {
            Cell cell = new Cell().add(new Paragraph(encabezado));
            cell.setBackgroundColor(azulClaro)
                    .setFontColor(blanco);
            table.addHeaderCell(cell);
        }

        Statement stmt = connection.createStatement();
        ResultSet rs = stmt.executeQuery(query);
        int rowIndex = 0;

        while (rs.next()) {
            for (String columna : columnas) {
                String valor = rs.getString(columna) != null ? rs.getString(columna) : "N/A";
                Color fondoFila = (rowIndex % 2 == 0) ? blanco : grisClaro;
                Cell cell = new Cell().add(new Paragraph(valor));
                cell.setBackgroundColor(fondoFila);
                table.addCell(cell);
            }
            rowIndex++;
        }

        document.add(table);
        document.add(new Paragraph("\n"));

        SolidLine solidLine = new SolidLine();
        document.add(new LineSeparator(solidLine));
        document.add(new Paragraph("\n"));
    }
}
