<?php
session_start();

// Vérification de la session pour s'assurer que l'administrateur est connecté
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../connexion/connexion.php"); // Redirection si l'utilisateur n'est pas un administrateur
    exit();
}

// Vérifier si l'ID du membre à supprimer a été passé
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Connexion à la base de données
$servername = "fdb1028.awardspace.net";
$username = "4553951_eligenaut";
$password = "jabady@55";  // Remplacez par votre mot de passe réel
$dbname = "4553951_eligenaut";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Erreur de connexion à la base de données : " . $conn->connect_error);
    }

    // Requête pour supprimer le membre
    $sql = "DELETE FROM utilisateurs WHERE id = ?";

    // Préparer la requête
    if ($stmt = $conn->prepare($sql)) {
        // Lier l'ID du membre à la requête
        $stmt->bind_param("i", $id);

        // Exécuter la requête
        if ($stmt->execute()) {
            // Si la suppression est réussie, rediriger vers la page des membres
            header("Location: voir_membres.php"); // Rediriger vers la page des membres
            exit();
        } else {
            echo "Erreur de suppression : " . $stmt->error;
        }

        // Fermer la requête
        $stmt->close();
    } else {
        echo "Erreur de préparation de la requête : " . $conn->error;
    }

    // Fermer la connexion
    $conn->close();
} else {
    echo "ID de l'utilisateur non spécifié.";
}
?>
