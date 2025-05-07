<?php
session_start();

// Vérification de la session pour s'assurer que l'administrateur est connecté
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../connexion/connexion.php"); // Redirection si l'utilisateur n'est pas un administrateur
    exit();
}

// Vérification si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données envoyées par le formulaire
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $etablissement = $_POST['etablissement'];
    $parcours = $_POST['parcours'];
    $promotion = $_POST['promotion'];
    $telephone = $_POST['telephone'];

    // Connexion à la base de données
$servername = "fdb1028.awardspace.net";
$username = "4553951_eligenaut";
$password = "jabady@55";  // Remplacez par votre mot de passe réel
$dbname = "4553951_eligenaut";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Erreur de connexion à la base de données : " . $conn->connect_error);
    }

    // Requête pour mettre à jour les informations de l'utilisateur
    $sql = "UPDATE utilisateurs SET nom = ?, prenom = ?, etablissement = ?, parcours = ?, promotion = ?, telephone = ? WHERE id = ?";

    // Préparer la requête
    if ($stmt = $conn->prepare($sql)) {
        // Lier les paramètres
        $stmt->bind_param("ssssssi", $nom, $prenom, $etablissement, $parcours, $promotion, $telephone, $id);

        // Exécuter la requête
        if ($stmt->execute()) {
            // Si la mise à jour est réussie, rediriger vers la liste des membres
            header("Location: voir_membres.php"); // Rediriger vers la page des membres
            exit();
        } else {
            echo "Erreur de mise à jour : " . $stmt->error;
        }
        
        // Fermer la requête
        $stmt->close();
    } else {
        echo "Erreur de préparation de la requête : " . $conn->error;
    }

    // Fermer la connexion
    $conn->close();
}
?>
