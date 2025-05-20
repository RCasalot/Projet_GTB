<?php
// Démarrer la session
session_start();

// Récupérer les informations de la session
$creationSuccess = isset($_SESSION['creation_success']) ? $_SESSION['creation_success'] : false;
$nomBatiment = isset($_SESSION['nom_batiment']) ? $_SESSION['nom_batiment'] : '';

// Vérifier si l'utilisateur a été redirigé après une création réussie
if (!$creationSuccess) {
    // Rediriger vers la page d'accueil si accès direct à cette page
    header("Location: admin.php");
    exit();
}

// Récupérer le nom de l'utilisateur depuis la session
$Nom = isset($_SESSION["Nom"]) ? $_SESSION["Nom"] : 'Invité';
$Statut = isset($_SESSION["Statut"]) ? $_SESSION["Statut"] : '';

// Supprimer les variables de session utilisées pour la confirmation
unset($_SESSION['creation_success']);
unset($_SESSION['nom_batiment']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Confirmation - Bâtiment créé</title>
    <link rel="stylesheet" href="../backend/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            color: black;
        }
        .confirmation-container {
            width: 80%;
            margin: 50px auto;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .success-icon {
            font-size: 60px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        .success-message {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }
        .confirmation-details {
            font-size: 18px;
            margin-bottom: 30px;
            color: #555;
        }
        .back-button {
            background-color: #665264;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
        }
        .back-button:hover {
            background-color: #563f54;
        }
        .buttons-container {
            margin-top: 30px;
        }
    </style>
    <!-- Redirection automatique après 5 secondes -->
    <meta http-equiv="refresh" content="5;url=admin.php">
</head>

<body>
<div class="header" style="background-color:#665264">
    <img src="../images/smica.png" width="500"></a>
</div>

<div class="topnav">
    <a href="admin.php"><i class="fa fa-fw fa-home"></i> Accueil</a>
    <a href="https://www.carnus.fr" target="_blank"><i class="fa-solid fa-circle-info"></i> Info</a>
    <a href="../backend/logout.php" style="float:right"><i class="fa-solid fa-power-off"></i> Déconnexion</a>
    <a style="float:right"><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($Nom); ?></a>
    <a href="ajout.php" style="float:right"><i class="fa-solid fa-user-plus"></i> Ajout</a>
</div>

<div class="confirmation-container">
    <div class="success-icon">
        <i class="fa-solid fa-circle-check"></i>
    </div>
    
    <div class="success-message">
        Bâtiment créé avec succès !
    </div>
    
    <div class="confirmation-details">
        Le bâtiment <strong><?php echo htmlspecialchars($nomBatiment); ?></strong> et ses capteurs ont été ajoutés à la base de données.
    </div>
    
    <div class="countdown">
        Vous serez redirigé vers la page d'accueil dans <span id="countdown">5</span> secondes...
    </div>
    
    <div class="buttons-container">
        <a href="admin.php" class="back-button">
            <i class="fa-solid fa-home"></i> Retour à l'accueil
        </a>
        
        <a href="admin.php" class="back-button">
            <i class="fa-solid fa-plus"></i> Ajouter un autre bâtiment
        </a>
    </div>
</div>

<script>
// Compte à rebours
let countdownElement = document.getElementById('countdown');
let seconds = 5;

function updateCountdown() {
    seconds--;
    countdownElement.textContent = seconds;
    
    if (seconds <= 0) {
        clearInterval(countdownInterval);
    }
}

let countdownInterval = setInterval(updateCountdown, 1000);
</script>

</body>
</html>