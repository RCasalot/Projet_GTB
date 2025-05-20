<?php
// Désactiver le rapport d'erreurs qui pourrait interférer avec les en-têtes
error_reporting(0);
ini_set('display_errors', 0);

// Démarrer la session
session_start();

// Connexion à la base de données
$mysqli = new mysqli("localhost", "root", "", "mairie");

// Vérification de la connexion
if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

// Messages d'erreur et de succès
$error_message = "";
$success_message = "";

// Définir l'action actuelle (par défaut: liste)
$action = isset($_GET['action']) ? $_GET['action'] : 'liste';
$client_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Récupérer le client spécifique si nécessaire
$client = null;
if ($client_id > 0 && ($action == 'details' || $action == 'modifier' || $action == 'supprimer')) {
    try {
        $query = "SELECT * FROM clients WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $client = $result->fetch_assoc();
        } else {
            $error_message = "Client non trouvé.";
            $action = 'liste'; // Rediriger vers la liste si le client n'existe pas
        }
    } catch (Exception $e) {
        $error_message = "Une erreur est survenue : " . $e->getMessage();
        $action = 'liste';
    }
}

// Traitement de la mise à jour d'un client
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_client'])) {
    $client_id = intval($_POST['client_id']);
    $nom_ville = $_POST['nom_ville'];
    $code_postal = $_POST['code_postal'];
    $nom_elu = $_POST['nom_elu'];
    $fonction_elu = $_POST['fonction_elu'];
    $email_elu = $_POST['email_elu'];
    $telephone_elu = $_POST['telephone_elu'];
    $adresse = isset($_POST['adresse']) ? $_POST['adresse'] : '';
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    
    // Validation des champs
    if (empty($nom_ville) || empty($code_postal) || empty($nom_elu) || empty($fonction_elu) || empty($email_elu) || empty($telephone_elu)) {
        $error_message = "Tous les champs marqués d'un astérisque (*) sont obligatoires.";
    } else {
        try {
            // Mettre à jour les informations du client
            $update_query = "UPDATE clients SET 
                nom_ville = ?, 
                code_postal = ?, 
                nom_elu = ?, 
                fonction_elu = ?, 
                email_elu = ?, 
                telephone_elu = ?, 
                adresse = ?, 
                notes = ? 
                WHERE id = ?";
            
            $update_stmt = $mysqli->prepare($update_query);
            $update_stmt->bind_param("ssssssssi", $nom_ville, $code_postal, $nom_elu, $fonction_elu, $email_elu, $telephone_elu, $adresse, $notes, $client_id);
            
            if ($update_stmt->execute()) {
                $success_message = "Les informations du client ont été mises à jour avec succès.";
                $action = 'liste'; // Retourner à la liste après la mise à jour
            } else {
                $error_message = "Erreur lors de la mise à jour des informations : " . $mysqli->error;
            }
        } catch (Exception $e) {
            $error_message = "Une erreur est survenue : " . $e->getMessage();
        }
    }
}

// Traitement de la suppression d'un client
if ($action == 'supprimer' && isset($_GET['confirm']) && $_GET['confirm'] == "yes") {
    try {
        $delete_query = "DELETE FROM clients WHERE id = ?";
        $delete_stmt = $mysqli->prepare($delete_query);
        $delete_stmt->bind_param("i", $client_id);
        
        if ($delete_stmt->execute()) {
            $success_message = "Le client a été supprimé avec succès.";
            $action = 'liste'; // Retourner à la liste après la suppression
        } else {
            $error_message = "Erreur lors de la suppression du client : " . $mysqli->error;
        }
    } catch (Exception $e) {
        $error_message = "Une erreur est survenue : " . $e->getMessage();
    }
}

// Récupérer la liste des clients pour l'affichage
$clients = [];
if ($action == 'liste') {
    try {
        $query = "SELECT * FROM clients ORDER BY nom_ville ASC";
        $result = $mysqli->query($query);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $clients[] = $row;
            }
            $result->free();
        } else {
            $error_message = "Erreur lors de la récupération des clients : " . $mysqli->error;
        }
    } catch (Exception $e) {
        $error_message = "Une erreur est survenue : " . $e->getMessage();
    }
}

