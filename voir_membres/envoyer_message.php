<?php
session_start();
require 'db_connection.php'; // Ce fichier établit la connexion dans la variable $conn

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['message'])) {
    // Récupérer l'ID de l'utilisateur connecté depuis la table utilisateurs (stocké en session)
    $sender_id = $_SESSION['id'] ?? 0;
    // Récupérer l'ID du destinataire envoyé par le formulaire (vérifiez que cet ID correspond à un utilisateur existant)
    $destinataire_id = isset($_POST['destinataire_id']) ? (int)$_POST['destinataire_id'] : 0;
    // Nettoyer et valider le message
    $message = htmlspecialchars(trim($_POST['message']));
    
    // Déterminer si l'expéditeur est administrateur (optionnel, pour marquer dans la table)
    $is_admin = (($_SESSION['role'] ?? '') === 'admin') ? 1 : 0;
    
    // Préparer l'insertion dans la table messages
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, destinataire_id, message, created_at, lu, is_admin) VALUES (?, ?, ?, NOW(), 0, ?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => $conn->error]);
        exit();
    }
    $stmt->bind_param("iisi", $sender_id, $destinataire_id, $message, $is_admin);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    
    $stmt->close();
}
?>
