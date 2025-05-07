<?php
session_start();

if (!isset($_SESSION['role'])) {
    die(json_encode(['error' => 'Non autorisé']));
}

$servername = "fdb1028.awardspace.net";
$username = "4553951_eligenaut";
$password = "jabady@55";
$dbname = "4553951_eligenaut";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données.");
}

// Récupération des paramètres
$searchTerm = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : '';
$selectedPromotion = isset($_REQUEST['promotion']) ? $_REQUEST['promotion'] : '';
$page = isset($_REQUEST['page']) ? max(1, (int)$_REQUEST['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Construction de la requête
$whereClauses = [];
$params = [];
$types = "";

if (!empty($searchTerm)) {
    $whereClauses[] = "(nom LIKE ? OR prenom LIKE ?)";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
    $types .= "ss";
}

if (!empty($selectedPromotion)) {
    $whereClauses[] = "promotion = ?";
    $params[] = $selectedPromotion;
    $types .= "s";
}

$where = $whereClauses ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Requête pour compter le total
$sql_count = "SELECT COUNT(*) as total FROM utilisateurs $where";
$stmt_count = $conn->prepare($sql_count);
if ($whereClauses) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$row = $count_result->fetch_assoc();
$totalResults = $row['total'];
$totalPages = max(1, ceil($totalResults / $limit));

// Requête principale
$sql = "SELECT id, nom, prenom, photo, etablissement, parcours, promotion, telephone 
        FROM utilisateurs 
        $where
        ORDER BY nom ASC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

if ($whereClauses) {
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="bg-gray-700 p-6 rounded-lg h-full flex flex-col">
    <div class="flex-1 overflow-y-auto">
        <h2 class="text-2xl font-bold mb-6 text-white">Liste des membres</h2>
        
        <!-- Barre de recherche et filtre -->
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <form id="searchForm" class="flex-1">
                <div class="flex">
                    <input type="text" name="search" placeholder="Rechercher par nom ou prénom" 
                           value="<?= htmlspecialchars($searchTerm) ?>" 
                           class="flex-1 px-4 py-2 rounded-l-md bg-gray-800 text-white border border-gray-600">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-r-md">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
            <form id="filterForm" class="flex-1">
                <div class="flex">
                    <select name="promotion" class="flex-1 px-4 py-2 rounded-l-md bg-gray-800 text-white border border-gray-600">
                        <option value="">Toutes promotions</option>
                        <?php
                        $promotions = ['2018','2019','2020','2021','2022','2023','2024'];
                        foreach ($promotions as $promo) {
                            $selected = ($selectedPromotion == $promo) ? 'selected' : '';
                            echo "<option value='$promo' $selected>$promo</option>";
                        }
                        ?>
                    </select>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-r-md">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Résultats -->
        <?php if ($result->num_rows > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full bg-gray-800 rounded-lg overflow-hidden">
                    <thead class="bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left">Photo</th>
                            <th class="px-4 py-3 text-left">Nom</th>
                            <th class="px-4 py-3 text-left">Prénom</th>
                            <th class="px-4 py-3 text-left">Établissement</th>
                            <th class="px-4 py-3 text-left">Parcours</th>
                            <th class="px-4 py-3 text-left">Promotion</th>
                            <th class="px-4 py-3 text-left">Téléphone</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php while ($user = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-600">
                            <td class="px-4 py-3">
                                <img src="../uploads/<?= htmlspecialchars($user['photo'] ?? 'default.png') ?>" 
                                     alt="Photo" class="w-10 h-10 rounded-full object-cover">
                            </td>
                            <td class="px-4 py-3"><?= htmlspecialchars($user['nom']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($user['prenom']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($user['etablissement']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($user['parcours']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($user['promotion']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($user['telephone']) ?></td>
                            <td class="px-4 py-3">
                                <div class="flex space-x-2 justify-center">
                                    <button onclick="loadUserProfile(<?= $user['id'] ?>)" 
                                            class="p-2 bg-blue-600 hover:bg-blue-500 rounded text-white"
                                            title="Voir profil">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editUser(<?= $user['id'] ?>)" 
                                            class="p-2 bg-yellow-600 hover:bg-yellow-500 rounded text-white"
                                            title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="confirmDelete(<?= $user['id'] ?>)" 
                                            class="p-2 bg-red-600 hover:bg-red-500 rounded text-white"
                                            title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button onclick="viewUserPayments(<?= $user['id'] ?>)" 
                                            class="p-2 bg-green-600 hover:bg-green-500 rounded text-white"
                                            title="Cotisations">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-user-slash text-3xl mb-2"></i>
                <p>Aucun membre trouvé</p>
                <?php if (!empty($searchTerm) || !empty($selectedPromotion)): ?>
                    <button onclick="resetFilters()" class="mt-4 text-blue-400 hover:text-blue-300">
                        <i class="fas fa-undo mr-1"></i> Réinitialiser les filtres
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="mt-4 pt-4 border-t border-gray-600 flex-shrink-0">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-gray-400 text-sm">
                Affichage de <?= $offset + 1 ?> à <?= min($offset + $limit, $totalResults) ?> sur <?= $totalResults ?> membres
            </div>
            
            <div class="flex gap-1">
                <!-- Première page -->
                <button onclick="loadPage(1)" 
                        class="px-3 py-1 rounded-md bg-gray-600 hover:bg-gray-500 text-white <?= $page == 1 ? 'opacity-50 cursor-not-allowed' : '' ?>">
                    <i class="fas fa-angle-double-left"></i>
                </button>
                
                <!-- Page précédente -->
                <button onclick="loadPage(<?= $page - 1 ?>)" 
                        class="px-3 py-1 rounded-md bg-gray-600 hover:bg-gray-500 text-white <?= $page == 1 ? 'opacity-50 cursor-not-allowed' : '' ?>">
                    <i class="fas fa-angle-left"></i>
                </button>
                
                <!-- Pages proches -->
                <?php 
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                
                for ($i = $start; $i <= $end; $i++): 
                ?>
                    <button onclick="loadPage(<?= $i ?>)" 
                            class="px-3 py-1 rounded-md <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-600 hover:bg-gray-500 text-white' ?>">
                        <?= $i ?>
                    </button>
                <?php endfor; ?>
                
                <!-- Page suivante -->
                <button onclick="loadPage(<?= $page + 1 ?>)" 
                        class="px-3 py-1 rounded-md bg-gray-600 hover:bg-gray-500 text-white <?= $page == $totalPages ? 'opacity-50 cursor-not-allowed' : '' ?>">
                    <i class="fas fa-angle-right"></i>
                </button>
                
                <!-- Dernière page -->
                <button onclick="loadPage(<?= $totalPages ?>)" 
                        class="px-3 py-1 rounded-md bg-gray-600 hover:bg-gray-500 text-white <?= $page == $totalPages ? 'opacity-50 cursor-not-allowed' : '' ?>">
                    <i class="fas fa-angle-double-right"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Fonction pour charger une page
function loadPage(page) {
    const searchTerm = document.querySelector('#searchForm input[name="search"]').value;
    const promotion = document.querySelector('#filterForm select[name="promotion"]').value;
    
    // Créer un objet avec les paramètres
    const params = {
        search: searchTerm,
        promotion: promotion,
        page: page
    };
    
    // Appeler la fonction parente ou faire une requête directe
    if (window.parent && window.parent.loadMembersPage) {
        window.parent.loadMembersPage(page, params);
    } else {
        // Fallback si pas de fonction parente
        const queryString = new URLSearchParams(params).toString();
        window.location.href = `?${queryString}`;
    }
}

// Réinitialiser les filtres
function resetFilters() {
    document.querySelector('#searchForm input[name="search"]').value = '';
    document.querySelector('#filterForm select[name="promotion"]').value = '';
    loadPage(1);
}

// Gestion des formulaires
document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    loadPage(1);
});

document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    loadPage(1);
});

// Fonctions pour les actions
function loadUserProfile(userId) {
    console.log("Voir profil:", userId);
    // window.parent.loadProfileView(userId);
}

function editUser(userId) {
    console.log("Modifier utilisateur:", userId);
    // window.parent.editUserForm(userId);
}

function confirmDelete(userId) {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce membre ?")) {
        console.log("Suppression de:", userId);
        // window.parent.deleteUser(userId);
    }
}

function viewUserPayments(userId) {
    console.log("Voir cotisations de:", userId);
    // window.parent.viewPayments(userId);
}
</script>

<?php
$stmt->close();
$stmt_count->close();
$conn->close();
?>