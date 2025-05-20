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

// Vérifie que l'utilisateur est connecté et qu'il a le statut "admin"
if (!isset($_SESSION["Statut"]) || $_SESSION["Statut"] !== "Administrateur") {
    // Redirige vers la page de connexion si l'utilisateur n'est pas autorisé
    header("Location: ../index.php");
    exit;
}

// Récupérer le nom de l'utilisateur depuis la session
$Nom = isset($_SESSION["Nom"]) ? $_SESSION["Nom"] : 'Invité';
$Statut = isset($_SESSION["Statut"]) ? $_SESSION["Statut"] : '';

// Variable pour stocker les messages
$message = "";

// Préparation de la requête pour récupérer tous les capteurs avec les informations des bâtiments
$query = "SELECT c.id, c.id_batiment, c.type_capteur, c.statut, b.nom_batiment, b.type_batiment, b.ville 
          FROM capteurs c
          JOIN batiments b ON c.id_batiment = b.id
          ORDER BY b.ville, b.nom_batiment, c.type_capteur";

$result = $mysqli->query($query);

// Traitement pour activer/désactiver un capteur
if (isset($_POST['action']) && isset($_POST['capteur_id'])) {
    $capteurId = (int)$_POST['capteur_id'];
    $action = $_POST['action'];
    
    $nouveauStatut = ($action === 'activer') ? 'Actif' : 'Inactif';
    
    $updateQuery = "UPDATE capteurs SET statut = ? WHERE id = ?";
    $stmt = $mysqli->prepare($updateQuery);
    $stmt->bind_param("si", $nouveauStatut, $capteurId);
    
    if ($stmt->execute()) {
        $message = "<div class='success'>Le capteur a été " . ($action === 'activer' ? 'activé' : 'désactivé') . " avec succès.</div>";
        // Recharger les données
        $result = $mysqli->query($query);
    } else {
        $message = "<div class='error'>Erreur lors de la mise à jour du statut : " . $mysqli->error . "</div>";
    }
    
    $stmt->close();
}

// Filtrage par ville, type de bâtiment ou type de capteur
$filtreVille = isset($_GET['ville']) ? $_GET['ville'] : '';
$filtreBatiment = isset($_GET['type_batiment']) ? $_GET['type_batiment'] : '';
$filtreCapteur = isset($_GET['type_capteur']) ? $_GET['type_capteur'] : '';
$filtreStatut = isset($_GET['statut']) ? $_GET['statut'] : '';

$queryVilles = "SELECT DISTINCT ville FROM batiments ORDER BY ville";
$resultVilles = $mysqli->query($queryVilles);

$queryBatiments = "SELECT DISTINCT type_batiment FROM batiments ORDER BY type_batiment";
$resultBatiments = $mysqli->query($queryBatiments);

