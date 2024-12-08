package org.example;

import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.sql.*;

public class TelefonosPanel extends JPanel {
    private JTable table;
    private DefaultTableModel tableModel;
    private Connection connection;

    public TelefonosPanel() {
        setLayout(new BorderLayout());

        // Configurar tabla
        tableModel = new DefaultTableModel(new String[]{"ID Cliente", "Teléfono"}, 0);
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
        addButton.addActionListener(e -> addTelefono());
        editButton.addActionListener(e -> editTelefono());
        deleteButton.addActionListener(e -> deleteTelefono());
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
            ResultSet rs = stmt.executeQuery("SELECT * FROM TELEFONO");
            while (rs.next()) {
                tableModel.addRow(new Object[]{
                        rs.getInt("id_cliente"),
                        rs.getString("telefono")
                });
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    private void addTelefono() {
        try {
            int idCliente = Integer.parseInt(JOptionPane.showInputDialog(this, "ID Cliente:"));
            String telefono = JOptionPane.showInputDialog(this, "Teléfono:");

            try (PreparedStatement stmt = connection.prepareStatement(
                    "INSERT INTO TELEFONO (id_cliente, telefono) VALUES (?, ?)")) {
                stmt.setInt(1, idCliente);
                stmt.setString(2, telefono);
                stmt.executeUpdate();

                tableModel.addRow(new Object[]{idCliente, telefono});
            }
        } catch (Exception e) {
            JOptionPane.showMessageDialog(this, "Error al añadir el teléfono", "Error", JOptionPane.ERROR_MESSAGE);
            e.printStackTrace();
        }
    }

    private void editTelefono() {
        int selectedRow = table.getSelectedRow();
        if (selectedRow == -1) {
            JOptionPane.showMessageDialog(this, "Selecciona un teléfono para modificar", "Error", JOptionPane.WARNING_MESSAGE);
            return;
        }

        try {
            int idCliente = (int) tableModel.getValueAt(selectedRow, 0);
            String telefonoActual = (String) tableModel.getValueAt(selectedRow, 1);
            String nuevoTelefono = JOptionPane.showInputDialog(this, "Nuevo Teléfono:", telefonoActual);

            try (PreparedStatement stmt = connection.prepareStatement(
                    "UPDATE TELEFONO SET telefono = ? WHERE id_cliente = ? AND telefono = ?")) {
                stmt.setString(1, nuevoTelefono);
                stmt.setInt(2, idCliente);
                stmt.setString(3, telefonoActual);
                stmt.executeUpdate();

                tableModel.setValueAt(nuevoTelefono, selectedRow, 1);
            }
        } catch (Exception e) {
            JOptionPane.showMessageDialog(this, "Error al modificar el teléfono", "Error", JOptionPane.ERROR_MESSAGE);
            e.printStackTrace();
        }
    }

    private void deleteTelefono() {
        int selectedRow = table.getSelectedRow();
        if (selectedRow == -1) {
            JOptionPane.showMessageDialog(this, "Selecciona un teléfono para eliminar", "Error", JOptionPane.WARNING_MESSAGE);
            return;
        }

        int idCliente = (int) tableModel.getValueAt(selectedRow, 0);
        String telefono = (String) tableModel.getValueAt(selectedRow, 1);

        try (PreparedStatement stmt = connection.prepareStatement(
                "DELETE FROM TELEFONO WHERE id_cliente = ? AND telefono = ?")) {
            stmt.setInt(1, idCliente);
            stmt.setString(2, telefono);
            stmt.executeUpdate();
            tableModel.removeRow(selectedRow);
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Error al eliminar el teléfono", "Error", JOptionPane.ERROR_MESSAGE);
            e.printStackTrace();
        }
    }
}
