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

// Récupérer toutes les activités et cotisations
$sql = "SELECT ac.*, 
               COUNT(uc.id_utilisateur) AS total_utilisateurs,
               SUM(CASE WHEN uc.etat_paiement = 'payé' THEN 1 ELSE 0 END) AS payes
        FROM activites_cotisations ac
        LEFT JOIN utilisateurs_cotisations uc ON ac.id = uc.id_cotisation
        GROUP BY ac.id
        ORDER BY ac.date_debut DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activités & Cotisations</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .table-container {
            height: calc(100vh - 200px);
            overflow-y: auto;
        }
        
        table {
            display: flex;
            flex-direction: column;
            width: 100%;
        }
        
        thead, tbody {
            display: block;
            width: 100%;
        }
        
        thead tr {
            display: flex;
            width: 100%;
        }
        
        tbody {
            overflow-y: auto;
            height: calc(100vh - 250px);
        }
        
        tbody tr {
            display: flex;
            width: 100%;
        }
        
        th, td {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #111827; /* bg-gray-900 */
        }
    </style>
</head>
<body class="bg-gray-800 text-white">
<div class="container mx-auto p-4">
    <div class="bg-gray-700 p-6 rounded-lg h-full flex flex-col">
        <h2 class="text-2xl font-bold mb-6 text-white">Activités & Cotisations</h2>

        <!-- Conteneur pour le formulaire d'ajout -->
        <div id="formContainer" class="mb-4"></div>

        <div class="table-container rounded-lg">
            <table class="w-full text-sm text-left text-white bg-gray-800 border border-gray-700">
                <thead class="text-xs uppercase text-gray-300">
                    <tr>
                        <th class="px-4 py-3 sticky-header">Type</th>
                        <th class="px-4 py-3 sticky-header">Nom</th>
                        <th class="px-4 py-3 sticky-header">Description</th>
                        <th class="px-4 py-3 sticky-header">Montant</th>
                        <th class="px-4 py-3 sticky-header">Date début</th>
                        <th class="px-4 py-3 sticky-header">Date fin</th>
                        <th class="px-4 py-3 sticky-header">Statut</th>
                        <th class="px-4 py-3 sticky-header">Progression</th>
                        <th class="px-4 py-3 sticky-header">Payeurs</th>
                        <th class="px-4 py-3 sticky-header">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-600">
                        <td class="px-4 py-3"><?= htmlspecialchars($row['type']) ?></td>
                        <td class="px-4 py-3"><?= htmlspecialchars($row['titre']) ?></td>
                        <td class="px-4 py-3"><?= htmlspecialchars($row['description'] ?? '-') ?></td>
                        <td class="px-4 py-3"><?= $row['montant'] !== NULL ? $row['montant'] . " Ar" : '-' ?></td>
                        <td class="px-4 py-3"><?= htmlspecialchars($row['date_debut']) ?></td>
                        <td class="px-4 py-3"><?= htmlspecialchars($row['date_fin'] ?? '-') ?></td>
                        <td class="px-4 py-3"><?= htmlspecialchars($row['statut']) ?></td>
                        <td class="px-4 py-3">
                            <?php if ($row['type'] === 'cotisation'): ?>
                                <?php
                                $total_utilisateurs = $row['total_utilisateurs'];
                                $payes = $row['payes'];
                                $pourcentage = ($total_utilisateurs > 0) ? round(($payes / $total_utilisateurs) * 100, 2) : 0;
                                ?>
                                <div class="w-full bg-gray-600 rounded-full h-2.5">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?= $pourcentage ?>%"></div>
                                </div>
                                <span class="text-sm"><?= $pourcentage ?>%</span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <?php if ($row['type'] === 'cotisation'): ?>
                                <?= $row['payes'] ?>/<?= $row['total_utilisateurs'] ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex space-x-2">
                                <button class="p-2 bg-yellow-600 hover:bg-yellow-500 rounded text-white" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="p-2 bg-red-600 hover:bg-red-500 rounded text-white" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>