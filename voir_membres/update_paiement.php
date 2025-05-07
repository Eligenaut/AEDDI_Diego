<?php
session_start();

// Vérifier si l'utilisateur est administrateur
if ($_SESSION['role'] !== 'admin') {
    die("Accès refusé.");
}

// Connexion à la base de données
$servername = "fdb1028.awardspace.net";
$username = "4553951_eligenaut";
$password = "jabady@55";
$dbname = "4553951_eligenaut";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion à la base de données.");
}

// Récupérer les données de la requête POST
$data = json_decode(file_get_contents('php://input'), true);
$id_cotisation = $data['id_cotisation'];
$id_utilisateur = $data['id_utilisateur'];
$etat_paiement = $data['etat_paiement'];

// Requête pour mettre à jour le statut de paiement
$sql = "UPDATE utilisateurs_cotisations 
        SET etat_paiement = ? 
        WHERE id_cotisation = ? AND id_utilisateur = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $etat_paiement, $id_cotisation, $id_utilisateur);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

$stmt->close();
$conn->close();
?>