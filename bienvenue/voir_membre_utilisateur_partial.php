<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['role'])) {
    die(json_encode(['error' => 'Non autorisé']));
}

// Connexion à la base de données
$servername = "fdb1028.awardspace.net";
$username = "4553951_eligenaut";
$password = "jabady@55";
$dbname = "4553951_eligenaut";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données.");
}

// Récupérer les paramètres
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$selectedPromotion = isset($_GET['promotion']) ? $_GET['promotion'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Requête pour compter le total
$sql_count = "SELECT COUNT(*) as total FROM utilisateurs WHERE (nom LIKE ? OR prenom LIKE ?)";
$params = ["%$searchTerm%", "%$searchTerm%"];
$types = "ss";

if (!empty($selectedPromotion)) {
    $sql_count .= " AND promotion = ?";
    $params[] = $selectedPromotion;
    $types .= "s";
}

$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param($types, ...$params);
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$row = $count_result->fetch_assoc();
$totalResults = $row['total'];
$totalPages = ceil($totalResults / $limit);

// Requête principale
$sql = "SELECT id, nom, prenom, photo, etablissement, parcours, promotion, telephone 
        FROM utilisateurs 
        WHERE (nom LIKE ? OR prenom LIKE ?)";
        
if (!empty($selectedPromotion)) {
    $sql .= " AND promotion = ?";
}

$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
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
                <input type="hidden" name="promotion" value="<?= htmlspecialchars($selectedPromotion) ?>">
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
                <input type="hidden" name="search" value="<?= htmlspecialchars($searchTerm) ?>">
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
                                <button onclick="loadUserProfile(<?= $user['id'] ?>)" 
                                        class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-1 rounded text-sm">
                                    Voir profil
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-8 text-gray-400">
                Aucun membre trouvé.
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="mt-4 pt-4 border-t border-gray-600 flex-shrink-0">
        <div class="flex justify-between items-center">
            <div class="text-gray-400">
                Page <?= $page ?> sur <?= $totalPages ?> - <?= $totalResults ?> membres
            </div>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                    <button onclick="loadMembersPage(<?= $page-1 ?>)" 
                            class="px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded">
                        <i class="fas fa-chevron-left mr-1"></i> Précédent
                    </button>
                <?php endif; ?>
                
                <?php if ($page < $totalPages): ?>
                    <button onclick="loadMembersPage(<?= $page+1 ?>)" 
                            class="px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded">
                        Suivant <i class="fas fa-chevron-right ml-1"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Fonction pour charger un profil utilisateur
function loadUserProfile(userId) {
    if (window.parent && window.parent.loadProfileView) {
        window.parent.loadProfileView(userId);
        window.parent.setActiveButton('btnProfil');
    }
}

// Fonction pour charger une page des membres
function loadMembersPage(page = 1) {
    const searchForm = document.getElementById('searchForm');
    const formData = new FormData(searchForm);
    
    if (window.parent && window.parent.loadMembersPage) {
        window.parent.loadMembersPage(page, formData);
    }
}
</script>

<?php
$stmt->close();
$stmt_count->close();
$conn->close();
?>