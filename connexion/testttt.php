<?php
session_start();

// Déterminer le formulaire initial
$initial_form = isset($_GET['form']) && $_GET['form'] === 'register' ? 'register' : 'login';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Bienvenue</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* [Conserver tous les styles CSS initiaux] */
    </style>
</head>
<body>
    <div id="app">
        <auth-container initial-form="<?= htmlspecialchars($initial_form) ?>"></auth-container>
    </div>

    <!-- Charger Vue.js -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <!-- Charger notre application -->
    <script src="app.js"></script>
</body>
</html>