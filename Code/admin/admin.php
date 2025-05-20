<?php
// Désactiver le rapport d'erreurs qui pourrait interférer avec les en-têtes
error_reporting(0);
ini_set('display_errors', 0);

// Démarrer la session
session_start();

// Vérifie que l'utilisateur est connecté et qu'il a le statut "admin"
if (!isset($_SESSION["Statut"]) || $_SESSION["Statut"] !== "Administrateur") {
    // Redirige vers la page d'erreur d'accès refusé
    header("Location: acces_refuse.php");
    exit;
}

// Récupérer le nom de l'utilisateur depuis la session
$Nom = isset($_SESSION["Nom"]) ? $_SESSION["Nom"] : 'Invité';
$Statut = isset($_SESSION["Statut"]) ? $_SESSION["Statut"] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Accueil - Gestion Admin</title>
    <link rel="stylesheet" href="../backend/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            color: black;
            font-family: Arial, sans-serif;
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
        .welcome-message {
            margin-bottom: 30px;
            font-size: 18px;
        }
        .buttons-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 40px;
        }
        .big-button {
            background-color: #665264;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 30px;
            width: 300px;
            font-size: 18px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.3s ease;
        }
        .big-button:hover {
            background-color: #563f54;
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        .big-button i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .big-button-text {
            margin-top: 10px;
        }
        .stats-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin-top: 50px;
            padding: 20px;
            background-color: #f0f0f0;
            border-radius: 8px;
        }
        .stat-box {
            text-align: center;
            padding: 15px;
            width: 200px;
            margin: 10px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #665264;
        }
        .stat-label {
            font-size: 14px;
            color: #666;
        }
    </style>
</head>

<body>
<div class="header" style="background-color:#665264">
    <img src="../images/smica.png" width="500">
</div>

<div class="topnav">
    <a href="admin.php" class="active"><i class="fa fa-fw fa-home"></i> Accueil</a>
    <a href="https://www.carnus.fr" target="_blank"><i class="fa-solid fa-circle-info"></i> Info</a>
    <a href="../backend/logout.php" style="float:right"><i class="fa-solid fa-power-off"></i> Déconnexion</a>
    <a href style="float:right"><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($Nom); ?></a>
</div>

<div class="container">
    <h1><i class="fa-solid fa-gauge-high"></i> Tableau de bord</h1>
    
    <div class="welcome-message">
        <p>Bienvenue, <strong><?php echo htmlspecialchars($Nom); ?></strong> ! Que souhaitez-vous faire aujourd'hui ?</p>
    </div>

    <div class="buttons-container">
        
    <a href="ajouter_client.php" class="big-button">
        <i class="fa-solid fa-address-card"></i>
        <span class="big-button-text">Ajouter un client</span>
    </a>

    <a href="ajouter_batiment.php" class="big-button">
        <i class="fa-solid fa-building-user"></i>
        <span class="big-button-text">Ajouter un bâtiment</span>
    </a>

    
    <a href="users.php" class="big-button">
        <i class="fa-solid fa-user-plus"></i>
        <span class="big-button-text">Ajouter un utilisateur</span>
    </a>
    
    <a href="liste_capteurs.php" class="big-button">
        <i class="fa-solid fa-microchip"></i>
        <span class="big-button-text">Gérer les capteurs</span>
    </a>
     
</div>
     
</div>
    
    <?php
    // Connexion à la base de données pour les statistiques
    $mysqli = new mysqli("localhost", "root", "", "mairie");
    
    // Vérification de la connexion
    if (!$mysqli->connect_error) {
        // Récupérer le nombre d'utilisateurs
        $query_users = "SELECT COUNT(*) as total FROM users";
        $result_users = $mysqli->query($query_users);
        $users_count = ($result_users) ? $result_users->fetch_assoc()['total'] : 0;
        
        // Récupérer le nombre de bâtiments
        $query_batiments = "SELECT COUNT(*) as total FROM batiments";
        $result_batiments = $mysqli->query($query_batiments);
        $batiments_count = ($result_batiments) ? $result_batiments->fetch_assoc()['total'] : 0;
        
        // Récupérer le nombre de villes
        $query_villes = "SELECT COUNT(DISTINCT ville) as total FROM batiments";
        $result_villes = $mysqli->query($query_villes);
        $villes_count = ($result_villes) ? $result_villes->fetch_assoc()['total'] : 0;
        
        // Récupérer le nombre de clients
        $query_clients = "SELECT COUNT(*) as total FROM clients";
        $result_clients = $mysqli->query($query_clients);
        $clients_count = ($result_clients) ? $result_clients->fetch_assoc()['total'] : 0;
    ?>
    
    <div class="stats-container">
        <div class="stat-box">
            <i class="fa-solid fa-users fa-2x" style="color: #665264;"></i>
            <div class="stat-number"><?php echo $users_count; ?></div>
            <div class="stat-label">Utilisateurs</div>
        </div>
        
        <div class="stat-box">
            <i class="fa-solid fa-building fa-2x" style="color: #665264;"></i>
            <div class="stat-number"><?php echo $batiments_count; ?></div>
            <div class="stat-label">Bâtiments</div>
        </div>
        
        <div class="stat-box">
            <i class="fa-solid fa-city fa-2x" style="color: #665264;"></i>
            <div class="stat-number"><?php echo $villes_count; ?></div>
            <div class="stat-label">Villes</div>
        </div>
        
        <div class="stat-box">
            <i class="fa-solid fa-address-card fa-2x" style="color: #665264;"></i>
            <div class="stat-number"><?php echo $clients_count; ?></div>
            <div class="stat-label">Clients</div>
        </div>
    </div>
    <?php
    }
    // Fermer la connexion à la base de données
    if (isset($mysqli)) {
        $mysqli->close();
    }
    ?>
    
    <div style="margin-top: 30px;">
        <h2><i class="fa-solid fa-clock-rotate-left"></i> Activités récentes</h2>
        <p>Consultez les dernières actions effectuées dans le système.</p>
        
        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <thead>
                <tr style="background-color: #eee;">
                    <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Date</th>
                    <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Utilisateur</th>
                    <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><?php echo date('d/m/Y H:i'); ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($Nom); ?></td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">Connexion au système</td>
                </tr>
                <!-- Ici, vous pourriez ajouter du code pour récupérer les activités récentes depuis une table de logs si vous en avez une -->
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation pour les compteurs de statistiques
    const statNumbers = document.querySelectorAll('.stat-number');
    
    statNumbers.forEach(statNumber => {
        const finalValue = parseInt(statNumber.textContent);
        let currentValue = 0;
        const increment = Math.max(1, Math.floor(finalValue / 20));
        const duration = 1000; // durée en ms
        const frameDuration = 20; // 20ms par frame
        const frames = duration / frameDuration;
        const incrementPerFrame = Math.max(1, Math.ceil(finalValue / frames));
        
        const counter = setInterval(() => {
            currentValue += incrementPerFrame;
            if (currentValue >= finalValue) {
                currentValue = finalValue;
                clearInterval(counter);
            }
            statNumber.textContent = currentValue;
        }, frameDuration);
        
        // Réinitialiser le contenu pour que l'animation commence à 0
        statNumber.textContent = '0';
    });
    
    // Effet de survol pour les boutons
    const bigButtons = document.querySelectorAll('.big-button');
    
    bigButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

</body>
</html>