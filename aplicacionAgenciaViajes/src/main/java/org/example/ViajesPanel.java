package org.example;

import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.math.BigDecimal;
import java.sql.*;

public class ViajesPanel extends JPanel {
    private JTable table;
    private DefaultTableModel tableModel;
    private Connection connection;

    public ViajesPanel() {
        setLayout(new BorderLayout());

        // Configurar tabla
        tableModel = new DefaultTableModel(new String[]{"ID", "Origen", "Destino", "Fecha Salida", "Fecha Llegada", "Precio", "Máx. Plazas"}, 0);
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
        addButton.addActionListener(e -> addViaje());
        editButton.addActionListener(e -> editViaje());
        deleteButton.addActionListener(e -> deleteViaje());
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
            ResultSet rs = stmt.executeQuery("SELECT * FROM VIAJE");
            while (rs.next()) {
                tableModel.addRow(new Object[]{
                        rs.getInt("id_viaje"),
                        rs.getString("origen"),
                        rs.getString("destino"),
                        rs.getTimestamp("fecha_salida"),
                        rs.getTimestamp("fecha_llegada"),
                        rs.getBigDecimal("precio"),
                        rs.getInt("max_plazas")
                });
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    private void addViaje() {
        String origen = JOptionPane.showInputDialog(this, "Origen:");
        String destino = JOptionPane.showInputDialog(this, "Destino:");
        String fechaSalida = JOptionPane.showInputDialog(this, "Fecha de salida (YYYY-MM-DD HH:MM:SS):");
        String fechaLlegada = JOptionPane.showInputDialog(this, "Fecha de llegada (YYYY-MM-DD HH:MM:SS):");
        String precioStr = JOptionPane.showInputDialog(this, "Precio:");
        String maxPlazasStr = JOptionPane.showInputDialog(this, "Máx. plazas:");

        try {
            BigDecimal precio = new BigDecimal(precioStr);
            int maxPlazas = Integer.parseInt(maxPlazasStr);

            try (PreparedStatement stmt = connection.prepareStatement(
                    "INSERT INTO VIAJE (origen, destino, fecha_salida, fecha_llegada, precio, max_plazas) VALUES (?, ?, ?, ?, ?, ?)",
                    Statement.RETURN_GENERATED_KEYS)) {
                stmt.setString(1, origen);
                stmt.setString(2, destino);
                stmt.setTimestamp(3, Timestamp.valueOf(fechaSalida));
                stmt.setTimestamp(4, Timestamp.valueOf(fechaLlegada));
                stmt.setBigDecimal(5, precio);
                stmt.setInt(6, maxPlazas);
                stmt.executeUpdate();

                ResultSet keys = stmt.getGeneratedKeys();
                if (keys.next()) {
                    tableModel.addRow(new Object[]{
                            keys.getInt(1), origen, destino, Timestamp.valueOf(fechaSalida), Timestamp.valueOf(fechaLlegada), precio, maxPlazas
                    });
                }
            }
        } catch (Exception e) {
            JOptionPane.showMessageDialog(this, "Error al añadir el viaje", "Error", JOptionPane.ERROR_MESSAGE);
            e.printStackTrace();
        }
    }

    private void editViaje() {
        int selectedRow = table.getSelectedRow();
        if (selectedRow == -1) {
            JOptionPane.showMessageDialog(this, "Selecciona un viaje para modificar", "Error", JOptionPane.WARNING_MESSAGE);
            return;
        }

        int idViaje = (int) tableModel.getValueAt(selectedRow, 0);
        String origen = JOptionPane.showInputDialog(this, "Nuevo origen:", tableModel.getValueAt(selectedRow, 1));
        String destino = JOptionPane.showInputDialog(this, "Nuevo destino:", tableModel.getValueAt(selectedRow, 2));
        String fechaSalida = JOptionPane.showInputDialog(this, "Nueva fecha de salida (YYYY-MM-DD HH:MM:SS):", tableModel.getValueAt(selectedRow, 3));
        String fechaLlegada = JOptionPane.showInputDialog(this, "Nueva fecha de llegada (YYYY-MM-DD HH:MM:SS):", tableModel.getValueAt(selectedRow, 4));
        String precioStr = JOptionPane.showInputDialog(this, "Nuevo precio:", tableModel.getValueAt(selectedRow, 5));
        String maxPlazasStr = JOptionPane.showInputDialog(this, "Nuevas máx. plazas:", tableModel.getValueAt(selectedRow, 6));

        try {
            BigDecimal precio = new BigDecimal(precioStr);
            int maxPlazas = Integer.parseInt(maxPlazasStr);

            try (PreparedStatement stmt = connection.prepareStatement(
                    "UPDATE VIAJE SET origen = ?, destino = ?, fecha_salida = ?, fecha_llegada = ?, precio = ?, max_plazas = ? WHERE id_viaje = ?")) {
                stmt.setString(1, origen);
                stmt.setString(2, destino);
                stmt.setTimestamp(3, Timestamp.valueOf(fechaSalida));
                stmt.setTimestamp(4, Timestamp.valueOf(fechaLlegada));
                stmt.setBigDecimal(5, precio);
                stmt.setInt(6, maxPlazas);
                stmt.setInt(7, idViaje);
                stmt.executeUpdate();

                tableModel.setValueAt(origen, selectedRow, 1);
                tableModel.setValueAt(destino, selectedRow, 2);
                tableModel.setValueAt(Timestamp.valueOf(fechaSalida), selectedRow, 3);
                tableModel.setValueAt(Timestamp.valueOf(fechaLlegada), selectedRow, 4);
                tableModel.setValueAt(precio, selectedRow, 5);
                tableModel.setValueAt(maxPlazas, selectedRow, 6);
            }
        } catch (Exception e) {
            JOptionPane.showMessageDialog(this, "Error al modificar el viaje", "Error", JOptionPane.ERROR_MESSAGE);
            e.printStackTrace();
        }
    }

    private void deleteViaje() {
        int selectedRow = table.getSelectedRow();
        if (selectedRow == -1) {
            JOptionPane.showMessageDialog(this, "Selecciona un viaje para eliminar", "Error", JOptionPane.WARNING_MESSAGE);
            return;
        }

        int idViaje = (int) tableModel.getValueAt(selectedRow, 0);
        try (PreparedStatement stmt = connection.prepareStatement(
                "DELETE FROM VIAJE WHERE id_viaje = ?")) {
            stmt.setInt(1, idViaje);
            stmt.executeUpdate();
            tableModel.removeRow(selectedRow);
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(this, "Error al eliminar el viaje", "Error", JOptionPane.ERROR_MESSAGE);
            e.printStackTrace();
        }
    }
}
