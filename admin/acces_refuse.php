<?php
// Démarrer la session
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Accès Refusé - Gestion Mairie</title>
    <link rel="stylesheet" href="../backend/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <meta http-equiv="refresh" content="5;url=../backend/login.php" />
    <style>
        body {
            color: black;
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
        }
        .error-container {
            width: 80%;
            max-width: 600px;
            margin: 100px auto;
            padding: 30px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #665264;
            margin-bottom: 20px;
        }
        .error-icon {
            font-size: 60px;
            color: #d9534f;
            margin-bottom: 20px;
        }
        .error-message {
            font-size: 18px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .redirect-message {
            font-size: 14px;
            color: #666;
            margin-top: 40px;
        }
        .countdown {
            font-weight: bold;
            color: #665264;
        }
        .login-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #665264;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .login-link:hover {
            background-color: #563f54;
        }
    </style>
</head>

<body>
<div class="header" style="background-color:#665264">
    <img src="../images/smica.png" width="500">
</div>

<div class="error-container">
    <div class="error-icon">
        <i class="fa-solid fa-circle-exclamation"></i>
    </div>
    <h1>Accès Refusé</h1>
    <div class="error-message">
        Vous n'avez pas les droits nécessaires pour accéder à cette page.
        <br>
        Veuillez vous connecter avec un compte disposant des privilèges d'administrateur.
    </div>
    
    <a href="../index.php" class="login-link">
        <i class="fa-solid fa-right-to-bracket"></i> Se connecter
    </a>
    
    <div class="redirect-message">
        Vous serez automatiquement redirigé vers la page de connexion dans <span id="countdown" class="countdown">5</span> secondes.
    </div>
</div>

<script>
    // Script pour le compte à rebours
    let secondsLeft = 5;
    const countdownElement = document.getElementById('countdown');
    
    const countdownInterval = setInterval(function() {
        secondsLeft--;
        countdownElement.textContent = secondsLeft;
        
        if (secondsLeft <= 0) {
            clearInterval(countdownInterval);
        }
    }, 1000);
</script>

</body>
</html>