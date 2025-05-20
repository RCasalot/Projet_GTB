<?php
// Démarrer la session
session_start();

// Connexion à la base de données
$mysqli = new mysqli("localhost", "root", "", "mairie");

// Vérification de la connexion
if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

// Vérification de l'authentification et des droits
if (!isset($_SESSION["Nom"]) || !isset($_SESSION["Statut"]) || $_SESSION["Statut"] !== "Administrateur") {
    header("Location: ../backend/login.php");
    exit();
}

// Récupérer le nom de l'utilisateur depuis la session
$Nom = isset($_SESSION["Nom"]) ? $_SESSION["Nom"] : 'Invité';
$Statut = isset($_SESSION["Statut"]) ? $_SESSION["Statut"] : '';

// Traitement de l'ajout d'un nouvel utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "ajouter") {
    // Récupération des données du formulaire
    $nouveauNom = trim($_POST["nom"]);
    $nouveauMdp = trim($_POST["password"]);
    $nouveauStatut = $_POST["statut"];
    
    // Validation des données
    $erreurs = [];
    
    // Validation du nom
    if (empty($nouveauNom)) {
        $erreurs[] = "Le nom est obligatoire";
    } else {
        // Vérifier si le nom existe déjà
        $checkNom = $mysqli->prepare("SELECT COUNT(*) FROM connexions WHERE Nom = ?");
        $checkNom->bind_param("s", $nouveauNom);
        $checkNom->execute();
        $checkNom->bind_result($count);
        $checkNom->fetch();
        $checkNom->close();
        
        if ($count > 0) {
            $erreurs[] = "Ce nom d'utilisateur est déjà utilisé";
        }
    }
    
    // Validation du mot de passe
    if (empty($nouveauMdp)) {
        $erreurs[] = "Le mot de passe est obligatoire";
    } elseif (strlen($nouveauMdp) < 4) {
        $erreurs[] = "Le mot de passe doit contenir au moins 6 caractères";
    }
    
    // Si aucune erreur, ajouter l'utilisateur
    if (empty($erreurs)) {
        // Hachage du mot de passe avec SHA-256 (d'après le format déjà utilisé dans la base)
        $hashedPassword = hash('sha256', $nouveauMdp);
        
        // Génération d'un code aléatoire à 6 chiffres
        $code = mt_rand(100000, 999999);
        
        // Préparation de la requête d'insertion
        $stmt = $mysqli->prepare("INSERT INTO connexions (Nom, Mdp, Statut, Code) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nouveauNom, $hashedPassword, $nouveauStatut, $code);
        
        // Exécution de la requête
        if ($stmt->execute()) {
            $successMessage = "Utilisateur ajouté avec succès";
        } else {
            $erreurs[] = "Erreur lors de l'ajout de l'utilisateur: " . $mysqli->error;
        }
        
        $stmt->close();
    }
}

// Traitement de la suppression d'un utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "supprimer") {
    $idUtilisateur = $_POST["id_utilisateur"];
    
    // Vérifier que l'utilisateur n'essaie pas de se supprimer lui-même
    $checkSelf = $mysqli->prepare("SELECT Nom FROM connexions WHERE ID = ?");
    $checkSelf->bind_param("i", $idUtilisateur);
    $checkSelf->execute();
    $checkSelf->bind_result($nomUtilisateur);
    $checkSelf->fetch();
    $checkSelf->close();
    
    if ($nomUtilisateur === $Nom) {
        $erreurs[] = "Vous ne pouvez pas supprimer votre propre compte";
    } else {
        // Suppression de l'utilisateur
        $stmt = $mysqli->prepare("DELETE FROM connexions WHERE ID = ?");
        $stmt->bind_param("i", $idUtilisateur);
        
        if ($stmt->execute()) {
            $successMessage = "Utilisateur supprimé avec succès";
        } else {
            $erreurs[] = "Erreur lors de la suppression de l'utilisateur: " . $mysqli->error;
        }
        
        $stmt->close();
    }
}

