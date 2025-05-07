<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['role'])) {
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

// Récupérer l'ID de l'utilisateur depuis la requête GET
$user_id = $_GET['user_id'];

// Requête pour récupérer les cotisations de l'utilisateur
$sql = "SELECT ac.*, uc.etat_paiement 
        FROM activites_cotisations ac
        JOIN utilisateurs_cotisations uc ON ac.id = uc.id_cotisation
        WHERE uc.id_utilisateur = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cotisations = [];
while ($row = $result->fetch_assoc()) {
    $cotisations[] = $row;
}

// Retourner les données au format JSON
echo json_encode($cotisations);

$stmt->close();
$conn->close();
?>