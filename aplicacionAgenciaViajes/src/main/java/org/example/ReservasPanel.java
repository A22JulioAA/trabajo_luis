package org.example;

import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.math.BigDecimal;
import java.sql.*;

public class ReservasPanel extends JPanel {
    private JTable table;
    private DefaultTableModel tableModel;
    private Connection connection;

    public ReservasPanel() {
        setLayout(new BorderLayout());

        // Configurar tabla
        tableModel = new DefaultTableModel(
                new String[]{"ID", "ID Cliente", "ID Viaje", "Fecha Reserva", "Cantidad Pagada", "Num. Plazas", "Cancelada"}, 0);
        table = new JTable(tableModel);
        add(new JScrollPane(table), BorderLayout.CENTER);

        // Panel de botones
        JPanel buttonPanel = new JPanel();
        JButton addButton = new JButton("Añadir");
        JButton editButton = new JButton("Modificar");
        JButton deleteButton = new JButton("Eliminar");
        buttonPanel.add(addButton);
        buttonPanel.add(editButton);
        buttonPanel.add(deleteButton);
        add(buttonPanel, BorderLayout.SOUTH);

        // Conectar con la base de datos
        connectToDatabase();
        loadData();

        // Listeners de botones
        addButton.addActionListener(e -> addReserva());
        editButton.addActionListener(e -> editReserva());
        deleteButton.addActionListener(e -> deleteReserva());
    }

    private void connectToDatabase() {
        try {
            connection = DriverManager.getConnection(
                    "jdbc:postgresql://localhost:5432/agencia_viajes",
                    "postgres",
                    "abc123."
            );
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Error al conectar con la base de datos", "Error", JOptionPane.ERROR_MESSAGE);
            e.printStackTrace();
        }
    }

    private void loadData() {
        try (Statement stmt = connection.createStatement()) {
            ResultSet rs = stmt.executeQuery("SELECT * FROM RESERVA");
            while (rs.next()) {
                tableModel.addRow(new Object[]{
                        rs.getInt("id_reserva"),
                        rs.getInt("id_cliente"),
                        rs.getInt("id_viaje"),
                        rs.getTimestamp("fecha_reserva"),
                        rs.getBigDecimal("cantidad_pagada"),
                        rs.getInt("num_plazas_reservadas"),
                        rs.getBoolean("cancelada")
                });
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    private void addReserva() {
        try {
            int idCliente = Integer.parseInt(JOptionPane.showInputDialog(this, "ID Cliente:"));
            int idViaje = Integer.parseInt(JOptionPane.showInputDialog(this, "ID Viaje:"));
            String fechaReserva = JOptionPane.showInputDialog(this, "Fecha de reserva (YYYY-MM-DD HH:MM:SS):");
            String cantidadPagadaStr = JOptionPane.showInputDialog(this, "Cantidad Pagada:");
            String numPlazasStr = JOptionPane.showInputDialog(this, "Número de plazas reservadas:");

            BigDecimal cantidadPagada = new BigDecimal(cantidadPagadaStr);
            int numPlazas = Integer.parseInt(numPlazasStr);

            try (PreparedStatement stmt = connection.prepareStatement(
                    "INSERT INTO RESERVA (id_cliente, id_viaje, fecha_reserva, cantidad_pagada, num_plazas_reservadas) " +
                            "VALUES (?, ?, ?, ?, ?)", Statement.RETURN_GENERATED_KEYS)) {
                stmt.setInt(1, idCliente);
                stmt.setInt(2, idViaje);
                stmt.setTimestamp(3, Timestamp.valueOf(fechaReserva));
                stmt.setBigDecimal(4, cantidadPagada);
                stmt.setInt(5, numPlazas);
                stmt.executeUpdate();

                ResultSet keys = stmt.getGeneratedKeys();
                if (keys.next()) {
                    tableModel.addRow(new Object[]{
                            keys.getInt(1), idCliente, idViaje, Timestamp.valueOf(fechaReserva), cantidadPagada, numPlazas, false
                    });
                }
            }
        } catch (Exception e) {
            JOptionPane.showMessageDialog(this, "Error al añadir la reserva", "Error", JOptionPane.ERROR_MESSAGE);
            e.printStackTrace();
        }
    }

    private void editReserva() {
        int selectedRow = table.getSelectedRow();
        if (selectedRow == -1) {
            JOptionPane.showMessageDialog(this, "Selecciona una reserva para modificar", "Error", JOptionPane.WARNING_MESSAGE);
            return;
        }

        try {
            int idReserva = (int) tableModel.getValueAt(selectedRow, 0);
            int idCliente = Integer.parseInt(JOptionPane.showInputDialog(this, "Nuevo ID Cliente:", tableModel.getValueAt(selectedRow, 1)));
            int idViaje = Integer.parseInt(JOptionPane.showInputDialog(this, "Nuevo ID Viaje:", tableModel.getValueAt(selectedRow, 2)));
            String fechaReserva = JOptionPane.showInputDialog(this, "Nueva Fecha de Reserva (YYYY-MM-DD HH:MM:SS):", tableModel.getValueAt(selectedRow, 3));
            String cantidadPagadaStr = JOptionPane.showInputDialog(this, "Nueva Cantidad Pagada:", tableModel.getValueAt(selectedRow, 4));
            String numPlazasStr = JOptionPane.showInputDialog(this, "Nuevas Num. Plazas Reservadas:", tableModel.getValueAt(selectedRow, 5));

            BigDecimal cantidadPagada = new BigDecimal(cantidadPagadaStr);
            int numPlazas = Integer.parseInt(numPlazasStr);

            try (PreparedStatement stmt = connection.prepareStatement(
                    "UPDATE RESERVA SET id_cliente = ?, id_viaje = ?, fecha_reserva = ?, cantidad_pagada = ?, num_plazas_reservadas = ? WHERE id_reserva = ?")) {
                stmt.setInt(1, idCliente);
                stmt.setInt(2, idViaje);
                stmt.setTimestamp(3, Timestamp.valueOf(fechaReserva));
                stmt.setBigDecimal(4, cantidadPagada);
                stmt.setInt(5, numPlazas);
                stmt.setInt(6, idReserva);
                stmt.executeUpdate();

                tableModel.setValueAt(idCliente, selectedRow, 1);
                tableModel.setValueAt(idViaje, selectedRow, 2);
                tableModel.setValueAt(Timestamp.valueOf(fechaReserva), selectedRow, 3);
                tableModel.setValueAt(cantidadPagada, selectedRow, 4);
                tableModel.setValueAt(numPlazas, selectedRow, 5);
            }
        } catch (Exception e) {
            JOptionPane.showMessageDialog(this, "Error al modificar la reserva", "Error", JOptionPane.ERROR_MESSAGE);
            e.printStackTrace();
        }
    }

    private void deleteReserva() {
        int selectedRow = table.getSelectedRow();
        if (selectedRow == -1) {
            JOptionPane.showMessageDialog(this, "Selecciona una reserva para eliminar", "Error", JOptionPane.WARNING_MESSAGE);
            return;
        }

        int idReserva = (int) tableModel.getValueAt(selectedRow, 0);
        try (PreparedStatement stmt = connection.prepareStatement(
                "DELETE FROM RESERVA WHERE id_reserva = ?")) {
            stmt.setInt(1, idReserva);
            stmt.executeUpdate();
            tableModel.removeRow(selectedRow);
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Error al eliminar la reserva", "Error", JOptionPane.ERROR_MESSAGE);
            e.printStackTrace();
        }
    }
}
