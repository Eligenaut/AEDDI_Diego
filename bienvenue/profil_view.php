<?php
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID utilisateur manquant ou invalide.");
}
$userId = (int) $_GET['id'];

// Connexion à la base de données
$servername = "fdb1028.awardspace.net";
$username   = "4553951_eligenaut";
$password   = "jabady@55";
$dbname     = "4553951_eligenaut";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}

// Récupération de l'utilisateur
$stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Aucun utilisateur trouvé.");
}
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

function esc($val) {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}
$nom             = esc($user['nom']);
$prenom          = esc($user['prenom']);
$lieux_naissance = esc($user['lieux_naissance']);
$date_naissance  = esc($user['date_naissance']);
$cin             = esc($user['cin']);
$date_delivrance = esc($user['date_delivrance']);
$etablissement   = esc($user['etablissement']);
$parcours        = esc($user['parcours']);
$niv_etude       = esc($user['niv_etude']);
$adresse_mail    = esc($user['adresse_mail']);
$telephone       = esc($user['telephone']);
$mot_de_passe    = esc($user['mot_de_passe']);
$photo           = esc($user['photo']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Profil de <?= $prenom . " " . $nom ?></title>
  <style>
    body {
      font-family: 'Roboto', sans-serif;
      background-color: #1e1e1e;
      color: #f8fafc;
      margin: 0;
      padding: 0;
    }

    .profile-container {
      max-width: 1800px;
      margin: 0 auto;
      padding: 20px;
      background-color: #2d3748;
      border-radius: 10px;
    }

    .profile-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .profile-header h1 {
      font-size: 2em;
      font-weight: bold;
      color: #f8fafc;
    }

    .edit-button {
      background-color: #3b82f6;
      color: white;
      padding: 8px 16px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }

    .edit-button:hover {
      background-color: #2563eb;
    }

    .profile-content {
      display: flex;
      gap: 40px;
    }

    .profile-photo-section {
      flex: 0 0 300px;
    }

    .profile-photo-section img {
      width: 100%;
      height: auto;
      border: 2px solid #4a5568;
      border-radius: 10px;
    }

    .info-columns-container {
      display: flex;
      flex: 1;
      gap: 20px;
    }

    .info-column {
      flex: 1;
      background-color: #374151;
      padding: 20px;
      border-radius: 10px;
    }

    .info-column h2 {
      font-size: 1.5em;
      font-weight: bold;
      color: #f8fafc;
      margin-bottom: 10px;
    }

    .info-column hr {
      border: 0;
      border-top: 2px solid #4a5568;
      margin-bottom: 20px;
    }

    .info-row {
      display: flex;
      margin-bottom: 15px;
      font-size: 1em;
    }

    .info-row label {
      font-weight: bold;
      color: #cbd5e0;
      width: 150px;
    }

    .info-row .value {
      flex: 1;
      color: #f8fafc;
      padding: 5px;
    }

    .action-buttons {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 20px;
    }

    .logout-button {
      background-color: #dc2626;
      color: white;
      padding: 8px 16px;
      border-radius: 4px;
      cursor: pointer;
      border: none;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="profile-container">
    <div class="profile-header">
      <h1>Bienvenue, <?= $prenom . " " . $nom ?></h1>
    </div>

    <div class="profile-content">
      <div class="profile-photo-section">
        <img src="../uploads/<?= $photo ?>" alt="Photo de <?= $prenom . " " . $nom ?>">
      </div>

      <div class="info-columns-container">
        <!-- Première colonne -->
        <div class="info-column">
          <h2>Informations personnelles</h2>
          <hr>
          <div class="info-row">
            <label>Nom :</label>
            <span class="value"><?= $nom ?></span>
          </div>
          <div class="info-row">
            <label>Prénom :</label>
            <span class="value"><?= $prenom ?></span>
          </div>
          <div class="info-row">
            <label>Lieu de naissance :</label>
            <span class="value"><?= $lieux_naissance ?></span>
          </div>
          <div class="info-row">
            <label>Date de naissance :</label>
            <span class="value"><?= $date_naissance ?></span>
          </div>
          <div class="info-row">
            <label>CIN :</label>
            <span class="value"><?= $cin ?></span>
          </div>
          <div class="info-row">
            <label>Date de délivrance :</label>
            <span class="value"><?= $date_delivrance ?></span>
          </div>
        </div>

        <!-- Deuxième colonne -->
        <div class="info-column">
          <h2>Informations académiques</h2>
          <hr>
          <div class="info-row">
            <label>Établissement :</label>
            <span class="value"><?= $etablissement ?></span>
          </div>
          <div class="info-row">
            <label>Parcours :</label>
            <span class="value"><?= $parcours ?></span>
          </div>
          <div class="info-row">
            <label>Niveau d'étude :</label>
            <span class="value"><?= $niv_etude ?></span>
          </div>
          <div class="info-row">
            <label>Mail :</label>
            <span class="value"><?= $adresse_mail ?></span>
          </div>
          <div class="info-row">
            <label>Téléphone :</label>
            <span class="value"><?= $telephone ?></span>
          </div>
          <div class="info-row">
            <label>Mot de passe :</label>
            <span class="value">••••••••</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
