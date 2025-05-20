<!DOCTYPE html>
<html>
<head>
    <title>Accueil - Gestion Mairie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            color: black;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }
        .header {
            background-color: #665264;
            padding: 10px 0;
            text-align: center;
        }
        .topnav {
            overflow: hidden;
            background-color: #333;
        }
        .topnav a {
            float: left;
            display: block;
            color: #f2f2f2;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }
        .topnav a:hover {
            background-color: #ddd;
            color: black;
        }
        .topnav a.active {
            background-color: #4CAF50;
            color: white;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1, h2 {
            color: #665264;
        }
        .welcome-section {
            margin: 30px 0;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }
        .login-button {
            background-color: #665264;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 25px 40px;
            font-size: 24px;
            cursor: pointer;
            margin: 40px auto;
            display: inline-block;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .login-button:hover {
            background-color: #563f54;
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }
        .login-button i {
            margin-right: 15px;
            font-size: 28px;
        }
        .features-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin-top: 40px;
        }
        .feature-box {
            text-align: center;
            padding: 20px;
            width: 250px;
            margin: 15px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .feature-box:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: #665264;
        }
        .footer {
            background-color: #665264;
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: 40px;
            font-size: 14px;
        }
    </style>
</head>

<body>
<div class="header">
    <img src="images/smica.png" width="500" alt="Logo SMICA">
</div>

<div class="topnav">
    <a href="index.html" class="active"><i class="fa fa-fw fa-home"></i> Accueil</a>
    <a href="https://www.carnus.fr" target="_blank"><i class="fa-solid fa-circle-info"></i> Info</a>
    <a href="#contact"><i class="fa-solid fa-envelope"></i> Contact</a>
</div>

<div class="container">
    <h1><i class="fa-solid fa-city"></i> Système de Gestion Mairie</h1>
    
    <div class="welcome-section">
        <h2>Bienvenue sur la plateforme de gestion municipale</h2>
        <p>Notre système vous permet de gérer efficacement les bâtiments, utilisateurs et clients de votre commune.</p>
        <p>Veuillez vous connecter pour accéder à votre espace personnel.</p>
    </div>
    
    <a href="backend/login.php" class="login-button">
        <i class="fa-solid fa-right-to-bracket"></i> Se connecter
    </a>
    
    <div class="features-container">
        <div class="feature-box">
            <div class="feature-icon">
                <i class="fa-solid fa-users"></i>
            </div>
            <h3>Gestion des utilisateurs</h3>
            <p>Ajoutez, modifiez et gérez les accès des utilisateurs du système.</p>
        </div>
        
        <div class="feature-box">
            <div class="feature-icon">
                <i class="fa-solid fa-building"></i>
            </div>
            <h3>Gestion des bâtiments</h3>
            <p>Inventoriez et administrez tous les bâtiments municipaux.</p>
        </div>
        
        <div class="feature-box">
            <div class="feature-icon">
                <i class="fa-solid fa-address-card"></i>
            </div>
            <h3>Gestion des clients</h3>
            <p>Suivez et organisez les informations relatives aux clients.</p>
        </div>
    </div>
</div>

<div class="footer">
    <p>&copy; 2025 Système de Gestion Technique des Bâtiments. Tous droits réservés.</p>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation pour les feature-box
    const featureBoxes = document.querySelectorAll('.feature-box');
    
    featureBoxes.forEach(box => {
        box.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
        });
        
        box.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(-5px)';
        });
    });
    
    // Animation pour le bouton de connexion
    const loginButton = document.querySelector('.login-button');
    loginButton.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
    });
    
    loginButton.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});
</script>

</body>
</html>