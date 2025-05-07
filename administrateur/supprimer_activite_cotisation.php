<?php
session_start();
ob_start();

// Connexion à la base de données
$servername = "fdb1028.awardspace.net";
$username = "4553951_eligenaut";
$password = "jabady@55";
$dbname = "4553951_eligenaut";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// Récupérer l'ID de l'activité ou de la cotisation à supprimer
$id = $_GET['id'];

// Supprimer l'élément
$sql = "DELETE FROM activites_cotisations WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['message'] = "Suppression réussie !";
} else {
    $_SESSION['error'] = "Erreur lors de la suppression : " . $stmt->error;
}

$stmt->close();
$conn->close();

header("Location: ajouter_activite_cotisation.php");
exit();
?>