<?php
// Vérifier si l'ID de l'utilisateur est passé dans l'URL
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Connexion à la base de données
        $servername = "fdb1028.awardspace.net";
        $username = "4553951_eligenaut";
        $password = "jabady@55"; // Remplacez par votre mot de passe réel
        $dbname = "4553951_eligenaut";

    // Créer la connexion
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Erreur de connexion à la base de données.");
    }

    // Requête pour récupérer les informations de l'utilisateur par ID
    $sql = "SELECT * FROM utilisateurs WHERE id = '$userId'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Si l'utilisateur existe, récupérer ses données
        $user = $result->fetch_assoc();
        echo json_encode($user); // Renvoie les données sous forme de JSON
    } else {
        echo json_encode(["error" => "Utilisateur non trouvé."]);
    }

    // Fermer la connexion
    $conn->close();
} else {
    echo json_encode(["error" => "ID utilisateur manquant."]);
}
?>
