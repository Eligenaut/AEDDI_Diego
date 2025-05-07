<?php
session_start();

// Vérification de sécurité
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("HTTP/1.1 400 Bad Request");
    die("Requête invalide");
}
$userId = (int) $_GET['id'];

// Connexion à la base de données
$servername = "fdb1028.awardspace.net";
$username   = "4553951_eligenaut";
$password   = "jabady@55";
$dbname     = "4553951_eligenaut";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données.");
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
function esc($val) {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}
$nom  = esc($user['nom']);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil Dynamique - <?= $nom ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1', // Indigo 500
                        secondary: '#374151', // Gray 700
                        accent: '#facc15', // Yellow 400
                        dark: '#0f172a', // Slate 900
                        light: '#e2e8f0', // Slate 200
                        success: '#22c55e', // Green 500
                        error: '#dc2626', // Red 500
                        info: '#3b82f6', // Blue 500
                    },
                    fontFamily: {
                        'body': ['"Nunito Sans"', 'sans-serif'],
                        'heading': ['"Poppins"', 'sans-serif'],
                    },
                    fontSize: {
                        'sm': '0.8rem',
                        'base': '1rem',
                        'xl': '1.25rem',
                        '2xl': '1.563rem',
                        '3xl': '1.953rem',
                        '4xl': '2.441rem',
                        '5xl': '3.052rem',
                    },
                    spacing: {
                        '128': '32rem',
                        '144': '36rem',
                    },
                    borderRadius: {
                        'xl': '0.75rem',
                        '2xl': '1rem',
                        '3xl': '1.5rem',
                    },
                    boxShadow: {
                        'md': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                        'lg': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
                        'xl': '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
                    },
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #111827; /* Slate 900 */
            color: #f3f4f6; /* Slate 200 */
            font-family: 'body', sans-serif;
            height: 100vh;
            overflow: hidden;
            font-size: 1rem;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        a { text-decoration: none; color: inherit; }

        /* Animation de chargement améliorée */
        .loading-overlay-content {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(17, 24, 39, 0.9); /* Slate 900 avec opacité */
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .loading-overlay-content.active {
            display: flex;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 6px solid #6366f1; /* Indigo 500 */
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Styles pour la sidebar responsive */
        .sidebar {
            transition: all 0.3s ease-in-out;
            background: linear-gradient(145deg, #1e293b, #0f172a); /* Darker gradient */
            box-shadow: 4px 4px 10px #070c13, -4px -4px 10px #1b2433;
            border-radius: 0.5rem;
        }

        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to right, #1e293b, #0f172a); /* Darker gradient */
            z-index: 50;
            padding: 0.75rem 0;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.4);
        }

        .bottom-nav-item {
            flex: 1;
            text-align: center;
            padding: 0.75rem 0.5rem;
            transition: background-color 0.2s ease-in-out;
            cursor: pointer;
            border-radius: 0.375rem;
        }

        .bottom-nav-item:hover {
            background-color: rgba(100, 116, 139, 0.2); /* Gray 600 with opacity */
        }

        .bottom-nav-item i {
            display: block;
            margin: 0 auto 0.375rem;
            font-size: 1.5rem;
        }

        .bottom-nav-item span {
            display: block;
            font-size: 0.9rem;
        }

        /* Animation pour le contenu */
        .content-transition {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Styles pour les écrans moyens et petits */
        @media (max-width: 1024px) {
            .sidebar {
                display: none;
            }

            .bottom-nav {
                display: flex;
            }

            .main-content {
                padding-bottom: 5rem;
            }

            body {
                font-size: 1.05rem;
            }
        }

        /* Styles pour les très petits écrans */
        @media (max-width: 480px) {
            .bottom-nav-item span {
                font-size: 0.8rem;
            }

            .bottom-nav-item i {
                font-size: 1.3rem;
            }
        }

        /* Badge de notification */
        .notification-badge {
            position: absolute;
            top: -0.3rem;
            right: -0.3rem;
            background-color: #dc2626; /* Red 500 */
            color: white;
            border-radius: 50%;
            width: 1.25rem;
            height: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }

        /* Style pour le bouton de profil dans la sidebar */
        .profile-button {
            background-color: #4b5563; /* Gray 600 */
            color: #f9fafb; /* Gray 100 */
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .profile-button:hover {
            background-color: #556370; /* Darker Gray 600 */
        }

        .profile-button .initials {
            background-color: #6366f1; /* Indigo 500 */
            color: white;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .profile-button .info {
            margin-left: 0.75rem;
        }

        /* Style pour la navigation de la sidebar */
        .sidebar-nav button {
            width: 100%;
            text-align: left;
            background-color: transparent;
            color: #cbd5e1; /* Slate 300 */
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
            box-shadow: none;
        }

        .sidebar-nav button:hover,
        .sidebar-nav button.active {
            background-color: #334155; /* Gray 700 */
            color: #f9fafb; /* Gray 100 */
        }

        .sidebar-nav button i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }

        .sidebar-nav h3 {
            color: #94a3b8; /* Slate 400 */
            margin-bottom: 1rem;
            padding-left: 1rem;
            font-size: 0.9rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Style pour le contenu principal */
        .main-content {
            background-color: #1e293b; /* Gray 800 */
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        /* Style pour le contenu par défaut */
        #defaultContent {
            color: #64748b; /* Gray 500 */
        }

        #defaultContent i {
            color: #475569; /* Gray 600 */
        }
    </style>
</head>
<body class="flex flex-col h-screen">
    <div class="bg-gradient-to-r from-gray-800 to-gray-900 p-4 flex flex-col items-center flex-shrink-0 border-b border-gray-700">
        <div class="text-2xl font-heading font-bold mb-2 text-center text-white">Association des Etudiants Dynamiques de DIego</div>
        <div class="text-yellow-400 font-medium" id="dateHeure"></div>
    </div>

    <div class="flex flex-1 overflow-hidden">
        <aside class="sidebar w-64 p-4 flex-shrink-0 h-full overflow-y-auto">
            <div class="profile-button flex items-center">
                <div class="initials"><?= strtoupper(substr($nom, 0, 1)) ?></div>
                <div class="info">
                    <div class="font-semibold text-white"><?= $nom ?></div>
                    <div class="text-xs text-gray-400">Membre actif</div>
                </div>
            </div>

            <nav class="space-y-4 sidebar-nav">
                <h3 class="text-xs font-bold uppercase text-gray-500">Navigation</h3>
                <button id="btnProfil" class="active">
                    <i class="fas fa-user"></i> Profil
                </button>
                <button id="btnActivites">
                    <i class="fas fa-chart-line"></i> Activités
                </button>
                <button id="btnMembres">
                    <i class="fas fa-users"></i> Membres
                </button>
                <button id="btnMessages" class="relative">
                    <i class="fas fa-envelope"></i> Messages
                    <span class="notification-badge">3</span>
                </button>
                <button id="btnParametres">
                    <i class="fas fa-cog"></i> Paramètres
                </button>
                <div class="mt-6">
                    <a href="deconnexion.php" class="block text-center bg-secondary hover:bg-gray-600 text-white px-4 py-3 rounded-md transition duration-200 shadow-md">
                        <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                    </a>
                </div>
            </nav>
            <div class="mt-8 text-xs text-gray-500 text-center">RevenuApp V1.0.2025</div>
        </aside>

        <main id="mainContent" class="main-content flex-1 p-6 overflow-y-auto">
            <div id="defaultContent" class="text-center py-16">
                <i class="fas fa-user-circle text-7xl text-gray-600 mb-6"></i>
                <h2 class="text-3xl font-heading font-semibold text-gray-300">Bienvenue dans votre espace membre</h2>
                <p class="text-gray-500 mt-4 text-lg">Sélectionnez une option dans le menu pour commencer</p>
            </div>
        </main>
    </div>

    <nav class="bottom-nav">
        <div class="bottom-nav-item active" onclick="loadProfileView(); setActiveButton('btnProfil');">
            <i class="fas fa-user"></i>
            <span>Profil</span>
        </div>
        <div class="bottom-nav-item" onclick="loadActivitiesView(); setActiveButton('btnActivites');">
            <i class="fas fa-chart-line"></i>
            <span>Activités</span>
        </div>
        <div class="bottom-nav-item" onclick="loadMembersView(); setActiveButton('btnMembres');">
            <i class="fas fa-users"></i>
            <span>Membres</span>
        </div>
        <div class="bottom-nav-item relative" onclick="loadMessagesView(); setActiveButton('btnMessages');">
            <i class="fas fa-envelope"></i>
            <span>Messages</span>
            <span class="notification-badge">3</span>
        </div>
        <div class="bottom-nav-item" onclick="loadSettingsView(); setActiveButton('btnParametres');">
            <i class="fas fa-cog"></i>
            <span>Paramètres</span>
        </div>
    </nav>

    <div id="contentLoading" class="loading-overlay-content">
        <div class="loading-spinner"></div>
    </div>

    <script>
        const userId = <?= (int)$userId ?>;
        const mainContent = document.getElementById('mainContent');
        const defaultContent = document.getElementById('defaultContent');
        const loadingOverlay = document.getElementById('contentLoading');
        let currentView = 'profile';

        // Fonctions d'affichage du chargement améliorées
        function showLoading() {
            loadingOverlay.classList.add('active');
        }

        function hideLoading() {
            loadingOverlay.classList.remove('active');
        }

        // Charger la vue profil
        async function loadProfileView(targetUserId = null) {
            const profileUserId = targetUserId || userId;
            currentView = 'profile';

            try {
                showLoading();
                defaultContent.style.display = 'none';

                const response = await fetch(`profil_view.php?id=${profileUserId}`);
                if (!response.ok) throw new Error('Erreur réseau');

                const html = await response.text();
                mainContent.innerHTML = `
                    <div class="content-transition">
                        ${html}
                    </div>
                `;

                // Ajouter le bouton Modifier si c'est le profil de l'utilisateur connecté
                if (profileUserId === userId) {
                    const header = mainContent.querySelector('.profile-header');
                    if (header) {
                        const editBtn = document.createElement('button');
                        editBtn.className = 'bg-primary hover:bg-indigo-400 text-white px-4 py-2 rounded-md flex items-center ml-4 transition-all duration-200 shadow-md';
                        editBtn.innerHTML = '<i class="fas fa-edit mr-2"></i> Modifier';
                        editBtn.onclick = loadEditView;
                        header.appendChild(editBtn);
                    }
                }

                setActiveButton('btnProfil');
            } catch (error) {
                console.error('Erreur:', error);
                mainContent.innerHTML = `
                    <div class="bg-error/20 border-l-4 border-error text-error p-4 rounded-md shadow-lg">
                        <p class="font-bold">Erreur de chargement</p>
                        <p>Impossible de charger le profil. Veuillez réessayer.</p>
                    </div>
                `;
                defaultContent.style.display = 'block';
            } finally {
                hideLoading();
            }
        }

        // Charger la vue d'édition
        async function loadEditView() {
            currentView = 'edit';

            try {
                showLoading();
                defaultContent.style.display = 'none';

                const response = await fetch(`modifier_profil_ajax.php?id=${userId}`);
                if (!response.ok) throw new Error('Erreur réseau');

                const html = await response.text();
                mainContent.innerHTML = `
                    <div class="content-transition">
                        ${html}
                    </div>
                `;

                // Configurer le formulaire
                const form = mainContent.querySelector('form');
                if (form) {
                    form.onsubmit = function(e) {
                        e.preventDefault();
                        saveProfileChanges(form);
                    };
                }

                // Configurer le bouton Annuler
                const cancelBtn = mainContent.querySelector('#cancelEdit');
                if (cancelBtn) {
                    cancelBtn.onclick = () => loadProfileView(userId);
                }
            } catch (error) {
                console.error('Erreur:', error);
                mainContent.innerHTML = `
                    <div class="bg-error/20 border-l-4 border-error text-error p-4 rounded-md shadow-lg">
                        <p class="font-bold">Erreur de chargement</p>
                        <p>Impossible de charger l'éditeur. Veuillez réessayer.</p>
                    </div>
                `;
                defaultContent.style.display = 'block';
            } finally {
                hideLoading();
            }
        }

        // Charger la vue des activités
        async function loadActivitiesView() {
            currentView = 'activities';

            try {
                showLoading();
                defaultContent.style.display = 'none';

                const response = await fetch(`voir_activite_partial.php?id=${userId}`);
                if (!response.ok) throw new Error('Erreur réseau');

                const html = await response.text();
                mainContent.innerHTML = `
                    <div class="content-transition">
                        ${html}
                    </div>
                `;

                setActiveButton('btnActivites');
            } catch (error) {
                console.error('Erreur:', error);
                mainContent.innerHTML = `
                    <div class="bg-error/20 border-l-4 border-error text-error p-4 rounded-md shadow-lg">
                        <p class="font-bold">Erreur de chargement</p>
                        <p>Impossible de charger les activités. Veuillez réessayer.</p>
                    </div>
                `;
                defaultContent.style.display = 'block';
            } finally {
                hideLoading();
            }
        }

        // Charger la vue des membres
        async function loadMembersView() {
            currentView = 'members';

            try {
                showLoading();
                defaultContent.style.display = 'none';

                const response = await fetch(`voir_membre_utilisateur_partial.php`);
                if (!response.ok) throw new Error('Erreur réseau');

                const html = await response.text();
                mainContent.innerHTML = `
                    <div class="content-transition">
                        ${html}
                    </div>
                `;

                setupMembersPagination();
                setActiveButton('btnMembres');
            } catch (error) {
                console.error('Erreur:', error);
                mainContent.innerHTML = `
                    <div class="bg-error/20 border-l-4 border-error text-error p-4 rounded-md shadow-lg">
                        <p class="font-bold">Erreur de chargement</p>
                        <p>Impossible de charger la liste des membres. Veuillez réessayer.</p>
                    </div>
                `;
                defaultContent.style.display = 'block';
            } finally {
                hideLoading();
            }
        }

        // Charger la vue des messages
        async function loadMessagesView() {
            currentView = 'messages';

            try {
                showLoading();
                defaultContent.style.display = 'none';

                // Simuler un chargement de messages
                mainContent.innerHTML = `
                    <div class="content-transition p-6">
                        <h2 class="text-2xl font-heading font-bold text-white mb-6">Messages</h2>
                        <div class="bg-gray-800 rounded-md p-6 shadow-lg">
                            <p class="text-gray-400">Fonctionnalité de messages en cours de développement.</p>
                        </div>
                    </div>
                `;

                setActiveButton('btnMessages');
            } catch (error) {
                console.error('Erreur:', error);
                mainContent.innerHTML = `
                    <div class="bg-error/20 border-l-4 border-error text-error p-4 rounded-md shadow-lg">
                        <p class="font-bold">Erreur de chargement</p>
                        <p>Impossible de charger les messages. Veuillez réessayer.</p>
                    </div>
                `;
                defaultContent.style.display = 'block';
            } finally {
                hideLoading();
            }
        }

        // Charger la vue des paramètres
        async function loadSettingsView() {
            currentView = 'settings';

            try {
                showLoading();
                defaultContent.style.display = 'none';

                // Simuler un chargement des paramètres
                mainContent.innerHTML = `
                    <div class="content-transition p-6">
                        <h2 class="text-2xl font-heading font-bold text-white mb-6">Paramètres</h2>
                        <div class="bg-gray-800 rounded-md p-6 shadow-lg">
                            <p class="text-gray-400">Fonctionnalité de paramètres en cours de développement.</p>
                        </div>
                    </div>
                `;

                setActiveButton('btnParametres');
            } catch (error) {
                console.error('Erreur:', error);
                mainContent.innerHTML = `
                    <div class="bg-error/20 border-l-4 border-error text-error p-4 rounded-md shadow-lg">
                        <p class="font-bold">Erreur de chargement</p>
                        <p>Impossible de charger les paramètres. Veuillez réessayer.</p>
                    </div>
                `;
                defaultContent.style.display = 'block';
            } finally {
                hideLoading();
            }
        }

        // Configurer la pagination pour les membres
        function setupMembersPagination() {
            document.querySelectorAll('.pagination-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = this.getAttribute('data-page');
                    loadMembersPage(page);
                });
            });

            const searchForm = document.getElementById('searchForm');
            const filterForm = document.getElementById('filterForm');

            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    loadMembersPage(1, formData);
                });
            }

            if (filterForm) {
                filterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    loadMembersPage(1, formData);
                });
            }
        }

        // Charger une page spécifique des membres avec filtres
        async function loadMembersPage(page = 1, formData = null) {
            try {
                showLoading();
                let url = `voir_membre_utilisateur_partial.php?page=${page}`;

                if (formData) {
                    const params = new URLSearchParams(formData);
                    url += `&${params.toString()}`;
                }

                const response = await fetch(url);
                if (!response.ok) throw new Error('Erreur réseau');

                const html = await response.text();
                mainContent.innerHTML = `
                    <div class="content-transition">
                        ${html}
                    </div>
                `;

                setupMembersPagination();
            } catch (error) {
                console.error('Erreur:', error);
                mainContent.innerHTML = `
                    <div class="bg-error/20 border-l-4 border-error text-error p-4 rounded-md shadow-lg">
                        <p class="font-bold">Erreur de chargement</p>
                        <p>Impossible de charger la page demandée. Veuillez réessayer.</p>
                    </div>
                `;
            } finally {
                hideLoading();
            }
        }

        // Sauvegarder les modifications du profil
        async function saveProfileChanges(form) {
            const saveBtn = form.querySelector('button[type="submit"]');
            const originalText = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Enregistrement...';
            showLoading();

            try {
                const formData = new FormData(form);
                formData.append('id', userId);

                const response = await fetch('modifier_profil_ajax.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showMessage('Modifications enregistrées avec succès!', 'success');
                    loadProfileView();
                } else {
                    showMessage(data.error || 'Erreur lors de la mise à jour', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showMessage('Une erreur est survenue lors de la sauvegarde', 'error');
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
                hideLoading();
            }
        }

        // Afficher un message temporaire
        function showMessage(message, color) {
            const alert = document.createElement('div');
            alert.className = `bg-${color}-500 text-white p-4 rounded-md mb-4 shadow-lg content-transition`;
            alert.innerHTML = message;
            mainContent.prepend(alert);
            setTimeout(() => {
                alert.classList.add('opacity-0', 'transition-all', 'duration-300');
                setTimeout(() => alert.remove(), 300);
            }, 3000);
        }

        // Affichage de la date et heure
        function updateDateHeure() {
            const mois = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
            const jours = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];

            const maintenant = new Date();
            const jourSemaine = jours[maintenant.getDay()];
            const jour = String(maintenant.getDate()).padStart(2, '0');
            const moisNom = mois[maintenant.getMonth()];
            const annee = maintenant.getFullYear();
            const heure = String(maintenant.getHours()).padStart(2, '0');
            const minute = String(maintenant.getMinutes()).padStart(2, '0');
            const seconde = String(maintenant.getSeconds()).padStart(2, '0');

            document.getElementById('dateHeure').textContent = `${jourSemaine} ${jour} ${moisNom} ${annee} - ${heure}:${minute}:${seconde}`;
        }

        // Mettre en surbrillance le bouton actif
        function setActiveButton(buttonId) {
            // Pour la sidebar desktop
            document.querySelectorAll('.sidebar-nav button').forEach(btn => {
                btn.classList.remove('active');
            });

            if (buttonId) {
                const btn = document.getElementById(buttonId);
                if (btn) {
                    btn.classList.add('active');
                }
            }

            // Pour la bottom-nav mobile
            document.querySelectorAll('.bottom-nav-item').forEach(item => {
                item.classList.remove('text-primary', 'bg-gray-700/30');
            });

            const buttonsMap = {
                'btnProfil': 0,
                'btnActivites': 1,
                'btnMembres': 2,
                'btnMessages': 3,
                'btnParametres': 4
            };

            if (buttonId in buttonsMap) {
                const index = buttonsMap[buttonId];
                document.querySelectorAll('.bottom-nav-item')[index]?.classList.add('text-primary', 'bg-gray-700/30');
            }
        }

        // Initialisation
        window.addEventListener('DOMContentLoaded', () => {
            // Vérifier que les éléments existent avant d'ajouter des écouteurs
            if (document.getElementById('btnProfil')) {
                document.getElementById('btnProfil').addEventListener('click', () => {
                    loadProfileView();
                    setActiveButton('btnProfil');
                });
            }

            if (document.getElementById('btnActivites')) {
                document.getElementById('btnActivites').addEventListener('click', () => {
                    loadActivitiesView();
                    setActiveButton('btnActivites');
                });
            }

            if (document.getElementById('btnMembres')) {
                document.getElementById('btnMembres').addEventListener('click', () => {
                    loadMembersView();
                    setActiveButton('btnMembres');
                });
            }

            if (document.getElementById('btnMessages')) {
                document.getElementById('btnMessages').addEventListener('click', () => {
                    loadMessagesView();
                    setActiveButton('btnMessages');
                });
            }

            if (document.getElementById('btnParametres')) {
                document.getElementById('btnParametres').addEventListener('click', () => {
                    loadSettingsView();
                    setActiveButton('btnParametres');
                });
            }

            // Mettre en surbrillance le bouton actif initial
            setActiveButton('btnProfil');

            // Démarrer le chargement initial
            loadProfileView();

            // Mettre à jour l'heure et la date
            setInterval(updateDateHeure, 1000);
            updateDateHeure();

            // Gérer le redimensionnement de la fenêtre
            window.addEventListener('resize', handleResize);
            handleResize();
        });

        // Gérer le redimensionnement de la fenêtre
        function handleResize() {
            // Vous pouvez ajouter ici une logique supplémentaire si nécessaire
        }
    </script>
</body>
</html>