<?php
ob_start();

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adresse_mail'])) {
    $adresse_mail = $_POST['adresse_mail'];
    $mot_de_passe = $_POST['mot_de_passe'];

    // Vérification des identifiants admin
    if ($adresse_mail === "admin@admin.com" && $mot_de_passe === "admin123") {
        $_SESSION['role'] = 'admin';
        ob_end_clean();
        header("Location: ../administrateur/administrateur.php");
        exit();
    }

    // Connexion DB et vérification
    $servername = "fdb1028.awardspace.net";
    $username = "4553951_eligenaut";
    $password = "jabady@55";
    $dbname = "4553951_eligenaut";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Erreur de connexion à la base de données : " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE adresse_mail = ? AND mot_de_passe = ?");
    $stmt->bind_param("ss", $adresse_mail, $mot_de_passe);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['role'] = 'user';
        $_SESSION['user_id'] = $user['id'];
        ob_end_clean();
        header("Location: ../bienvenue/bienvenue.php?id=" . $user['id']);
        exit();
    } else {
        $error_message = "Adresse mail ou mot de passe incorrect.";
    }

    $stmt->close();
    $conn->close();
}
?>

<div class="auth-form-wrapper">
    <h2 class="auth-title">Connexion</h2>
    <?php if (!empty($error_message)): ?>
        <div class="error-message" style="color: #e74c3c; margin-bottom: 1rem;"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <form id="loginForm" action="page_formulaire.php?form=login" method="post">
        <div class="auth-input-group">
            <label for="adresse_mail" class="auth-input-label">Adresse mail</label>
            <input type="email" id="adresse_mail" name="adresse_mail" class="auth-input" placeholder="Votre adresse mail" required>
        </div>
        <div class="auth-input-group">
            <label for="mot_de_passe" class="auth-input-label">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" class="auth-input" placeholder="Votre mot de passe" required>
        </div>
        <button type="submit" class="auth-button">Se connecter</button>
    </form>
    
    <div class="auth-links">
        <span>Pas encore inscrit ?</span>
        <a href="page_formulaire.php?form=register" class="auth-link">Créer un compte</a>
    </div>
</div>

<style>
    /* Les mêmes styles que dans inscription.php */
    .auth-form-wrapper {
        background: #34495e;
        padding: 2.5rem;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }
    
    .auth-title {
        color: #ecf0f1;
        font-size: 2.2rem;
        margin-bottom: 2rem;
        text-align: center;
    }
    
    .auth-input-group {
        margin-bottom: 1.5rem;
    }
    
    .auth-input-label {
        display: block;
        color: #bdc3c7;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    
    .auth-input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #7f8c8d;
        border-radius: 10px;
        font-size: 1rem;
        background: #2c3e50;
        color: #ecf0f1;
        transition: border-color 0.3s ease;
    }
    
    .auth-input:focus {
        border-color: #3498db;
        outline: none;
    }
    
    .auth-button {
        width: 100%;
        padding: 12px;
        background: #3498db;
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1.1rem;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    
    .auth-button:hover {
        background: #2980b9;
    }
    
    .auth-links {
        text-align: center;
        margin-top: 1.5rem;
    }
    
    .auth-link {
        color: #3498db;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }
    
    .auth-link:hover {
        color: #2980b9;
    }
</style>
<?php
ob_end_flush();
?>