$queryCapteurs = "SELECT DISTINCT type_capteur FROM capteurs ORDER BY type_capteur";
$resultCapteurs = $mysqli->query($queryCapteurs);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Liste des Capteurs</title>
    <link rel="stylesheet" href="../backend/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            color: black;
            font-family: Arial, sans-serif;
        }
        .container {
            width: 90%;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #665264;
        }
        .capteurs-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .capteurs-table th {
            background-color: #665264;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .capteurs-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .capteurs-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .capteurs-table tr:hover {
            background-color: #e0e0e0;
        }
        .status-active {
            color: green;
            font-weight: bold;
        }
        .status-inactive {
            color: red;
            font-weight: bold;
        }
        .action-btn {
            padding: 5px 10px;
            margin-right: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .activate-btn {
            background-color: #4CAF50;
            color: white;
        }
        .deactivate-btn {
            background-color: #f44336;
            color: white;
        }
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 8px;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        .filter-group label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        .filter-group select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .filter-btn {
            background-color: #665264;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 15px;
            cursor: pointer;
            margin-top: auto;
        }
        .filter-btn:hover {
            background-color: #563f54;
        }
        .reset-btn {
            background-color: #666;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 15px;
            cursor: pointer;
            margin-top: auto;
        }
        .reset-btn:hover {
            background-color: #555;
        }
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .stats-summary {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .stat-item {
            text-align: center;
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            min-width: 150px;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #665264;
        }
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
        .capteur-icon {
            width: 20px;
            text-align: center;
            margin-right: 5px;
        }
        .export-btn {
            background-color: #4285F4;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 15px;
            cursor: pointer;
            margin-left: 10px;
        }
        .export-btn:hover {
            background-color: #3367D6;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a {
            color: black;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
        }
        .pagination a.active {
            background-color: #665264;
            color: white;
            border: 1px solid #665264;
        }
        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
    </style>
</head>

<body>
<div class="header" style="background-color:#665264">
    <img src="../images/smica.png" width="500">
</div>

<div class="topnav">
    <a href="admin.php"><i class="fa fa-fw fa-home"></i> Accueil</a>
    <a href="liste_capteurs.php" class="active"><i class="fa-solid fa-microchip"></i> Capteurs</a>
    <a href="https://www.carnus.fr" target="_blank"><i class="fa-solid fa-circle-info"></i> Info</a>
    <a href="../backend/logout.php" style="float:right"><i class="fa-solid fa-power-off"></i> Déconnexion</a>
    <a href style="float:right"><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($Nom); ?></a>
</div>

<div class="container">
    <h1><i class="fa-solid fa-microchip"></i> Liste des Capteurs</h1>
    
    <?php echo $message; ?>
    
    <!-- Statistiques des capteurs -->
    <?php
    // Calculer les statistiques
    $totalCapteurs = 0;
    $capteursActifs = 0;
    $capteursInactifs = 0;
    $batimentsEquipes = 0;
    $typesUniques = [];
    $batimentsUniques = [];
    
    if ($result) {
        $totalCapteurs = $result->num_rows;
        
        // Réinitialiser le pointeur de résultat
        $result->data_seek(0);
        
        while ($row = $result->fetch_assoc()) {
            if ($row['statut'] == 'Actif') {
                $capteursActifs++;
            } else {
                $capteursInactifs++;
            }
            
            // Compter les types uniques
            $typesUniques[$row['type_capteur']] = true;
            
            // Compter les bâtiments équipés
            $batimentsUniques[$row['id_batiment']] = true;
        }
        
        $batimentsEquipes = count($batimentsUniques);
        
        // Réinitialiser le pointeur de résultat pour l'affichage principal
        $result->data_seek(0);
    }
    ?>
    
    <div class="stats-summary">
        <div class="stat-item">
            <div class="stat-number"><?php echo $totalCapteurs; ?></div>
            <div class="stat-label">Total des capteurs</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $capteursActifs; ?></div>
            <div class="stat-label">Capteurs actifs</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $capteursInactifs; ?></div>
            <div class="stat-label">Capteurs inactifs</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $batimentsEquipes; ?></div>
            <div class="stat-label">Bâtiments équipés</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo count($typesUniques); ?></div>
            <div class="stat-label">Types de capteurs</div>
        </div>
    </div>
    
    <!-- Filtres -->
    <form method="GET" class="filters">
        <div class="filter-group">
            <label for="ville">Ville :</label>
            <select id="ville" name="ville">
                <option value="">Toutes les villes</option>
                <?php
                if ($resultVilles) {
                    while ($row = $resultVilles->fetch_assoc()) {
                        $selected = ($filtreVille == $row['ville']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($row['ville']) . '" ' . $selected . '>' . 
                             htmlspecialchars($row['ville']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="type_batiment">Type de bâtiment :</label>
            <select id="type_batiment" name="type_batiment">
                <option value="">Tous les bâtiments</option>
                <?php
                if ($resultBatiments) {
                    while ($row = $resultBatiments->fetch_assoc()) {
                        $selected = ($filtreBatiment == $row['type_batiment']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($row['type_batiment']) . '" ' . $selected . '>' . 
                             htmlspecialchars($row['type_batiment']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="type_capteur">Type de capteur :</label>
            <select id="type_capteur" name="type_capteur">
                <option value="">Tous les capteurs</option>
                <?php
                if ($resultCapteurs) {
                    while ($row = $resultCapteurs->fetch_assoc()) {
                        $selected = ($filtreCapteur == $row['type_capteur']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($row['type_capteur']) . '" ' . $selected . '>' . 
                             htmlspecialchars($row['type_capteur']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="statut">Statut :</label>
            <select id="statut" name="statut">
                <option value="">Tous les statuts</option>
                <option value="Actif" <?php echo ($filtreStatut == 'Actif') ? 'selected' : ''; ?>>Actif</option>
                <option value="Inactif" <?php echo ($filtreStatut == 'Inactif') ? 'selected' : ''; ?>>Inactif</option>
            </select>
        </div>
        
        <div class="filter-group">
            <button type="submit" class="filter-btn"><i class="fa-solid fa-filter"></i> Filtrer</button>
        </div>
        
        <div class="filter-group">
            <a href="liste_capteurs.php" class="reset-btn" style="text-decoration: none; display: inline-block; text-align: center;">
                <i class="fa-solid fa-rotate"></i> Réinitialiser
            </a>
        </div>
        
        <div class="filter-group">
            <button type="button" class="export-btn" onclick="exportTableToCSV('capteurs_export.csv')">
                <i class="fa-solid fa-file-export"></i> Exporter CSV
            </button>
        </div>
    </form>
    
    <?php
    // Appliquer les filtres à la requête
    $whereClause = [];
    $filterParams = [];
    $filterTypes = "";
    
    if (!empty($filtreVille)) {
        $whereClause[] = "b.ville = ?";
        $filterParams[] = $filtreVille;
        $filterTypes .= "s";
    }
    
    if (!empty($filtreBatiment)) {
        $whereClause[] = "b.type_batiment = ?";
        $filterParams[] = $filtreBatiment;
        $filterTypes .= "s";
    }
    
    if (!empty($filtreCapteur)) {
        $whereClause[] = "c.type_capteur = ?";
        $filterParams[] = $filtreCapteur;
        $filterTypes .= "s";
    }
    
    if (!empty($filtreStatut)) {
        $whereClause[] = "c.statut = ?";
        $filterParams[] = $filtreStatut;
        $filterTypes .= "s";
    }
    
    $queryFiltered = "SELECT c.id, c.id_batiment, c.type_capteur, c.statut, 
                      b.nom_batiment, b.type_batiment, b.ville 
                      FROM capteurs c
                      JOIN batiments b ON c.id_batiment = b.id";
    
    if (!empty($whereClause)) {
        $queryFiltered .= " WHERE " . implode(" AND ", $whereClause);
    }
    
    $queryFiltered .= " ORDER BY b.ville, b.nom_batiment, c.type_capteur";
    
    // Pagination
    $itemsPerPage = 20;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $itemsPerPage;
    
    // Ajouter LIMIT pour la pagination
    $queryPaginated = $queryFiltered . " LIMIT ?, ?";
    
    // Préparer la requête avec les filtres et la pagination
    $stmt = $mysqli->prepare($queryPaginated);
    
    if (!empty($filterParams)) {
        // Ajouter les paramètres de pagination
        $filterParams[] = $offset;
        $filterParams[] = $itemsPerPage;
        $filterTypes .= "ii";
        
        // Lier les paramètres
        $bindParams = array_merge([$stmt, $filterTypes], $filterParams);
        call_user_func_array('mysqli_stmt_bind_param', $bindParams);
    } else {
        // Seulement les paramètres de pagination
        $stmt->bind_param("ii", $offset, $itemsPerPage);
    }
    
    $stmt->execute();
    $resultFiltered = $stmt->get_result();
    
    // Requête pour compter le nombre total d'éléments après filtrage (pour la pagination)
    $countStmt = $mysqli->prepare(str_replace("SELECT c.id, c.id_batiment, c.type_capteur, c.statut, 
                      b.nom_batiment, b.type_batiment, b.ville", "SELECT COUNT(*) as total", $queryFiltered));
    
    if (!empty($filterParams)) {
        // Supprimer les paramètres de pagination
        $countParams = array_slice($filterParams, 0, -2);
        $countBindParams = array_merge([$countStmt, substr($filterTypes, 0, -2)], $countParams);
        call_user_func_array('mysqli_stmt_bind_param', $countBindParams);
    }
    
    $countStmt->execute();
    $countResult = $countStmt->get_result()->fetch_assoc();
    $totalItems = $countResult['total'];
    $totalPages = ceil($totalItems / $itemsPerPage);
    ?>
    
    <!-- Tableau des capteurs -->
    <table class="capteurs-table" id="capteurs-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ville</th>
                <th>Bâtiment</th>
                <th>Type de bâtiment</th>
                <th>Type de capteur</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($resultFiltered && $resultFiltered->num_rows > 0) {
                while ($row = $resultFiltered->fetch_assoc()) {
                    $statusClass = ($row['statut'] == 'Actif') ? 'status-active' : 'status-inactive';
                    $capteurIcon = '';
                    
                    // Déterminer l'icône en fonction du type de capteur
                    if (strpos($row['type_capteur'], 'CO2') !== false) {
                        $capteurIcon = '<i class="fa-solid fa-wind capteur-icon"></i>';
                    } elseif (strpos($row['type_capteur'], 'Multi-Entrées') !== false) {
                        $capteurIcon = '<i class="fa-solid fa-door-open capteur-icon"></i>';
                    } elseif (strpos($row['type_capteur'], 'Flash\'O') !== false) {
                        $capteurIcon = '<i class="fa-solid fa-faucet capteur-icon"></i>';
                    } else {
                        $capteurIcon = '<i class="fa-solid fa-microchip capteur-icon"></i>';
                    }
                    
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>{$row['ville']}</td>";
                    echo "<td>{$row['nom_batiment']}</td>";
                    echo "<td>";
                    
                    // Icône selon le type de bâtiment
                    if ($row['type_batiment'] == 'mairie') {
                        echo '<i class="fa-solid fa-city"></i> Mairie';
                    } elseif ($row['type_batiment'] == 'gymnase') {
                        echo '<i class="fa-solid fa-dumbbell"></i> Gymnase';
                    } elseif ($row['type_batiment'] == 'ecole') {
                        echo '<i class="fa-solid fa-school"></i> École';
                    } else {
                        echo htmlspecialchars($row['type_batiment']);
                    }
                    
                    echo "</td>";
                    echo "<td>{$capteurIcon} {$row['type_capteur']}</td>";
                    echo "<td class='{$statusClass}'>{$row['statut']}</td>";
                    echo "<td>";
                    
                    // Formulaires pour les actions (activer/désactiver)
                    if ($row['statut'] == 'Inactif') {
                        echo "<form method='POST' style='display:inline;'>
                              <input type='hidden' name='capteur_id' value='{$row['id']}'>
                              <input type='hidden' name='action' value='activer'>
                              <button type='submit' class='action-btn activate-btn'>
                                <i class='fa-solid fa-toggle-on'></i> Activer
                              </button>
                              </form>";
                    } else {
                        echo "<form method='POST' style='display:inline;'>
                              <input type='hidden' name='capteur_id' value='{$row['id']}'>
                              <input type='hidden' name='action' value='desactiver'>
                              <button type='submit' class='action-btn deactivate-btn'>
                                <i class='fa-solid fa-toggle-off'></i> Désactiver
                              </button>
                              </form>";
                    }
                    
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7' style='text-align:center;'>Aucun capteur trouvé</td></tr>";
            }
            ?>
        </tbody>
    </table>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php
        // Afficher "Précédent" s'il y a des pages précédentes
        if ($page > 1) {
            $prevPage = $page - 1;
            echo "<a href='?page={$prevPage}";
            if (!empty($filtreVille)) echo "&ville=" . urlencode($filtreVille);
            if (!empty($filtreBatiment)) echo "&type_batiment=" . urlencode($filtreBatiment);
            if (!empty($filtreCapteur)) echo "&type_capteur=" . urlencode($filtreCapteur);
            if (!empty($filtreStatut)) echo "&statut=" . urlencode($filtreStatut);
            echo "'>&laquo; Précédent</a>";
        }
        
        // Afficher les numéros de page
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $page + 2);
        
        for ($i = $startPage; $i <= $endPage; $i++) {
            $active = ($i == $page) ? "active" : "";
            echo "<a class='{$active}' href='?page={$i}";
            if (!empty($filtreVille)) echo "&ville=" . urlencode($filtreVille);
            if (!empty($filtreBatiment)) echo "&type_batiment=" . urlencode($filtreBatiment);
            if (!empty($filtreCapteur)) echo "&type_capteur=" . urlencode($filtreCapteur);
            if (!empty($filtreStatut)) echo "&statut=" . urlencode($filtreStatut);
            echo "'>{$i}</a>";
        }
        
        // Afficher "Suivant" s'il y a des pages suivantes
        if ($page < $totalPages) {
            $nextPage = $page + 1;
            echo "<a href='?page={$nextPage}";
            if (!empty($filtreVille)) echo "&ville=" . urlencode($filtreVille);
            if (!empty($filtreBatiment)) echo "&type_batiment=" . urlencode($filtreBatiment);
            if (!empty($filtreCapteur)) echo "&type_capteur=" . urlencode($filtreCapteur);
            if (!empty($filtreStatut)) echo "&statut=" . urlencode($filtreStatut);
            echo "'>Suivant &raquo;</a>";
        }
        ?>
    </div>
    <?php endif; ?>
</div>

<script>
// Fonction pour exporter le tableau en CSV
function exportTableToCSV(filename) {
    var csv = [];
    var rows = document.querySelectorAll("#capteurs-table tr");
    
    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll("td, th");
        
        for (var j = 0; j < cols.length; j++) {
            // Récupérer le texte sans les icônes et nettoyer
            var text = cols[j].innerText || cols[j].textContent;
            text = text.replace(/"/g, '""'); // Échapper les guillemets
            row.push('"' + text.trim() + '"');
        }
        
        csv.push(row.join(","));
    }
    
    // Télécharger le fichier CSV
    downloadCSV(csv.join("\n"), filename);
}

function downloadCSV(csv, filename) {
    var csvFile;
    var downloadLink;
    
    // Créer un objet Blob pour le CSV
    csvFile = new Blob([csv], {type: "text/csv;charset=utf-8;"});
    
    // Créer un lien de téléchargement
    downloadLink = document.createElement("a");
    
    // Nom du fichier
    downloadLink.download = filename;
    
    // Créer un lien vers le fichier
    downloadLink.href = window.URL.createObjectURL(csvFile);
    
    // Cacher le lien
    downloadLink.style.display = "none";
    
    // Ajouter le lien au document
    document.body.appendChild(downloadLink);
    
    // Cliquer sur le lien
    downloadLink.click();
    
    // Nettoyer
    document.body.removeChild(downloadLink);
}

// Animation pour les statistiques
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>

</body>
</html>
<?php
// Fermer la connexion à la base de données
$mysqli->close();
?>