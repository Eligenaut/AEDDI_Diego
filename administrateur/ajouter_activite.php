<?php
session_start();

// Vérifier les droits d'accès
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

// Connexion à la base de données
$servername = "fdb1028.awardspace.net";
$username = "4553951_eligenaut";
$password = "jabady@55";
$dbname = "4553951_eligenaut";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Connexion à la base de données échouée']);
    exit;
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = isset($_POST['type']) ? trim($_POST['type']) : '';
    $date_debut = isset($_POST['date_debut']) ? trim($_POST['date_debut']) : '';
    $date_fin = isset($_POST['date_fin']) && !empty(trim($_POST['date_fin'])) ? trim($_POST['date_fin']) : NULL;

    if (!in_array($type, ['activite', 'cotisation'])) {
        echo json_encode(['success' => false, 'error' => 'Type non valide']);
        exit;
    }

    if (empty($date_debut)) {
        echo json_encode(['success' => false, 'error' => 'La date de début est requise']);
        exit;
    }

    if ($type == "activite") {
        $titre = isset($_POST['titre']) ? trim($_POST['titre']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $montant = NULL;
        $type_cotisation = NULL;

        if (empty($titre)) {
            echo json_encode(['success' => false, 'error' => "Le nom de l'activité est requis"]);
            exit;
        }
    } else {
        $titre = isset($_POST['type_cotisation']) ? trim($_POST['type_cotisation']) : '';
        $montant = isset($_POST['montant']) ? (float)$_POST['montant'] : 0;
        $description = NULL;

        if (empty($titre)) {
            echo json_encode(['success' => false, 'error' => "Le type de cotisation est requis"]);
            exit;
        }

        if ($montant <= 0) {
            echo json_encode(['success' => false, 'error' => "Le montant doit être supérieur à 0"]);
            exit;
        }
    }

    $sql = "INSERT INTO activites_cotisations (type, titre, description, montant, date_debut, date_fin, statut) 
            VALUES (?, ?, ?, ?, ?, ?, 'en cours')";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => "Préparation de la requête échouée"]);
        exit;
    }

    $stmt->bind_param("sssdss", $type, $titre, $description, $montant, $date_debut, $date_fin);

    if ($stmt->execute()) {
        $new_id = $stmt->insert_id;

        if ($type == "cotisation") {
            $sql_users = "SELECT id FROM utilisateurs WHERE role = 'membre'";
            $result_users = $conn->query($sql_users);

            if ($result_users && $result_users->num_rows > 0) {
                $sql_link = "INSERT INTO utilisateurs_cotisations (id_utilisateur, id_cotisation, etat_paiement) 
                             VALUES (?, ?, 'non payé')";
                $stmt_link = $conn->prepare($sql_link);

                if ($stmt_link) {
                    while ($user = $result_users->fetch_assoc()) {
                        $stmt_link->bind_param("ii", $user['id'], $new_id);
                        $stmt_link->execute(); // erreurs silencieuses, mais loggées
                    }
                    $stmt_link->close();
                }
            }
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'error' => "Méthode non autorisée"]);
}
?>

<div class="bg-gray-700 p-6 rounded-lg shadow-lg w-full max-w-2xl mx-auto border border-gray-600 animate-fade-in">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-white">Ajouter une activité ou cotisation</h2>
        <button onclick="closeForm()" class="text-gray-400 hover:text-white transition-colors">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>
    
    <form id="addForm" method="POST" class="space-y-4" onsubmit="submitForm(event)">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-300 mb-2">Type :</label>
                <select name="type" id="activityType" class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required onchange="toggleFields()">
                    <option value="">Sélectionnez un type</option>
                    <option value="activite">Activité</option>
                    <option value="cotisation">Cotisation</option>
                </select>
            </div>       
        </div>

        <div id="nameField">
            <label class="block text-gray-300 mb-2">Nom de l'activité :</label>
            <input type="text" name="nom_activite" class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
        </div>

        <div id="descriptionField">
            <label class="block text-gray-300 mb-2">Description :</label>
            <textarea name="description" rows="3" class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div id="montantField">
                <label class="block text-gray-300 mb-2">Montant (Ar) :</label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-400">Ar</span>
                    <input type="number" name="montant" step="100" class="w-full pl-10 pr-4 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                </div>
            </div>
            
            <div>
                <label class="block text-gray-300 mb-2">Date de début :</label>
                <input type="date" name="date_debut" class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
            </div>
            
            <div>
                <label class="block text-gray-300 mb-2">Date de fin :</label>
                <input type="date" name="date_fin" class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4">
            <button onclick="loadView('activities')" class="text-blue-400 hover:text-blue-300 mr-4">
              	<i class="fas fa-arrow-left mr-2"></i> Anuller
            </button>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200 flex items-center">
                <i class="fas fa-plus mr-2"></i> Ajouter
            </button>
        </div>
    </form>
</div>

