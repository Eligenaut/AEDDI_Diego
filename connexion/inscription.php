<?php
session_start();

$error_message = ""; // Message d'erreur initialement vide

// Traitement de l'inscription
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $adresse_mail = $_POST['adresse_mail'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $conf_mot_de_passe = $_POST['conf_mot_de_passe'];

    // Empêcher l'utilisation de l'adresse mail de l'administrateur
    if ($adresse_mail === "admin@admin.com") {
        $error_message = "Cette adresse mail est réservée pour l'administrateur. Veuillez utiliser une autre adresse.";
    } elseif ($mot_de_passe !== $conf_mot_de_passe) {
        $error_message = "Les mots de passe ne correspondent pas.";
    } else {
        // Connexion à la base de données
        $servername = "fdb1028.awardspace.net";
        $username = "4553951_eligenaut";
        $password = "jabady@55";
        $dbname = "4553951_eligenaut";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Erreur de connexion à la base de données : " . $conn->connect_error);
        }

        // Vérifier si l'adresse mail existe déjà
        $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE adresse_mail = ?");
        $stmt->bind_param("s", $adresse_mail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Cette adresse mail est déjà utilisée. Veuillez en choisir une autre.";
        } else {
            // Récupérer les autres données du formulaire
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $lieux_naissance = $_POST['lieux_naissance'];
            $date_naissance = $_POST['date_naissance'];
            $cin = $_POST['cin'];
            $date_delivrance = $_POST['date_delivrance'];
            $etablissement = $_POST['etablissement'];
            $parcours = $_POST['parcours'];
            $niv_etude = $_POST['niv_etude'];
            $promotion = isset($_POST['promotion']) ? $_POST['promotion'] : "";
            $telephone = $_POST['telephone'];

            // Vérifier la longueur du téléphone
            if (strlen($telephone) > 10) {
                $error_message = "Le numéro de téléphone ne doit pas dépasser 10 caractères.";
            } else {
                $photo = $_FILES['photo']['name'];
                $photo_tmp = $_FILES['photo']['tmp_name'];
                $photo_path = "../uploads/" . basename($photo);

                // Télécharger la photo
                if (move_uploaded_file($photo_tmp, $photo_path)) {
                    // Préparer la requête d'insertion
                    $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, prenom, lieux_naissance, date_naissance, cin, date_delivrance, etablissement, parcours, niv_etude, promotion, adresse_mail, telephone, mot_de_passe, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssssssssssss", $nom, $prenom, $lieux_naissance, $date_naissance, $cin, $date_delivrance, $etablissement, $parcours, $niv_etude, $promotion, $adresse_mail, $telephone, $mot_de_passe, $photo);

                    if ($stmt->execute()) {
                        $userId = $stmt->insert_id;

                        // Lier le nouvel utilisateur à toutes les cotisations existantes
                        $sql_cotisations = "SELECT id FROM activites_cotisations WHERE type = 'cotisation'";
                        $result_cotisations = $conn->query($sql_cotisations);

                        if ($result_cotisations->num_rows > 0) {
                            while ($cotisation = $result_cotisations->fetch_assoc()) {
                                $id_cotisation = $cotisation['id'];
                                // Insérer dans utilisateurs_cotisations
                                $stmt_link = $conn->prepare("INSERT INTO utilisateurs_cotisations (id_utilisateur, id_cotisation, etat_paiement) VALUES (?, ?, 'non payé')");
                                $stmt_link->bind_param("ii", $userId, $id_cotisation);
                                $stmt_link->execute();
                                $stmt_link->close();
                            }
                        }

                        // Rediriger vers la page de bienvenue
                        header("Location: ../bienvenue/bienvenue.php?id=$userId");
                        exit();
                    } else {
                        $error_message = "Erreur lors de l'inscription : " . $stmt->error;
                    }
                } else {
                    $error_message = "Erreur lors du téléchargement de la photo.";
                }
            }
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <style>
        :root {
            --primary-color: #3498db;
            --dark-bg: #2c3e50;
            --input-bg: #34495e;
            --text-color: #ecf0f1;
            --error-color: #e74c3c;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--dark-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
        }

        .auth-container {
            display: flex;
            min-height: 100vh;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        .auth-form-wrapper {
            background: var(--input-bg);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
            position: relative;
            overflow: hidden;
        }

        .auth-title {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 2rem;
            color: var(--primary-color);
        }

        .auth-input-group {
            margin-bottom: 1.2rem;
        }

        .auth-input-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #bdc3c7;
        }

        .auth-input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #4a627a;
            border-radius: 8px;
            background: var(--dark-bg);
            color: var(--text-color);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .auth-input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
        }

        .auth-button {
            background: var(--primary-color);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .auth-button:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .error-message {
            color: var(--error-color);
            padding: 0.8rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid var(--error-color);
        }

        .hidden {
            display: none;
        }

        [id^="part"] {
    transition: all 0.3s ease-in-out;
    opacity: 0;
    transform: translateX(20px);
    position: absolute;
    width: 100%;
}

[id^="part"]:not(.hidden) {
    opacity: 1;
    transform: translateX(0);
    position: relative;
}

        @media (max-width: 480px) {
            .auth-form-wrapper {
                padding: 1.5rem;
                border-radius: 0;
            }

            .auth-title {
                font-size: 1.5rem;
            }

            .auth-input {
                padding: 0.7rem;
                font-size: 0.9rem;
            }

            .auth-button {
                padding: 0.7rem;
                font-size: 0.9rem;
            }
        }

        /* Animation shake */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            75% { transform: translateX(8px); }
        }

        .shake {
            animation: shake 0.4s ease;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-form-wrapper">
            <h2 class="auth-title">Inscription</h2>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form id="registerForm" action="inscription.php" method="post" enctype="multipart/form-data">
                <!-- Partie 1 -->
                    <div id="part1">
                        <div class="auth-input-group">
                            <label for="nom" class="auth-input-label">Nom</label>
                            <input type="text" id="nom" name="nom" class="auth-input" placeholder="Votre nom" required>
                        </div>
                        <div class="auth-input-group">
                            <label for="prenom" class="auth-input-label">Prénom</label>
                            <input type="text" id="prenom" name="prenom" class="auth-input" placeholder="Votre prénom" required>
                        </div>
                        <div class="auth-input-group">
                            <label for="lieux_naissance" class="auth-input-label">Lieu de naissance</label>
                            <input type="text" id="lieux_naissance" name="lieux_naissance" class="auth-input" placeholder="Lieu de naissance" required>
                        </div>
                        <div class="auth-input-group">
                            <label for="date_naissance" class="auth-input-label">Date de naissance</label>
                            <input type="date" id="date_naissance" name="date_naissance" class="auth-input" required>
                        </div>
                        <div class="auth-input-group">
                            <label for="cin" class="auth-input-label">Numéro CIN</label>
                            <input type="number" id="cin" name="cin" class="auth-input" placeholder="Numéro CIN" required>
                        </div>
                        <button type="button" class="auth-button next-btn" data-next="2">Suivant</button>
                    </div>

                    <!-- Partie 2 -->
                    <div id="part2" class="hidden">
                        <div class="auth-input-group">
                            <label for="date_delivrance" class="auth-input-label">Date de délivrance</label>
                            <input type="date" id="date_delivrance" name="date_delivrance" class="auth-input" required>
                        </div>
                        <div class="auth-input-group">
                            <label for="etablissement" class="auth-input-label">Établissement</label>
                            <input type="text" id="etablissement" name="etablissement" class="auth-input" placeholder="Établissement" required>
                        </div>
                        <div class="auth-input-group">
                            <label for="parcours" class="auth-input-label">Parcours</label>
                            <input type="text" id="parcours" name="parcours" class="auth-input" placeholder="Parcours" required>
                        </div>
                        <div class="auth-input-group">
                            <label for="niv_etude" class="auth-input-label">Niveau d'étude</label>
                            <input type="text" id="niv_etude" name="niv_etude" class="auth-input" placeholder="Niveau d'étude" required>
                        </div>
                        <div class="auth-input-group">
                            <label for="role" class="auth-input-label">Votre rôle</label>
                            <input type="text" id="role" name="role" class="auth-input" placeholder="Votre rôle" required>
                        </div>
                        <button type="button" class="auth-button prev-btn" data-prev="1">Précédent</button>
                        <button type="button" class="auth-button next-btn" data-next="">Suivant</button>
                    </div>

                    <!-- Partie 3 -->
                    <div id="part3" class="hidden">
                        <div class="auth-input-group">
                            <label for="promotion" class="auth-input-label">Promotion</label>
                            <select id="promotion" name="promotion" class="auth-input" required>
                                <option value="">Sélectionner la promotion</option>
                                <option value="2018">2018</option>
                                <option value="2019">2019</option>
                                <option value="2020">2020</option>
                                <option value="2021">2021</option>
                                <option value="2022">2022</option>
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                            </select>
                        </div>
                        <div class="auth-input-group">
                            <label for="adresse_mail" class="auth-input-label">Adresse mail</label>
                            <input type="email" id="adresse_mail" name="adresse_mail" class="auth-input" placeholder="Adresse mail" required>
                        </div>
                        <div class="auth-input-group">
                            <label for="telephone" class="auth-input-label">Téléphone</label>
                            <input type="text" id="telephone" name="telephone" class="auth-input" placeholder="Téléphone" required>
                        </div>
                        <div class="auth-input-group">
                            <label for="mot_de_passe" class="auth-input-label">Mot de passe</label>
                            <input type="password" id="mot_de_passe" name="mot_de_passe" class="auth-input" placeholder="Mot de passe" required>
                        </div>
                        <div class="auth-input-group">
                            <label for="conf_mot_de_passe" class="auth-input-label">Confirmer le mot de passe</label>
                            <input type="password" id="conf_mot_de_passe" name="conf_mot_de_passe" class="auth-input" placeholder="Confirmer le mot de passe" required>
                        </div>
                        <div class="auth-input-group">
                            <label for="photo" class="auth-input-label">Photo</label>
                            <input type="file" id="photo" name="photo" class="auth-input" accept="image/*" required>
                        </div>
                        <button type="button" class="auth-button prev-btn" data-prev="2">Précédent</button>
                        <button type="submit" class="auth-button" id="submit-btn">S'inscrire</button>
                    </div>
            </form>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const steps = document.querySelectorAll('[id^="part"]');
    
    function showStep(step) {
        steps.forEach(part => {
            const partNumber = part.id.replace('part', '');
            if(partNumber == step) {
                part.classList.remove('hidden');
                part.style.opacity = 1;
                part.style.transform = 'translateX(0)';
            } else {
                part.classList.add('hidden');
                part.style.opacity = 0;
                part.style.transform = 'translateX(20px)';
            }
        });
        currentStep = step;
    }

    function validateStep(step) {
        const currentPart = document.getElementById(`part${step}`);
        const inputs = currentPart.querySelectorAll('input[required], select[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('error', 'shake');
                setTimeout(() => input.classList.remove('shake'), 400);
                isValid = false;
            } else {
                input.classList.remove('error');
            }
        });

        if (!isValid) {
            const errorMessage = currentPart.querySelector('.error-message') || document.createElement('div');
            errorMessage.textContent = 'Veuillez remplir tous les champs obligatoires';
            errorMessage.className = 'error-message';
            if (!currentPart.querySelector('.error-message')) {
                currentPart.prepend(errorMessage);
            }
        }

        return isValid;
    }

    document.querySelectorAll('.next-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (validateStep(currentStep)) {
                showStep(currentStep + 1);
            }
        });
    });

    document.querySelectorAll('.prev-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            showStep(currentStep - 1);
        });
    });

    document.getElementById('submit-btn').addEventListener('click', function(e) {
        if (!validateStep(currentStep)) {
            e.preventDefault();
        }
    });

    // Initialisation
    showStep(1);
});
</script>
</body>
</html>