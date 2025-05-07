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

// Récupérer les données du formulaire
$id = $_POST['id'];
$type = $_POST['type'];
$titre = $_POST['nom_activite'];
$description = $_POST['description'];
$montant = $_POST['montant'];
$date_debut = $_POST['date_debut'];
$date_fin = $_POST['date_fin'];

// Mettre à jour les données dans la base de données
$sql = "UPDATE activites_cotisations 
        SET type = ?, titre = ?, description = ?, montant = ?, date_debut = ?, date_fin = ?
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssdssi", $type, $titre, $description, $montant, $date_debut, $date_fin, $id);

if ($stmt->execute()) {
    echo "succès";
} else {
    echo "Erreur lors de la modification : " . $stmt->error;
}

$stmt->close();
$conn->close();
ob_end_flush();
?>