<?php
session_start();
require 'db_connection.php'; // Votre connexion à la DB

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $message = $_POST['message'];
    $sender_id = $_SESSION['user_id']; // Vérifiez que cet index est défini lors de la connexion
    $destinataire_id = isset($_POST['destinataire_id']) ? $_POST['destinataire_id'] : 0;

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, destinataire_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $sender_id, $destinataire_id, $message);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    $stmt->close();
}
?>
