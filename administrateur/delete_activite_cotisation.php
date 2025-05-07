<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../connexion/connexion.php");
    exit();
}

// Vérifier si l'ID est bien reçu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Connexion à la base de données
    $servername = "fdb1028.awardspace.net";
    $username = "4553951_eligenaut";
    $password = "jabady@55";
    $dbname = "4553951_eligenaut";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Échec de la connexion : " . $conn->connect_error);
    }

    // Supprimer l'activité ou la cotisation
    $sql = "DELETE FROM activites_cotisations WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: voir_activites_cotisations.php"); // Retour à la liste après suppression
        exit();
    } else {
        echo "Erreur lors de la suppression : " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "ID invalide.";
}
?>
