<?php
session_start();

// Vérification du rôle admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../connexion/page_formulaire.php");
    exit();
}

// Connexion à la base de données
$servername = "fdb1028.awardspace.net";
$username = "4553951_eligenaut";
$password = "jabady@55";
$dbname = "4553951_eligenaut";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// Récupération des statistiques
$stats = $conn->query("
    SELECT 
        COUNT(*) AS total_cotisations,
        SUM(CASE WHEN etat_paiement = 'payé' THEN 1 ELSE 0 END) AS cotisations_payees
    FROM utilisateurs_cotisations
")->fetch_assoc();

// Récupération des messages
$messages = $conn->query("
    SELECT m.*, u.prenom, u.nom 
    FROM messages m
    JOIN utilisateurs u ON m.sender_id = u.id
    WHERE destinataire_id = 0
    ORDER BY created_at DESC
    LIMIT 5
");

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --dark: #1b263b;
            --light: #f8f9fa;
            --card: #2b2d42;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: #0d1b2a;
            color: var(--light);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1b263b 0%, #0d1b2a 100%);
            height: 100vh;
            position: fixed;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
            z-index: 50;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .content {
            margin-left: 280px;
            min-height: 100vh;
            transition: margin 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card {
            background: var(--card);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .nav-item {
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        
        .nav-item:hover {
            background: rgba(67, 97, 238, 0.1);
            border-left: 3px solid var(--primary);
        }
        
        .nav-item.active {
            background: rgba(67, 97, 238, 0.2);
            border-left: 3px solid var(--primary);
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(13, 27, 42, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }
        
        .loading-overlay.active {
            opacity: 1;
            pointer-events: all;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(255,255,255,0.1);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .content-view {
            opacity: 0;
            transform: translateY(10px);
            animation: fadeIn 0.4s forwards;
        }
        
        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .btn-primary {
            background: var(--primary);
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            background: var(--secondary);
            transform: translateY(-1px);
        }
        
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .content {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
        }
    </style>
</head>
<body class="flex">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="p-6 border-b border-gray-700">
            <h1 class="text-2xl font-bold text-white flex items-center">
                <i class="fas fa-shield-alt mr-3 text-blue-400"></i>
                <span>Admin Panel</span>
            </h1>
        </div>
        <nav class="p-4 space-y-1">
            <button onclick="loadView('dashboard')" class="w-full text-left text-white px-4 py-3 rounded-lg flex items-center nav-item active">
                <i class="fas fa-tachometer-alt mr-3"></i>
                Tableau de bord
            </button>
            <button onclick="loadView('members')" class="w-full text-left text-white px-4 py-3 rounded-lg flex items-center nav-item">
                <i class="fas fa-users mr-3"></i>
                Gestion des membres
            </button>
            <button onclick="loadView('activities')" class="w-full text-left text-white px-4 py-3 rounded-lg flex items-center nav-item">
                <i class="fas fa-tasks mr-3"></i>
                Activités & Cotisations
            </button>
            <button onclick="loadView('messages')" class="w-full text-left text-white px-4 py-3 rounded-lg flex items-center nav-item">
                <i class="fas fa-envelope mr-3"></i>
                Messagerie
            </button>
            <button onclick="loadView('settings')" class="w-full text-left text-white px-4 py-3 rounded-lg flex items-center nav-item">
                <i class="fas fa-cog mr-3"></i>
                Paramètres
            </button>
            <a href="../connexion/page_formulaire.php?logout=true" class="w-full text-left text-white px-4 py-3 rounded-lg flex items-center nav-item mt-8 bg-red-500 hover:bg-red-600">
                <i class="fas fa-sign-out-alt mr-3"></i>
                Déconnexion
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="content w-full">
        <!-- Top Bar -->
        <header class="bg-gray-800 p-4 flex justify-between items-center shadow-md">
            <button id="mobileMenuBtn" class="text-white md:hidden">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-300">
                    <i class="fas fa-user-shield mr-1"></i>
                    <?= $_SESSION['prenom'] ?? 'Admin' ?>
                </div>
            </div>
        </header>

        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="loading-overlay">
            <div class="spinner"></div>
        </div>
        
        <!-- Dynamic Content -->
        <main id="contentContainer" class="p-6 content-view">
            <!-- Content will be loaded here dynamically -->
        </main>
    </div>

    <script>
        // Variables globales
        let currentView = 'dashboard';
        let chartInstance = null;
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', () => {
            // Charger le dashboard par défaut
            loadView('dashboard');
            
            // Gestion du menu mobile
            document.getElementById('mobileMenuBtn').addEventListener('click', () => {
                document.querySelector('.sidebar').classList.toggle('active');
            });
            
            // Mettre à jour l'heure toutes les secondes
            setInterval(updateDateTime, 1000);
        });
        
        // Fonctions de base
        function toggleLoading(show) {
            const loader = document.getElementById('loadingOverlay');
            show ? loader.classList.add('active') : loader.classList.remove('active');
        }
        
        function setActiveNav(view) {
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('onclick')?.includes(view)) {
                    item.classList.add('active');
                }
            });
        }
        
        function updateDateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            };
            const dateElements = document.querySelectorAll('.current-datetime');
            if (dateElements) {
                dateElements.forEach(el => {
                    el.textContent = now.toLocaleDateString('fr-FR', options);
                });
            }
        }
        
        // Fonction principale de chargement des vues
        async function loadView(view) {
            currentView = view;
            setActiveNav(view);
            toggleLoading(true);
            
            try {
                let content = '';
                
                switch(view) {
                    case 'dashboard':
                        content = await loadDashboardView();
                        break;
                    case 'members':
                        content = await loadMembersView();
                        break;
                    case 'activities':
                        content = await loadActivitiesView();
                        break;
                    case 'messages':
                        content = await loadMessagesView();
                        break;
                    case 'settings':
                        content = await loadSettingsView();
                        break;
                    default:
                        content = '<div class="text-center py-10 text-xl">Vue non trouvée</div>';
                }
                
                document.getElementById('contentContainer').innerHTML = content;
                document.getElementById('contentContainer').classList.add('content-view');
                
                // Initialiser les composants spécifiques à la vue
                initViewComponents(view);
                
            } catch (error) {
                console.error('Erreur de chargement:', error);
                document.getElementById('contentContainer').innerHTML = `
                    <div class="bg-red-500 text-white p-4 rounded-lg">
                        Erreur de chargement: ${error.message}
                    </div>
                `;
            } finally {
                toggleLoading(false);
                // Fermer le sidebar sur mobile après sélection
                document.querySelector('.sidebar').classList.remove('active');
            }
        }
        
        // Fonctions de chargement des vues
        async function loadDashboardView() {
            const stats = <?= json_encode($stats) ?>;
            const messages = <?= json_encode($messages->fetch_all(MYSQLI_ASSOC)) ?>;
            
            return `
                <div class="space-y-6">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-bold">Tableau de bord</h1>
                        <div class="text-blue-300 current-datetime"></div>
                    </div>
                    
                    <!-- Cartes de statistiques -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="card p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400">Total cotisations</p>
                                    <p class="text-3xl font-bold">${stats.total_cotisations}</p>
                                </div>
                                <div class="bg-blue-500/10 p-3 rounded-full">
                                    <i class="fas fa-wallet text-blue-400 text-xl"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400">Cotisations payées</p>
                                    <p class="text-3xl font-bold text-green-400">${stats.cotisations_payees}</p>
                                </div>
                                <div class="bg-green-500/10 p-3 rounded-full">
                                    <i class="fas fa-check-circle text-green-400 text-xl"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-400">Taux de paiement</p>
                                    <p class="text-3xl font-bold">
                                        ${stats.total_cotisations > 0 ? 
                                            Math.round((stats.cotisations_payees / stats.total_cotisations) * 100) : 0}%
                                    </p>
                                </div>
                                <div class="bg-purple-500/10 p-3 rounded-full">
                                    <i class="fas fa-percent text-purple-400 text-xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Graphique -->
                    <div class="card p-6">
                        <h2 class="text-xl font-semibold mb-4">Statistiques des paiements</h2>
                        <div class="h-80">
                            <canvas id="paymentChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Derniers messages -->
                    <div class="card p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-semibold">Derniers messages</h2>
                            <button class="btn-primary px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-plus mr-2"></i> Nouveau message
                            </button>
                        </div>
                        
                        <div class="space-y-4">
                            ${messages.map(msg => `
                                <div class="bg-gray-700/50 p-4 rounded-lg border-l-4 border-blue-400">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium">${msg.prenom} ${msg.nom}</p>
                                            <p class="text-sm text-gray-400">${new Date(msg.created_at).toLocaleString('fr-FR')}</p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button class="text-blue-400 hover:text-blue-300">
                                                <i class="fas fa-reply"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="mt-2 text-gray-300">${msg.message}</p>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `;
        }
        
async function loadMembersView() {
        return `
            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold">Gestion des membres</h1>
                    <button onclick="loadMemberForm()" class="btn-primary px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i> Ajouter membre
                    </button>
                </div>
                <div id="membersContainer">
                    ${await loadMembersContent()}
                </div>
            </div>
        `;
    }

    async function loadMembersContent(page = 1, filters = {}) {
        try {
            const params = new URLSearchParams({
                page: page,
                ...filters
            });
            
            const response = await fetch(`voir_membre_admin_partial.php?${params.toString()}`);
            return await response.text();
        } catch (error) {
            console.error('Error:', error);
            return `
                <div class="bg-red-500/20 border border-red-500 text-red-300 p-6 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
                        <h2 class="text-xl font-bold">Erreur de chargement</h2>
                    </div>
                    <p class="mt-2">${error.message}</p>
                </div>
            `;
        }
    }

    // [Le reste de vos fonctions existantes]

    function initViewComponents(view) {
        switch(view) {
            case 'dashboard':
                initDashboardCharts();
                break;
            case 'members':
                setupMembersEvents();
                break;
            case 'activities':
                initActivityEvents();
                break;
        }
        updateDateTime();
    }

    function setupMembersEvents() {
        // Déléguer les événements au conteneur parent
        document.getElementById('membersContainer').addEventListener('click', function(e) {
            // Pagination
            if (e.target.closest('[data-page]')) {
                e.preventDefault();
                const page = e.target.closest('[data-page]').getAttribute('data-page');
                const search = document.querySelector('#searchForm input[name="search"]')?.value || '';
                const promotion = document.querySelector('#filterForm select[name="promotion"]')?.value || '';
                refreshMembersContent(page, { search, promotion });
            }
            
            // Réinitialisation des filtres
            if (e.target.closest('#resetFilters')) {
                e.preventDefault();
                refreshMembersContent(1, { search: '', promotion: '' });
            }
        });

        // Formulaires
        const searchForm = document.getElementById('searchForm');
        const filterForm = document.getElementById('filterForm');

        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const search = this.querySelector('input[name="search"]').value;
                refreshMembersContent(1, { search });
            });
        }

        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const promotion = this.querySelector('select[name="promotion"]').value;
                refreshMembersContent(1, { promotion });
            });
        }
    }

    async function refreshMembersContent(page, filters) {
        const container = document.getElementById('membersContainer');
        container.innerHTML = `
            <div class="flex justify-center items-center h-40">
                <i class="fas fa-spinner fa-spin text-2xl text-blue-400"></i>
            </div>
        `;
        
        container.innerHTML = await loadMembersContent(page, filters);
    }

        async function loadActivitiesView() {
            try {
                const response = await fetch('voir_activite_partial.php');
                const content = await response.text();
                return `
                    <div class="space-y-6">
                        <div class="flex justify-between items-center">
                            <h1 class="text-2xl font-bold">Gestion des activités</h1>
                            <button onclick="loadActivityForm()" class="btn-primary px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-plus mr-2"></i> Ajouter
                            </button>
                        </div>
                        ${content}
                    </div>
                `;
            } catch (error) {
                return errorView('activities');
            }
        }
        
        async function loadActivityForm() {
            toggleLoading(true);
            try {
                const response = await fetch('ajouter_activite.php');
                const form = await response.text();
                
                document.getElementById('contentContainer').innerHTML = `
                    <div class="max-w-3xl mx-auto content-view">
                        <div class="flex items-center mb-6">
                            <button onclick="loadView('activities')" class="mr-4 text-blue-400 hover:text-blue-300">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <h1 class="text-2xl font-bold">Ajouter une activité</h1>
                        </div>
                        ${form}
                    </div>
                `;
            } catch (error) {
                document.getElementById('contentContainer').innerHTML = errorView('activity form');
            } finally {
                toggleLoading(false);
            }
        }
        
        async function loadMessagesView() {
            return `
                <div class="space-y-6">
                    <h1 class="text-2xl font-bold">Messagerie administrative</h1>
                    <div class="card p-6">
                        <p>Interface de messagerie à implémenter</p>
                    </div>
                </div>
            `;
        }
        
        async function loadSettingsView() {
            return `
                <div class="space-y-6">
                    <h1 class="text-2xl font-bold">Paramètres du système</h1>
                    <div class="card p-6">
                        <p>Panel de configuration à implémenter</p>
                    </div>
                </div>
            `;
        }
        
        function errorView(context) {
            return `
                <div class="bg-red-500/20 border border-red-500 text-red-300 p-6 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
                        <h2 class="text-xl font-bold">Erreur de chargement</h2>
                    </div>
                    <p class="mt-2">Impossible de charger les données ${context}.</p>
                    <button onclick="loadView('${currentView}')" class="mt-4 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-sync-alt mr-2"></i> Réessayer
                    </button>
                </div>
            `;
        }
             
        // Initialisation des composants spécifiques
        function initViewComponents(view) {
            switch(view) {
                case 'dashboard':
                    initDashboardCharts();
                    break;
                case 'members':
                    break;
                case 'activities':
                    initActivityEvents();
                    break;
            }
            
            updateDateTime();
        }
        
        function initDashboardCharts() {
            const ctx = document.getElementById('paymentChart').getContext('2d');
            
            if (chartInstance) {
                chartInstance.destroy();
            }
            
            chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
                    datasets: [{
                        label: 'Cotisations payées',
                        data: [12, 19, 3, 5, 2, 3, 15, 8, 7, 10, 6, 9],
                        backgroundColor: '#4361ee',
                        borderRadius: 6
                    }, {
                        label: 'Cotisations en attente',
                        data: [5, 3, 8, 2, 4, 6, 3, 2, 5, 1, 4, 2],
                        backgroundColor: '#4cc9f0',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: '#f8f9fa'
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(255,255,255,0.1)'
                            },
                            ticks: {
                                color: '#f8f9fa'
                            }
                        },
                        y: {
                            grid: {
                                color: 'rgba(255,255,255,0.1)'
                            },
                            ticks: {
                                color: '#f8f9fa'
                            }
                        }
                    }
                }
            });
        }
        
        function initActivityEvents() {
            // Événements pour la gestion des activités
            document.querySelectorAll('.activity-action').forEach(btn => {
                btn.addEventListener('click', function() {
                    const action = this.dataset.action;
                    const activityId = this.dataset.id;
                    
                    // Implémenter les actions (éditer, supprimer, etc.)
                    console.log(`${action} activity ${activityId}`);
                });
            });
        }
    </script>
</body>
</html>