<style>
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out forwards;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
// Fonction pour afficher/masquer les champs selon le type sélectionné
function toggleFields() {
    const typeSelect = document.getElementById('activityType');
    const montantField = document.getElementById('montantField');
    const cotisationField = document.getElementById('cotisationTypeField');
    const nameField = document.getElementById('nameField');
    const descriptionField = document.getElementById('descriptionField');

    if (typeSelect.value === 'cotisation') {
        montantField.classList.remove('hidden');
        cotisationField.classList.remove('hidden');
        nameField.classList.add('hidden');
        descriptionField.classList.add('hidden');
        
        // Rendre les champs obligatoires
        document.querySelector('input[name="montant"]').required = true;
        document.querySelector('input[name="type_cotisation"]').required = true;
        document.querySelector('input[name="nom_activite"]').required = false;
        document.querySelector('textarea[name="description"]').required = false;
    } else if (typeSelect.value === 'activite') {
        montantField.classList.add('hidden');
        cotisationField.classList.add('hidden');
        nameField.classList.remove('hidden');
        descriptionField.classList.remove('hidden');
        
        // Rendre les champs obligatoires
        document.querySelector('input[name="montant"]').required = false;
        document.querySelector('input[name="type_cotisation"]').required = false;
        document.querySelector('input[name="nom_activite"]').required = true;
        document.querySelector('textarea[name="description"]').required = true;
    } else {
        // Cas par défaut (aucun type sélectionné)
        montantField.classList.add('hidden');
        cotisationField.classList.add('hidden');
        nameField.classList.add('hidden');
        descriptionField.classList.add('hidden');
    }
}

// Fermer le formulaire
function closeForm() {
    const formContainer = document.getElementById('formContainer');
    formContainer.style.animation = 'fadeOut 0.3s ease-out forwards';
    
    setTimeout(() => {
        formContainer.innerHTML = '';
        // Réafficher le contenu des activités si caché
        const activitesContent = document.getElementById('activitesContent');
        if (activitesContent) activitesContent.style.display = 'block';
    }, 300);
}

// Soumettre le formulaire via AJAX
function submitForm(event) {
    event.preventDefault();
    const form = event.target;
    const formContainer = document.getElementById('formContainer');
    
    // Afficher un indicateur de chargement stylisé
    formContainer.innerHTML = `
        <div class="bg-gray-700 p-8 rounded-lg shadow-lg w-full max-w-2xl mx-auto border border-gray-600 text-center animate-fade-in">
            <i class="fas fa-spinner fa-spin text-3xl text-blue-500 mb-4"></i>
            <p class="text-white">Traitement en cours...</p>
        </div>
    `;

    fetch('traitement_ajout.php', {
        method: 'POST',
        body: new FormData(form)
    })
    .then(response => {
        if (!response.ok) throw new Error('Erreur réseau');
        return response.text(); // On utilise text() car votre PHP retourne du texte simple
    })
    .then(data => {
        if (data === "succès") {
            // Afficher un message de succès stylisé
            formContainer.innerHTML = `
                <div class="bg-gray-700 p-6 rounded-lg shadow-lg w-full max-w-2xl mx-auto border border-green-500 animate-fade-in">
                    <div class="text-center">
                        <i class="fas fa-check-circle text-4xl text-green-500 mb-4"></i>
                        <h3 class="text-xl font-bold text-white mb-2">Succès!</h3>
                        <p class="text-gray-300 mb-4">L'élément a été ajouté avec succès</p>
                        <button onclick="loadActivitiesView()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-sync-alt mr-2"></i> Recharger la liste
                        </button>
                    </div>
                </div>
            `;
        } else {
            // Afficher les erreurs
            formContainer.innerHTML = `
                <div class="bg-gray-700 p-6 rounded-lg shadow-lg w-full max-w-2xl mx-auto border border-red-500 animate-fade-in">
                    <div class="text-center">
                        <i class="fas fa-exclamation-circle text-4xl text-red-500 mb-4"></i>
                        <h3 class="text-xl font-bold text-white mb-2">Erreur</h3>
                        <p class="text-gray-300 mb-4">${data || 'Une erreur est survenue lors de l\'ajout'}</p>
                        <button onclick="loadAddForm()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-undo mr-2"></i> Réessayer
                        </button>
                    </div>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        formContainer.innerHTML = `
            <div class="bg-gray-700 p-6 rounded-lg shadow-lg w-full max-w-2xl mx-auto border border-red-500 animate-fade-in">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-4"></i>
                    <h3 class="text-xl font-bold text-white mb-2">Erreur de connexion</h3>
                    <p class="text-gray-300 mb-4">Impossible de se connecter au serveur</p>
                    <button onclick="loadAddForm()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-undo mr-2"></i> Réessayer
                    </button>
                </div>
            </div>
        `;
    });
}

// Initialiser les champs au chargement
document.addEventListener('DOMContentLoaded', () => {
    toggleFields();
});
</script>