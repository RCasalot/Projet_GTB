<?php
// Démarrer la session
session_start();

// Vérifie que l'utilisateur est connecté et qu'il a le statut "admin"
if (!isset($_SESSION["Statut"]) || $_SESSION["Statut"] !== "Administrateur") {
    // Redirige vers la page de connexion si l'utilisateur n'est pas autorisé
    header("Location: ../index.php");
    exit;
}

// Récupérer le nom de l'utilisateur depuis la session
$Nom = isset($_SESSION["Nom"]) ? $_SESSION["Nom"] : 'Invité';
$Statut = isset($_SESSION["Statut"]) ? $_SESSION["Statut"] : '';

// Vérifier si l'utilisateur vient bien d'ajouter un client
if (!isset($_SESSION['creation_success']) || $_SESSION['creation_success'] !== true) {
    // Rediriger vers la page d'administration si pas de confirmation de création
    header("Location: admin.php");
    exit;
}

// Récupérer le nom de la ville depuis la session
$nomVille = isset($_SESSION['nom_ville']) ? $_SESSION['nom_ville'] : 'le client';

// Récupérer le nom de l'utilisateur depuis la session
$Nom = isset($_SESSION["Nom"]) ? $_SESSION["Nom"] : 'Invité';
$Statut = isset($_SESSION["Statut"]) ? $_SESSION["Statut"] : '';

// Réinitialiser les variables de session pour éviter les doublons
$_SESSION['creation_success'] = false;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Confirmation d'ajout de client</title>
    <link rel="stylesheet" href="../backend/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .confirmation-container {
            width: 80%;
            margin: 50px auto;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            color: black;
        }
        .success-icon {
            font-size: 64px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        .btn-group {
            margin-top: 30px;
        }
        .btn {
            display: inline-block;
            background-color: #665264;
            color: white;
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #563f54;
        }
        h1, h2, p {
            color: black;
        }
    </style>
</head>

<body>
<div class="header" style="background-color:#665264">
    <img src="../images/smica.png" width="500">
</div>

<div class="topnav">
    <a href="admin.php"><i class="fa fa-fw fa-home"></i> Accueil</a>
    <a href="https://www.carnus.fr" target="_blank"><i class="fa-solid fa-circle-info"></i> Info</a>
    <a href="../backend/logout.php" style="float:right"><i class="fa-solid fa-power-off"></i> Déconnexion</a>
    <a href style="float:right"><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($Nom); ?></a>
    <a href="users.php" class="active" href="ajout.php" style="float:right"><i class="fa-solid fa-user-plus"></i> Ajout</a>
</div>

<div class="confirmation-container">
    <div class="success-icon">
        <i class="fas fa-check-circle"></i>
    </div>
    
    <h1>Client ajouté avec succès !</h1>
    <p>Le client <strong><?php echo htmlspecialchars($nomVille); ?></strong> a été ajouté à la base de données.</p>
    
    <div class="btn-group">
        <a href="ajouter_client.php" class="btn">
            <i class="fas fa-plus"></i> Ajouter un autre client
        </a>
        <a href="admin.php" class="btn">
            <i class="fas fa-home"></i> Retour à l'accueil
        </a>
    </div>
</div>

</body>
</html>