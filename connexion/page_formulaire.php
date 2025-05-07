<?php
session_start();

// Déterminer quel formulaire charger
$form_to_load = 'connexion.php';
if (isset($_GET['form'])) {
    $form_to_load = ($_GET['form'] === 'register') ? 'inscription.php' : 'connexion.php';
}

ob_start();
include $form_to_load;
$form_content = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Bienvenue</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #f39c12;
            --secondary-color: #2c3e50;
            --dark-bg: #1e1e1e;
            --text-light: #ecf0f1;
            --overlay-dark: rgba(0, 0, 0, 0.7);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            overflow-x: hidden;
            font-family: 'Poppins', sans-serif;
            background: var(--dark-bg);
            color: var(--text-light);
        }

        .auth-container {
            display: flex;
            min-height: 100vh;
        }

        .auth-image-section {
            flex: 1;
            min-width: 50%;
            position: relative;
        }

        .image-slider {
            height: 60vh;
            position: relative;
            overflow: hidden;
            border-bottom: 3px solid var(--primary-color);
        }

        .slider-container {
            display: flex;
            height: 100%;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .slider-container img {
            min-width: 100%;
            height: 100%;
            object-fit: cover;
            flex-shrink: 0;
            filter: brightness(0.9);
        }

        .about-section {
            padding: 2rem 1.5rem;
            background: linear-gradient(135deg, var(--overlay-dark), var(--secondary-color));
            position: relative;
            min-height: 40vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .about-section h2 {
            font-size: 1.7rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
            line-height: 1.3;
        }

        .about-section p {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }

        .social-icons {
            display: flex;
            gap: 1.2rem;
        }

        .social-icons a {
            color: var(--text-light);
            font-size: 1.6rem;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .social-icons a:hover {
            color: var(--primary-color);
            transform: translateY(-3px);
        }

        .auth-form-section {
            flex: 0 0 40%;
            background: var(--secondary-color);
            padding: 2rem;
            position: relative;
            overflow-y: auto;
        }

        .auth-form-section::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 80%;
            width: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .form-container {
            max-width: 400px;
            margin: 0 auto;
            position: relative;
        }

        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column;
                min-height: auto;
                height: auto;
            }

            .image-slider {
                height: 45vh;
                border-bottom-width: 2px;
            }

            .about-section {
                min-height: auto;
                padding: 1.8rem 1.2rem;
            }

            .auth-form-section {
                flex: 1;
                padding: 2rem 1.2rem;
                min-height: 60vh;
                overflow-y: visible;
            }

            .auth-form-section::before {
                display: none;
            }

            .social-icons a {
                font-size: 1.8rem;
            }

            .about-section h2 {
                font-size: 1.5rem;
            }

            .about-section p {
                font-size: 0.95rem;
            }
        }

        @media (max-width: 480px) {
            .image-slider {
                height: 40vh;
            }

            .auth-form-section {
                min-height: 55vh;
                padding: 1.5rem 1rem;
            }

            .about-section {
                padding: 1.5rem 1rem;
            }

            .social-icons {
                gap: 1rem;
            }

            .social-icons a {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 360px) {
            .image-slider {
                height: 35vh;
            }

            .auth-form-section {
                min-height: 50vh;
            }
        }

        /* Améliorations de l'animation du slider */
        .slider-container {
            will-change: transform;
        }

        /* Optimisation du défilement mobile */
        @media (hover: none) {
            body {
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Section image -->
        <div class="auth-image-section">
            <div class="image-slider">
                <div class="slider-container" id="slider">
                    <img src="img1.jpg" alt="Destination">
                    <img src="img2.jpg" alt="Aventure">
                    <img src="img3.jpg" alt="Plage">
                    <img src="img4.jpg" alt="Plagek">
                    <img src="img5.jpg" alt="Plagekk">
                </div>
            </div>
            
            <div class="about-section">
                <h2>Qui sommes-nous ?</h2>
                <p>Associationdes Etudiant Dynamique de DIego.</p>
                
                <div class="social-icons">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>

        <!-- Section formulaire -->
        <div class="auth-form-section">
            <div class="form-container">
                <?php echo $form_content; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slider = document.getElementById('slider');
            if (!slider) return;

            const images = slider.querySelectorAll('img');
            if (images.length < 2) return;

            let currentIndex = 0;
            const slideInterval = 5000;
            let interval;

            function updateSlider() {
                slider.style.transform = `translateX(-${currentIndex * 100}%)`;
            }

            function nextSlide() {
                currentIndex = (currentIndex + 1) % images.length;
                updateSlider();
            }

            // Gestion responsive du slider
            function handleResize() {
                if (window.innerWidth <= 768) {
                    startSlider();
                } else {
                    clearInterval(interval);
                }
            }

            function startSlider() {
                if (!interval) {
                    interval = setInterval(nextSlide, slideInterval);
                }
            }

            // Démarrer/arrêter le slider selon la taille
            window.addEventListener('resize', handleResize);
            handleResize();

            // Gestion tactile
            let touchStartX = 0;
            let touchEndX = 0;

            slider.addEventListener('touchstart', e => {
                touchStartX = e.changedTouches[0].screenX;
                clearInterval(interval);
            });

            slider.addEventListener('touchend', e => {
                touchEndX = e.changedTouches[0].screenX;
                if (touchStartX - touchEndX > 50) {
                    nextSlide();
                }
                if (touchEndX - touchStartX > 50) {
                    currentIndex = (currentIndex - 1 + images.length) % images.length;
                    updateSlider();
                }
                startSlider();
            });
        });
    </script>
</body>
</html>