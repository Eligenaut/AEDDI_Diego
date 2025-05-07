<?php
session_start();

header('Content-Type: application/json');

if (!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id'])) {
    echo json_encode(['error' => 'ID utilisateur invalide']);
    exit;
}

$userId = (int) $_REQUEST['id'];

// Connexion à la base de données
$servername = "fdb1028.awardspace.net";
$username = "4553951_eligenaut";
$password = "jabady@55";
$dbname = "4553951_eligenaut";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Erreur de connexion à la base de données']);
    exit;
}

// Traitement de la requête POST (enregistrement)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nom' => $_POST['nom'] ?? '',
        'prenom' => $_POST['prenom'] ?? '',
        'lieux_naissance' => $_POST['lieux_naissance'] ?? '',
        'date_naissance' => $_POST['date_naissance'] ?? '',
        'cin' => $_POST['cin'] ?? '',
        'date_delivrance' => $_POST['date_delivrance'] ?? '',
        'etablissement' => $_POST['etablissement'] ?? '',
        'parcours' => $_POST['parcours'] ?? '',
        'niv_etude' => $_POST['niv_etude'] ?? '',
        'adresse_mail' => $_POST['adresse_mail'] ?? '',
        'telephone' => $_POST['telephone'] ?? '',
        'mot_de_passe' => $_POST['mot_de_passe'] ?? ''
    ];

    // Construction de la requête SQL
    $sql = "UPDATE utilisateurs SET ";
    $params = [];
    $types = '';
    
    foreach ($data as $field => $value) {
        if (!empty($value)) {  // Correction ici - parenthèse supplémentaire supprimée
            $sql .= "$field = ?, ";
            $params[] = $value;
            $types .= 's';
        }
    }
    
    $sql = rtrim($sql, ', ') . " WHERE id = ?";
    $params[] = $userId;
    $types .= 'i';

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo json_encode(['error' => 'Erreur de préparation de la requête']);
        exit;
    }

    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Erreur lors de la mise à jour: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
    exit;
}

// Récupération des données pour affichage (GET)
$stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

function esc($val) {
    return htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8');
}

// Si ce n'est pas une requête POST, retourner le formulaire HTML
header('Content-Type: text/html');
?>

<div class="bg-gray-700 p-6 rounded-lg">
    <h2 class="text-2xl font-bold mb-4 text-white">Modifier votre profil</h2>
    
    <form method="post" class="edit-form">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Colonne 1 - Informations personnelles -->
            <div>
                <h3 class="text-xl font-semibold mb-3 text-white">Informations personnelles</h3>
                
                <div class="form-group">
                    <label class="text-gray-300">Nom</label>
                    <input type="text" name="nom" value="<?= esc($user['nom']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="text-gray-300">Prénom</label>
                    <input type="text" name="prenom" value="<?= esc($user['prenom']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="text-gray-300">Lieu de naissance</label>
                    <input type="text" name="lieux_naissance" value="<?= esc($user['lieux_naissance']) ?>">
                </div>
                
                <div class="form-group">
                    <label class="text-gray-300">Date de naissance</label>
                    <input type="date" name="date_naissance" value="<?= esc($user['date_naissance']) ?>">
                </div>
                
                <div class="form-group">
                    <label class="text-gray-300">CIN</label>
                    <input type="text" name="cin" value="<?= esc($user['cin']) ?>">
                </div>
                
                <div class="form-group">
                    <label class="text-gray-300">Date de délivrance</label>
                    <input type="date" name="date_delivrance" value="<?= esc($user['date_delivrance']) ?>">
                </div>
            </div>
            
            <!-- Colonne 2 - Informations académiques -->
            <div>
                <h3 class="text-xl font-semibold mb-3 text-white">Informations académiques</h3>
                
                <div class="form-group">
                    <label class="text-gray-300">Établissement</label>
                    <input type="text" name="etablissement" value="<?= esc($user['etablissement']) ?>">
                </div>
                
                <div class="form-group">
                    <label class="text-gray-300">Parcours</label>
                    <input type="text" name="parcours" value="<?= esc($user['parcours']) ?>">
                </div>
                
                <div class="form-group">
                    <label class="text-gray-300">Niveau d'étude</label>
                    <input type="text" name="niv_etude" value="<?= esc($user['niv_etude']) ?>">
                </div>
                
                <div class="form-group">
                    <label class="text-gray-300">Email</label>
                    <input type="email" name="adresse_mail" value="<?= esc($user['adresse_mail']) ?>">
                </div>
                
                <div class="form-group">
                    <label class="text-gray-300">Téléphone</label>
                    <input type="tel" name="telephone" value="<?= esc($user['telephone']) ?>">
                </div>
                
                <div class="form-group">
                    <label class="text-gray-300">Mot de passe</label>
                    <input type="password" name="mot_de_passe" placeholder="Laisser vide pour ne pas modifier">
                </div>
            </div>
        </div>
        
        <div class="flex justify-end space-x-4 mt-6">
            <button type="button" id="cancelEdit" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded">
                Annuler
            </button>
            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded">
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>