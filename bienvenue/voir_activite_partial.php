<?php
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die(json_encode(['error' => 'ID utilisateur invalide']));
}

$userId = (int) $_GET['id'];

// Connexion à la base de données
$servername = "fdb1028.awardspace.net";
$username = "4553951_eligenaut";
$password = "jabady@55"; 
$dbname = "4553951_eligenaut";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Erreur de connexion à la base de données']));
}

// Récupérer les activités en cours
$sql_activites = "SELECT * FROM activites_cotisations WHERE type = 'activité' AND statut = 'en cours'";
$result_activites = $conn->query($sql_activites);

// Récupérer les cotisations de l'utilisateur
$stmt_cotisations = $conn->prepare("SELECT ac.*, uc.etat_paiement 
                  FROM activites_cotisations ac
                  JOIN utilisateurs_cotisations uc ON ac.id = uc.id_cotisation
                  WHERE uc.id_utilisateur = ?");
$stmt_cotisations->bind_param("i", $userId);
$stmt_cotisations->execute();
$result_cotisations = $stmt_cotisations->get_result();

function esc($val) {
    return htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<div class="bg-gray-700 rounded-lg h-full flex flex-col">
  <div class="p-6 flex-1 overflow-y-auto">
    <h2 class="text-2xl font-bold mb-6 text-white">Activités et Cotisations</h2>
    
    <!-- Activités en cours -->
    <div class="mb-8">
      <h3 class="text-xl font-semibold mb-4 text-white">Activités en cours</h3>
      <div class="overflow-x-auto">
        <table class="w-full bg-gray-800 rounded-lg overflow-hidden">
          <thead class="bg-gray-900">
            <tr>
              <th class="px-4 py-3 text-left">Type</th>
              <th class="px-4 py-3 text-left">Nom/Type</th>
              <th class="px-4 py-3 text-left">Description</th>
              <th class="px-4 py-3 text-left">Montant</th>
              <th class="px-4 py-3 text-left">Date début</th>
              <th class="px-4 py-3 text-left">Date fin</th>
              <th class="px-4 py-3 text-left">Statut</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-700">
            <?php while ($row = $result_activites->fetch_assoc()): ?>
            <tr class="hover:bg-gray-600">
              <td class="px-4 py-3"><?= esc($row['type']) ?></td>
              <td class="px-4 py-3"><?= esc($row['titre']) ?></td>
              <td class="px-4 py-3"><?= esc($row['description'] ?? '-') ?></td>
              <td class="px-4 py-3"><?= $row['montant'] !== null ? esc($row['montant']) . " Ar" : '-' ?></td>
              <td class="px-4 py-3"><?= esc($row['date_debut']) ?></td>
              <td class="px-4 py-3"><?= esc($row['date_fin'] ?? '-') ?></td>
              <td class="px-4 py-3"><?= esc($row['statut']) ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Cotisations de l'utilisateur -->
    <div class="mb-8">
      <h3 class="text-xl font-semibold mb-4 text-white">Vos cotisations</h3>
      <div class="overflow-x-auto">
        <table class="w-full bg-gray-800 rounded-lg overflow-hidden">
          <thead class="bg-gray-900">
            <tr>
              <th class="px-4 py-3 text-left">Type</th>
              <th class="px-4 py-3 text-left">Nom/Type</th>
              <th class="px-4 py-3 text-left">Description</th>
              <th class="px-4 py-3 text-left">Montant</th>
              <th class="px-4 py-3 text-left">Date début</th>
              <th class="px-4 py-3 text-left">Date fin</th>
              <th class="px-4 py-3 text-left">Statut</th>
              <th class="px-4 py-3 text-left">Paiement</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-700">
            <?php while ($row = $result_cotisations->fetch_assoc()): ?>
            <tr class="hover:bg-gray-600">
              <td class="px-4 py-3"><?= esc($row['type']) ?></td>
              <td class="px-4 py-3"><?= esc($row['titre']) ?></td>
              <td class="px-4 py-3"><?= esc($row['description'] ?? '-') ?></td>
              <td class="px-4 py-3"><?= $row['montant'] !== null ? esc($row['montant']) . " Ar" : '-' ?></td>
              <td class="px-4 py-3"><?= esc($row['date_debut']) ?></td>
              <td class="px-4 py-3"><?= esc($row['date_fin'] ?? '-') ?></td>
              <td class="px-4 py-3"><?= esc($row['statut']) ?></td>
              <td class="px-4 py-3 <?= $row['etat_paiement'] === 'payé' ? 'text-green-400' : 'text-red-400' ?>">
                <?= esc($row['etat_paiement'] ?? '-') ?>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  
  <!-- Bouton Retour (toujours visible) -->
  <div class="p-4 border-t border-gray-600 flex-shrink-0">
    <button onclick="loadProfileView()" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded w-full">
      <i class="fas fa-arrow-left mr-2"></i> Retour au profil
    </button>
  </div>
</div>

<script>
// Fonction globale accessible depuis bienvenue.php
function loadProfileView() {
    if (window.parent && window.parent.loadProfileView) {
        window.parent.loadProfileView();
    }
}
</script>

<?php
$stmt_cotisations->close();
$conn->close();
?>