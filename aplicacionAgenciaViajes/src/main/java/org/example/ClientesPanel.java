package org.example;

import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.sql.*;

public class ClientesPanel extends JPanel {
    private JTable table;
    private DefaultTableModel tableModel;
    private Connection connection;

    public ClientesPanel() {
        setLayout(new BorderLayout());

        // Configurar tabla
        tableModel = new DefaultTableModel(new String[]{"ID", "Nombre", "Apellidos", "DNI", "Correo"}, 0);
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
        addButton.addActionListener(e -> addCliente());
        editButton.addActionListener(e -> editCliente());
        deleteButton.addActionListener(e -> deleteCliente());
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
            ResultSet rs = stmt.executeQuery("SELECT * FROM CLIENTE");
            while (rs.next()) {
                tableModel.addRow(new Object[]{
                        rs.getInt("id_cliente"),
                        rs.getString("nombre"),
                        rs.getString("apellidos"),
                        rs.getString("dni"),
                        rs.getString("correo_electronico")
                });
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    private void addCliente() {
        String nombre = JOptionPane.showInputDialog(this, "Nombre:");
        if (nombre == null || nombre.trim().isEmpty()) return;

        String apellidos = JOptionPane.showInputDialog(this, "Apellidos:");
        if (apellidos == null || apellidos.trim().isEmpty()) return;

        String dni = JOptionPane.showInputDialog(this, "DNI:");
        if (dni == null || dni.trim().isEmpty()) return;

        String correo = JOptionPane.showInputDialog(this, "Correo:");
        if (correo == null || correo.trim().isEmpty()) return;

        try (PreparedStatement stmt = connection.prepareStatement(
                "INSERT INTO CLIENTE (nombre, apellidos, dni, correo_electronico) VALUES (?, ?, ?, ?)",
                Statement.RETURN_GENERATED_KEYS)) {
            // Asignar valores al PreparedStatement
            stmt.setString(1, nombre);
            stmt.setString(2, apellidos);
            stmt.setString(3, dni);
            stmt.setString(4, correo);

            // Ejecutar inserción
            stmt.executeUpdate();

            // Obtener la clave generada automáticamente
            ResultSet keys = stmt.getGeneratedKeys();
            if (keys.next()) {
                int idCliente = keys.getInt(1); // Recuperar el ID generado
                // Añadir fila al modelo de la tabla
                tableModel.addRow(new Object[]{idCliente, nombre, apellidos, dni, correo});
            }
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Error al añadir cliente: " + e.getMessage(), "Error", JOptionPane.ERROR_MESSAGE);
            e.printStackTrace();
        }
    }

    private void editCliente() {
        int selectedRow = table.getSelectedRow();
        if (selectedRow == -1) {
            JOptionPane.showMessageDialog(this, "Selecciona un cliente para modificar", "Error", JOptionPane.WARNING_MESSAGE);
            return;
        }

        int idCliente = (int) tableModel.getValueAt(selectedRow, 0);
        String nombre = JOptionPane.showInputDialog(this, "Nuevo nombre:", tableModel.getValueAt(selectedRow, 1));
        String apellidos = JOptionPane.showInputDialog(this, "Nuevos apellidos:", tableModel.getValueAt(selectedRow, 2));
        String dni = JOptionPane.showInputDialog(this, "Nuevo DNI:", tableModel.getValueAt(selectedRow, 3));
        String correo = JOptionPane.showInputDialog(this, "Nuevo correo:", tableModel.getValueAt(selectedRow, 4));

        try (PreparedStatement stmt = connection.prepareStatement(
                "UPDATE CLIENTE SET nombre = ?, apellidos = ?, dni = ?, correo_electronico = ? WHERE id_cliente = ?")) {
            stmt.setString(1, nombre);
            stmt.setString(2, apellidos);
            stmt.setString(3, dni);
            stmt.setString(4, correo);
            stmt.setInt(5, idCliente);
            stmt.executeUpdate();

            tableModel.setValueAt(nombre, selectedRow, 1);
            tableModel.setValueAt(apellidos, selectedRow, 2);
            tableModel.setValueAt(dni, selectedRow, 3);
            tableModel.setValueAt(correo, selectedRow, 4);
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    private void deleteCliente() {
        int selectedRow = table.getSelectedRow();
        if (selectedRow == -1) {
            JOptionPane.showMessageDialog(this, "Selecciona un cliente para eliminar", "Error", JOptionPane.WARNING_MESSAGE);
            return;
        }

        int idCliente = (int) tableModel.getValueAt(selectedRow, 0);
        try (PreparedStatement stmt = connection.prepareStatement(
                "DELETE FROM CLIENTE WHERE id_cliente = ?")) {
            stmt.setInt(1, idCliente);
            stmt.executeUpdate();
            tableModel.removeRow(selectedRow);
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
}
