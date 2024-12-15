package org.example;

import javax.swing.*;

public class VistaGeneral {
    public static void main(String[] args) {
        SwingUtilities.invokeLater(() -> {
            JFrame frame = new JFrame("Gestión de Agencia de Viajes");
            frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
            frame.setSize(800, 600);

            JTabbedPane tabbedPane = new JTabbedPane();

            // Añadir paneles para cada tabla
            tabbedPane.addTab("Clientes", new ClientesPanel());
            tabbedPane.addTab("Teléfonos", new TelefonosPanel());
            tabbedPane.addTab("Viajes", new ViajesPanel());
            tabbedPane.addTab("Reservas", new ReservasPanel());
            tabbedPane.addTab("Cancelaciones", new CancelacionesPanel());

            // Añadir pestaña de informes
            tabbedPane.addTab("Informes", new InformesPanel());

            frame.add(tabbedPane);
            frame.setVisible(true);
        });
    }
}