// Récupération de la liste des utilisateurs
$query = "SELECT ID, Nom, Statut, Code, Date FROM connexions ORDER BY Nom ASC";
$result = $mysqli->query($query);
$utilisateurs = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $utilisateurs[] = $row;
    }
    $result->free();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Utilisateurs - Mairie</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../backend/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .container {
            width: 90%;
            margin: 20px auto;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .section-title {
            color: #665264;
            margin: 20px 0 10px;
            border-bottom: 2px solid #eaeaea;
            padding-bottom: 10px;
        }
        
        /* Tableau des utilisateurs */
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .users-table th, .users-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        
        .users-table th {
            background-color: #665264;
            color: white;
            font-weight: normal;
        }
        
        .users-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .users-table tr:hover {
            background-color: #e9e9e9;
        }
        
        /* Formulaire d'ajout */
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .btn {
            background-color: #665264;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 16px;
        }
        
        .btn:hover {
            background-color: #533f52;
        }
        
        .btn-danger {
            background-color: #dc3545;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        /* Messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        
        /* Tabs */
        .tab {
            overflow: hidden;
            background-color: #f2f2f2;
            border-radius: 8px 8px 0 0;
            border: 1px solid #ddd;
            border-bottom: none;
        }
        
        .tab button {
            background-color: inherit;
            float: left;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 14px 16px;
            transition: 0.3s;
            font-size: 16px;
        }
        
        .tab button:hover {
            background-color: #ddd;
        }
        
        .tab button.active {
            background-color: #665264;
            color: white;
        }
        
        .tabcontent {
            display: none;
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 8px 8px;
            animation: fadeEffect 1s;
        }
        
        @keyframes fadeEffect {
            from {opacity: 0;}
            to {opacity: 1;}
        }
        
        /* Actions column */
        .actions-cell {
            white-space: nowrap;
            text-align: center;
        }
        
        /* Responsive design */
        @media screen and (max-width: 768px) {
            .container {
                width: 95%;
                padding: 10px;
            }
            
            .users-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="header" style="background-color:#665264">
        <a href="index2.php" target="_blank"><img src="../images/smica.png" width="500"></a>
    </div>

    <div class="topnav">
        <a href="admin.php"><i class="fa fa-fw fa-home"></i> Accueil</a>
        <a href="https://www.carnus.fr" target="_blank"><i class="fa-solid fa-circle-info"></i> Info</a>
        <a href="../backend/logout.php" style="float:right"><i class="fa-solid fa-power-off"></i> Déconnexion</a>
        <a style="float:right"><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($Nom); ?></a>
    </div>

    <h1 style="text-align: center; margin-top: 20px;">Gestion des Utilisateurs</h1>

    <div class="container">
        <!-- Affichage des messages de succès ou d'erreur -->
        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($erreurs)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($erreurs as $erreur): ?>
                        <li><?php echo htmlspecialchars($erreur); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Onglets -->
        <div class="tab">
            <button class="tablinks active" onclick="openTab(event, 'liste')">Liste des Utilisateurs</button>
            <button class="tablinks" onclick="openTab(event, 'ajout')">Ajouter un Utilisateur</button>
        </div>

        <!-- Onglet Liste des Utilisateurs -->
        <div id="liste" class="tabcontent" style="display: block;">
            <h2 class="section-title">Liste des Utilisateurs</h2>
            
            <?php if (empty($utilisateurs)): ?>
                <p>Aucun utilisateur trouvé.</p>
            <?php else: ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Statut</th>
                            <th>Code</th>
                            <th>Date de mise à jour</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($utilisateurs as $utilisateur): ?>
                            <tr>
                                <td><font color="black"><?php echo htmlspecialchars($utilisateur['Nom']); ?></font></td>
                                <td><font color="black"><?php echo htmlspecialchars($utilisateur['Statut']); ?></font></td>
                                <td><font color="black"><?php echo htmlspecialchars($utilisateur['Code']); ?></font></td>
                                <td><font color="black"><?php echo htmlspecialchars($utilisateur['Date']); ?></font></td>
                                <td class="actions-cell">
                                    <?php if ($utilisateur['Nom'] !== $Nom): ?>
                                        <form method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');" style="display: inline;">
                                            <input type="hidden" name="action" value="supprimer">
                                            <input type="hidden" name="id_utilisateur" value="<?php echo $utilisateur['ID']; ?>">
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fa-solid fa-trash"></i> Supprimer
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span title="Vous ne pouvez pas supprimer votre propre compte">
                                            <button class="btn btn-danger" disabled>
                                                <i class="fa-solid fa-trash"></i> Supprimer
                                            </button>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Onglet Ajouter un Utilisateur -->
        <div id="ajout" class="tabcontent">
            <h2 class="section-title">Ajouter un Nouvel Utilisateur</h2>
            
            <form method="post" action="">
                <input type="hidden" name="action" value="ajouter">
                
                <div class="form-group">
                    <label for="nom"><font color="black">Nom d'utilisateur:</font></label>
                    <input type="text" id="nom" name="nom" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password"><font color="black">Mot de passe</font></label>
                    <input type="password" id="password" name="password" class="form-control" minlength="6" required>
                </div>
                
                <div class="form-group">
                    <label for="statut"><font color="black">Statut:</font></label>
                    <select id="statut" name="statut" class="form-control" required>
                        <option value="Utilisateur">Utilisateur</option>
                        <option value="Administrateur">Administrateur</option>
                        <option value="Responsable">Responsable</option>
                    </select>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fa-solid fa-user-plus"></i> Ajouter l'utilisateur
                </button>
            </form>
        </div>
    </div>

    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            
            // Masquer tous les contenus d'onglets
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            
            // Désactiver tous les boutons d'onglets
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            
            // Afficher l'onglet actif et activer le bouton
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
    </script>
</body>
</html>