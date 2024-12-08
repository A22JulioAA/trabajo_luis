package org.example;

import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.math.BigDecimal;
import java.sql.*;

public class CancelacionesPanel extends JPanel {
    private JTable table;
    private DefaultTableModel tableModel;
    private Connection connection;

    public CancelacionesPanel() {
        setLayout(new BorderLayout());

        // Configurar tabla
        tableModel = new DefaultTableModel(
                new String[]{"ID Cancelación", "Fecha Cancelación", "Penalización", "ID Reserva"}, 0);
        table = new JTable(tableModel);
        add(new JScrollPane(table), BorderLayout.CENTER);

        // Panel de botones
        JPanel buttonPanel = new JPanel();
        JButton addButton = new JButton("Añadir");
        JButton deleteButton = new JButton("Eliminar");
        buttonPanel.add(addButton);
        buttonPanel.add(deleteButton);
        add(buttonPanel, BorderLayout.SOUTH);

        // Conectar con la base de datos
        connectToDatabase();
        loadData();

        // Listeners de botones
        addButton.addActionListener(e -> addCancelacion());
        deleteButton.addActionListener(e -> deleteCancelacion());
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
            ResultSet rs = stmt.executeQuery("SELECT * FROM CANCELACION");
            while (rs.next()) {
                tableModel.addRow(new Object[]{
                        rs.getInt("id_cancelacion"),
                        rs.getDate("fecha_cancelacion"),
                        rs.getBigDecimal("penalizacion"),
                        rs.getInt("id_reserva")
                });
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    private void addCancelacion() {
        try {
            int idReserva = Integer.parseInt(JOptionPane.showInputDialog(this, "ID Reserva:"));
            String fechaCancelacion = JOptionPane.showInputDialog(this, "Fecha de Cancelación (YYYY-MM-DD):");
            String penalizacionStr = JOptionPane.showInputDialog(this, "Penalización (opcional, dejar en blanco si no aplica):");

            BigDecimal penalizacion = null;
            if (penalizacionStr != null && !penalizacionStr.trim().isEmpty()) {
                penalizacion = new BigDecimal(penalizacionStr);
            }

            try (PreparedStatement stmt = connection.prepareStatement(
                    "INSERT INTO CANCELACION (fecha_cancelacion, penalizacion, id_reserva) VALUES (?, ?, ?)",
                    Statement.RETURN_GENERATED_KEYS)) {
                stmt.setDate(1, Date.valueOf(fechaCancelacion));
                if (penalizacion != null) {
                    stmt.setBigDecimal(2, penalizacion);
                } else {
                    stmt.setNull(2, Types.NUMERIC);
                }
                stmt.setInt(3, idReserva);
                stmt.executeUpdate();

                ResultSet keys = stmt.getGeneratedKeys();
                if (keys.next()) {
                    tableModel.addRow(new Object[]{
                            keys.getInt(1), Date.valueOf(fechaCancelacion), penalizacion, idReserva
                    });
                }
            }
        } catch (Exception e) {
            JOptionPane.showMessageDialog(this, "Error al añadir la cancelación", "Error", JOptionPane.ERROR_MESSAGE);
            e.printStackTrace();
        }
    }

    private void deleteCancelacion() {
        int selectedRow = table.getSelectedRow();
        if (selectedRow == -1) {
            JOptionPane.showMessageDialog(this, "Selecciona una cancelación para eliminar", "Error", JOptionPane.WARNING_MESSAGE);
            return;
        }

        int idCancelacion = (int) tableModel.getValueAt(selectedRow, 0);

        try (PreparedStatement stmt = connection.prepareStatement(
                "DELETE FROM CANCELACION WHERE id_cancelacion = ?")) {
            stmt.setInt(1, idCancelacion);
            stmt.executeUpdate();
            tableModel.removeRow(selectedRow);
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Error al eliminar la cancelación", "Error", JOptionPane.ERROR_MESSAGE);
            e.printStackTrace();
        }
    }
}