// Vérifie que l'utilisateur est connecté et qu'il a le statut "admin"
if (!isset($_SESSION["Statut"]) || $_SESSION["Statut"] !== "Administrateur") {
    // Redirige vers la page de connexion si l'utilisateur n'est pas autorisé
    header("Location: ../index.php");
    exit;
}

// Récupérer le nom de l'utilisateur depuis la session
$Nom = isset($_SESSION["Nom"]) ? $_SESSION["Nom"] : 'Invité';
$Statut = isset($_SESSION["Statut"]) ? $_SESSION["Statut"] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des clients</title>
    <link rel="stylesheet" href="../backend/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            color: black;
        }
        .container {
            width: 90%;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            color: black;
        }
        h2, h3 {
            color: black;
        }
        .error {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .client-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .client-table th, .client-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .client-table th {
            background-color: #665264;
            color: white;
        }
        .client-table tr:hover {
            background-color: #f5f5f5;
        }
        .client-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .btn-action {
            display: inline-block;
            padding: 5px 10px;
            margin: 2px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
        }
        .btn-details {
            background-color: #2196F3;
        }
        .btn-edit {
            background-color: #4CAF50;
        }
        .btn-delete {
            background-color: #F44336;
        }
        .btn-action:hover {
            opacity: 0.8;
        }
        .btn-add {
            background-color: #665264;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        .btn-add:hover {
            background-color: #563f54;
        }
        .search-container {
            margin-bottom: 20px;
        }
        .search-container input[type=text] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-container button {
            padding: 10px 15px;
            background-color: #665264;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-container button:hover {
            background-color: #563f54;
        }
        .empty-message {
            padding: 20px;
            text-align: center;
            background-color: #f5f5f5;
            border-radius: 4px;
            margin-top: 20px;
        }
        /* Styles pour la vue détails */
        .client-details {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .detail-group {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .detail-group:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: bold;
            color: #665264;
            margin-bottom: 5px;
        }
        .detail-value {
            margin-top: 5px;
            font-size: 16px;
        }
        /* Styles pour le formulaire de modification */
        form {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #665264;
        }
        .required:after {
            content: " *";
            color: red;
        }
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        .section-title {
            margin-top: 30px;
            border-bottom: 2px solid #665264;
            padding-bottom: 10px;
            color: #665264;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            margin: 10px 5px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-primary {
            background-color: #665264;
        }
        .btn-save {
            background-color: #4CAF50;
        }
        .btn-cancel {
            background-color: #607D8B;
        }
        .btn:hover {
            opacity: 0.8;
        }
        .actions-container {
            margin-top: 20px;
            text-align: center;
        }
        /* Styles pour la confirmation de suppression */
        .delete-confirmation {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 20px;
        }
        .delete-confirmation p {
            margin-bottom: 20px;
            font-size: 18px;
        }
        .btn-danger {
            background-color: #F44336;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
<div class="header" style="background-color:#665264">
    <img src="../images/smica.png" width="500">
</div>

<div class="topnav">
    <a href="admin.php"><i class="fa fa-fw fa-home"></i> Accueil</a>
    <a href="liste_clients.php" class="active"><i class="fa-solid fa-list"></i> Liste des clients</a>
    <a href="https://www.carnus.fr" target="_blank"><i class="fa-solid fa-circle-info"></i> Info</a>
    <a href="../backend/logout.php" style="float:right"><i class="fa-solid fa-power-off"></i> Déconnexion</a>
    <a href style="float:right"><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($Nom); ?></a>
</div>

<div class="container">
    <?php if (!empty($error_message)): ?>
    <div class="error">
        <h3><?php echo $error_message; ?></h3>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($success_message)): ?>
    <div class="success">
        <h3><?php echo $success_message; ?></h3>
    </div>
    <?php endif; ?>
    
    <?php if ($action == 'liste'): ?>
        <!-- LISTE DES CLIENTS -->
        <h2><i class="fa-solid fa-city"></i> Liste des clients</h2>
        
        <a href="ajouter_client.php" class="btn-add">
            <i class="fa-solid fa-plus"></i> Ajouter un nouveau client
        </a>
        
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Rechercher un client..." onkeyup="searchTable()">
            <button type="button" onclick="searchTable()">
                <i class="fa-solid fa-search"></i> Rechercher
            </button>
        </div>
        
        <?php if (count($clients) > 0): ?>
            <table class="client-table" id="clientTable">
                <thead>
                    <tr>
                        <th>Ville</th>
                        <th>Code postal</th>
                        <th>Nom de l'élu</th>
                        <th>Fonction</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['nom_ville']); ?></td>
                        <td><?php echo htmlspecialchars($client['code_postal']); ?></td>
                        <td><?php echo htmlspecialchars($client['nom_elu']); ?></td>
                        <td><?php echo htmlspecialchars($client['fonction_elu']); ?></td>
                        <td><?php echo htmlspecialchars($client['email_elu']); ?></td>
                        <td><?php echo htmlspecialchars($client['telephone_elu']); ?></td>
                        <td>
                            <a href="?action=details&id=<?php echo $client['id']; ?>" class="btn-action btn-details" title="Voir les détails">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="?action=modifier&id=<?php echo $client['id']; ?>" class="btn-action btn-edit" title="Modifier">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="?action=supprimer&id=<?php echo $client['id']; ?>" class="btn-action btn-delete" title="Supprimer">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-message">
                <i class="fa-solid fa-info-circle fa-2x"></i>
                <p>Aucun client n'a été trouvé dans la base de données.</p>
                <p>Cliquez sur "Ajouter un nouveau client" pour commencer.</p>
            </div>
        <?php endif; ?>
        
    <?php elseif ($action == 'details' && $client): ?>
        <!-- DÉTAILS D'UN CLIENT -->
        <h2><i class="fa-solid fa-city"></i> Détails du client</h2>
        
        <div class="client-details">
            <h3 class="section-title">Informations de la commune</h3>
            
            <div class="detail-group">
                <div class="detail-label">Nom de la ville</div>
                <div class="detail-value"><?php echo htmlspecialchars($client['nom_ville']); ?></div>
            </div>
            
            <div class="detail-group">
                <div class="detail-label">Code postal</div>
                <div class="detail-value"><?php echo htmlspecialchars($client['code_postal']); ?></div>
            </div>
            
            <h3 class="section-title">Informations de l'élu</h3>
            
            <div class="detail-group">
                <div class="detail-label">Nom de l'élu</div>
                <div class="detail-value"><?php echo htmlspecialchars($client['nom_elu']); ?></div>
            </div>
            
            <div class="detail-group">
                <div class="detail-label">Fonction</div>
                <div class="detail-value"><?php echo htmlspecialchars($client['fonction_elu']); ?></div>
            </div>
            
            <div class="detail-group">
                <div class="detail-label">Email</div>
                <div class="detail-value"><?php echo htmlspecialchars($client['email_elu']); ?></div>
            </div>
            
            <div class="detail-group">
                <div class="detail-label">Téléphone</div>
                <div class="detail-value"><?php echo htmlspecialchars($client['telephone_elu']); ?></div>
            </div>
            
            <?php if (isset($client['adresse']) && !empty($client['adresse'])): ?>
            <div class="detail-group">
                <div class="detail-label">Adresse</div>
                <div class="detail-value"><?php echo htmlspecialchars($client['adresse']); ?></div>
            </div>
            <?php endif; ?>
            
            <?php if (isset($client['notes']) && !empty($client['notes'])): ?>
            <h3 class="section-title">Notes supplémentaires</h3>
            <div class="detail-group">
                <div class="detail-value"><?php echo nl2br(htmlspecialchars($client['notes'])); ?></div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="actions-container">
            <a href="?action=modifier&id=<?php echo $client['id']; ?>" class="btn btn-edit">
                <i class="fa-solid fa-pen-to-square"></i> Modifier
            </a>
            <a href="?action=liste" class="btn btn-back">
                <i class="fa-solid fa-arrow-left"></i> Retour à la liste
            </a>
        </div>
        
    <?php elseif ($action == 'modifier' && $client): ?>
        <!-- MODIFICATION D'UN CLIENT -->
        <h2><i class="fa-solid fa-pen-to-square"></i> Modifier un client</h2>
        
        <form method="post" action="">
            <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
            
            <h3 class="section-title">Informations de la commune</h3>
            
            <div class="form-group">
                <label for="nom_ville" class="required">Nom de la ville</label>
                <input type="text" id="nom_ville" name="nom_ville" value="<?php echo htmlspecialchars($client['nom_ville']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="code_postal" class="required">Code postal</label>
                <input type="text" id="code_postal" name="code_postal" value="<?php echo htmlspecialchars($client['code_postal']); ?>" required>
            </div>
            
            <h3 class="section-title">Informations de l'élu</h3>
            
            <div class="form-group">
                <label for="nom_elu" class="required">Nom de l'élu</label>
                <input type="text" id="nom_elu" name="nom_elu" value="<?php echo htmlspecialchars($client['nom_elu']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="fonction_elu" class="required">Fonction</label>
                <input type="text" id="fonction_elu" name="fonction_elu" value="<?php echo htmlspecialchars($client['fonction_elu']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email_elu" class="required">Email</label>
                <input type="email" id="email_elu" name="email_elu" value="<?php echo htmlspecialchars($client['email_elu']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="telephone_elu" class="required">Téléphone</label>
                <input type="tel" id="telephone_elu" name="telephone_elu" value="<?php echo htmlspecialchars($client['telephone_elu']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="adresse">Adresse</label>
                <input type="text" id="adresse" name="adresse" value="<?php echo isset($client['adresse']) ? htmlspecialchars($client['adresse']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="notes">Notes supplémentaires</label>
                <textarea id="notes" name="notes"><?php echo isset($client['notes']) ? htmlspecialchars($client['notes']) : ''; ?></textarea>
            </div>
            
            <div class="actions-container">
                <button type="submit" name="update_client" class="btn btn-save">
                    <i class="fa-solid fa-floppy-disk"></i> Enregistrer les modifications
                </button>
                <a href="?action=liste" class="btn btn-cancel">
                    <i class="fa-solid fa-ban"></i> Annuler
                </a>
            </div>
        </form>
        
    <?php elseif ($action == 'supprimer' && $client): ?>
        <!-- CONFIRMATION DE SUPPRESSION -->
        <h2><i class="fa-solid fa-trash"></i> Supprimer le client</h2>
        
        <div class="delete-confirmation">
            <i class="fa-solid fa-exclamation-triangle fa-3x" style="color: #F44336; margin-bottom: 20px;"></i>
            <h3>Confirmation de suppression</h3>
            <p>Êtes-vous sûr de vouloir supprimer le client <strong><?php echo htmlspecialchars($client['nom_ville']); ?></strong> ?</p>
            <p>Cette action est irréversible.</p>
            
            <div class="actions-container">
                <a href="?action=supprimer&id=<?php echo $client['id']; ?>&confirm=yes" class="btn btn-danger">
                    <i class="fa-solid fa-check"></i> Oui, supprimer
                </a>
                <a href="?action=liste" class="btn btn-cancel">
                    <i class="fa-solid fa-ban"></i> Non, annuler
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Fonction de recherche pour filtrer le tableau des clients
function searchTable() {
    const input = document.getElementById("searchInput");
    const filter = input.value.toUpperCase();
    const table = document.getElementById("clientTable");
    const rows = table.getElementsByTagName("tr");
    
    for (let i = 1; i < rows.length; i++) {
        let found = false;
        const cells = rows[i].getElementsByTagName("td");
        
        for (let j = 0; j < cells.length - 1; j++) { // Ignorer la dernière colonne (actions)
            const cell = cells[j];
            if (cell) {
                const text = cell.textContent || cell.innerText;
                if (text.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        rows[i].style.display = found ? "" : "none";
    }
}
</script>

</body>
</html>