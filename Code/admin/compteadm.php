<?php
// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté et a des droits d'administrateur
if (!isset($_SESSION["Nom"]) || !isset($_SESSION["Statut"]) || $_SESSION["Statut"] !== "admin") {
    // Rediriger vers la page de connexion si non connecté ou non admin
    header("Location: ../index.php");
    exit();
}

// Connexion à la base de données
$mysqli = new mysqli("localhost", "root", "", "mairie");

// Vérification de la connexion
if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

// Récupérer tous les utilisateurs
$query = "SELECT * FROM users ORDER BY id ASC";
$result = $mysqli->query($query);

// Récupérer le nom de l'utilisateur depuis la session
$Nom = isset($_SESSION["Nom"]) ? $_SESSION["Nom"] : 'Invité';
$Statut = isset($_SESSION["Statut"]) ? $_SESSION["Statut"] : '';

// Traitement de la suppression d'un utilisateur
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['ID'])) {
    $id = intval($_GET['ID']);
    
    // Empêcher la suppression de son propre compte
    if (isset($_SESSION['ID']) && $id == $_SESSION['ID']) {
        $message = "Vous ne pouvez pas supprimer votre propre compte.";
        $messageType = "error";
    } else {
        $deleteQuery = "DELETE FROM users WHERE id = $id";
        if ($mysqli->query($deleteQuery)) {
            $message = "Utilisateur supprimé avec succès.";
            $messageType = "success";
            // Recharger la liste des utilisateurs après suppression
            $result = $mysqli->query($query);
        } else {
            $message = "Erreur lors de la suppression de l'utilisateur : " . $mysqli->error;
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Liste des utilisateurs</title>
    <link rel="stylesheet" href="../backend/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            color: black;
        }
        .users-container {
            width: 90%;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #665264;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .actions-column {
            width: 150px;
            text-align: center;
        }
        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 5px;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn-edit {
            background-color: #4CAF50;
            color: white;
        }
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
        .btn-new {
            background-color: #665264;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
            text-decoration: none;
            display: inline-block;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        .user-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .admin-badge {
            background-color: #d9534f;
            color: white;
        }
        .user-badge.agent {
            background-color: #5bc0de;
            color: white;
        }
        .user-badge.technicien {
            background-color: #f0ad4e;
            color: white;
        }
        .search-form {
            display: flex;
            margin-bottom: 20px;
        }
        .search-input {
            flex-grow: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
        }
        .search-button {
            background-color: #665264;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        .confirm-delete {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .confirm-box {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .confirm-box h3 {
            margin-top: 0;
        }
        .confirm-buttons {
            margin-top: 20px;
        }
        .btn-confirm {
            padding: 8px 15px;
            margin: 0 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-yes {
            background-color: #f44336;
            color: white;
        }
        .btn-no {
            background-color: #ccc;
            color: black;
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
    <a class="active" href="liste_utilisateurs.php" style="float:right"><i class="fa-solid fa-users"></i> Utilisateurs</a>
</div>

<div class="users-container">
    <h2><i class="fa-solid fa-users"></i> Liste des utilisateurs</h2>
    
    <?php if (isset($message)): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <a href="ajouter_utilisateur.php" class="btn-new">
        <i class="fa-solid fa-user-plus"></i> Ajouter un utilisateur
    </a>
    
    <div class="search-form">
        <input type="text" id="searchInput" class="search-input" placeholder="Rechercher un utilisateur...">
        <button type="button" class="search-button" onclick="searchUsers()">
            <i class="fa-solid fa-search"></i>
        </button>
    </div>
    
    <div class="table-responsive">
        <table id="usersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Statut</th>
                    <th>Date d'inscription</th>
                    <th>Dernière connexion</th>
                    <th class="actions-column">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['Nom']); ?></td>
                            <td><?php echo htmlspecialchars($row['Email']); ?></td>
                            <td>
                                <span class="user-badge <?php echo strtolower($row['Statut']); ?>">
                                    <?php echo htmlspecialchars($row['Statut']); ?>
                                </span>
                            </td>
                            <td><?php echo isset($row['date_inscription']) ? $row['date_inscription'] : 'N/A'; ?></td>
                            <td><?php echo isset($row['derniere_connexion']) ? $row['derniere_connexion'] : 'N/A'; ?></td>
                            <td class="actions-column">
                                <a href="modifier_utilisateur.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit" title="Modifier">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <a href="#" class="btn-action btn-delete" title="Supprimer" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['Nom']); ?>')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">Aucun utilisateur trouvé</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Boîte de dialogue de confirmation pour la suppression -->
<div id="confirmDelete" class="confirm-delete" style="display: none;">
    <div class="confirm-box">
        <h3>Confirmation de suppression</h3>
        <p>Êtes-vous sûr de vouloir supprimer l'utilisateur <span id="userName"></span> ?</p>
        <div class="confirm-buttons">
            <a href="#" id="confirmYes" class="btn-confirm btn-yes">Oui, supprimer</a>
            <button id="confirmNo" class="btn-confirm btn-no">Annuler</button>
        </div>
    </div>
</div>

<script>
// Fonction pour confirmer la suppression
function confirmDelete(userId, userName) {
    document.getElementById('userName').textContent = userName;
    document.getElementById('confirmYes').setAttribute('href', 'liste_utilisateurs.php?action=supprimer&id=' + userId);
    document.getElementById('confirmDelete').style.display = 'flex';
}

// Fermer la boîte de dialogue de confirmation
document.getElementById('confirmNo').addEventListener('click', function() {
    document.getElementById('confirmDelete').style.display = 'none';
});

// Fonction de recherche d'utilisateurs
function searchUsers() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toUpperCase();
    const table = document.getElementById('usersTable');
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) { // Commencer à 1 pour ignorer l'en-tête
        let found = false;
        const td = tr[i].getElementsByTagName('td');
        
        for (let j = 0; j < td.length - 1; j++) { // Ignorer la colonne des actions
            const txtValue = td[j].textContent || td[j].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                found = true;
                break;
            }
        }
        
        tr[i].style.display = found ? '' : 'none';
    }
}

// Activer la recherche en appuyant sur Entrée
document.getElementById('searchInput').addEventListener('keyup', function(event) {
    if (event.key === 'Enter') {
        searchUsers();
    }
});
</script>

</body>
</html>