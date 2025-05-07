<?php
session_start();
session_destroy(); // Détruit toutes les données de session
header("Location: ../connexion/page_formulaire.php"); // Redirige vers la page de connexion
exit();
?>
