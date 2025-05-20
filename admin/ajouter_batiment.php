<?php
// Désactiver le rapport d'erreurs qui pourrait interférer avec les en-têtes
error_reporting(0);
ini_set('display_errors', 0);

// Démarrer la session
session_start();

// Activer la mise en mémoire tampon de sortie pour éviter les problèmes d'en-têtes
ob_start();

// Connexion à la base de données
$mysqli = new mysqli("localhost", "root", "", "mairie");

// Vérification de la connexion
if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

// Variable pour stocker les messages d'erreur
$error_message = "";
$success_message = "";

// Si les tables n'existent pas, les créer
$tableExists = $mysqli->query("SHOW TABLES LIKE 'batiments'");
if ($tableExists->num_rows == 0) {
    // Créer la table batiments avec une structure améliorée
    $createBatimentsTable = "CREATE TABLE batiments (
        id_batiment INT AUTO_INCREMENT PRIMARY KEY,
        nom_batiment VARCHAR(255) NOT NULL,
        type_batiment VARCHAR(50) NOT NULL,
        adresse VARCHAR(255) NOT NULL,
        code_postal VARCHAR(10) NOT NULL,
        ville VARCHAR(100) NOT NULL,
        commentaire TEXT,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$mysqli->query($createBatimentsTable)) {
        $error_message = "Erreur lors de la création de la table batiments : " . $mysqli->error;
    }
}

$tableExists = $mysqli->query("SHOW TABLES LIKE 'capteurs'");
if ($tableExists->num_rows == 0) {
    // Créer la table capteurs avec une structure améliorée
    $createCapteursTable = "CREATE TABLE capteurs (
        id_capteur INT AUTO_INCREMENT PRIMARY KEY,
        id_batiment INT NOT NULL,
        type_capteur VARCHAR(100) NOT NULL,
        statut VARCHAR(50) DEFAULT 'Actif',
        date_installation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_batiment) REFERENCES batiments(id_batiment) ON DELETE CASCADE
    )";
    
    if (!$mysqli->query($createCapteursTable)) {
        $error_message = "Erreur lors de la création de la table capteurs : " . $mysqli->error;
    }
}

// Définition des types de capteurs disponibles avec mappage pour la BD
$types_capteurs = [
    'Capteur CO2 LoRaWAN',
    'Contrôleur Multi-Entrées',
    'Capteur Flash\'O'
];

// Mappage entre les ID HTML et les noms à stocker dans la base de données
$capteurMap = [
    'CapteurCO2LoRaWAN' => 'Capteur CO2 LoRaWAN',
    'ControleurMultiEntrees' => 'Contrôleur Multi-Entrées',
    'CapteurFlashO' => 'Capteur Flash\'O'
];

// Types de bâtiments disponibles
$types_batiments = [
    'mairie' => 'Mairie',
    'gymnase' => 'Gymnase',
    'ecole' => 'École'
];

// Traitement du formulaire
if (isset($_POST["submit"])) {
    try {
        $ville = $_POST["ville"];
        $codePostal = $_POST["codePostal"];
        $nombreBatiments = $_POST["nombreBatiments"];
        
        // Validation du nombre de bâtiments
        if (!is_numeric($nombreBatiments) || $nombreBatiments < 1 || $nombreBatiments > 3) {
            throw new Exception("Le nombre de bâtiments doit être entre 1 et 3.");
        }
        
        $successCount = 0;
        $errorBatiments = [];
        
        // Traiter chaque bâtiment
        for ($i = 1; $i <= $nombreBatiments; $i++) {
            $nomBatiment = $_POST["nomBatiment_$i"];
            $adresse = $_POST["adresse_$i"];
            $commentaire = $_POST["commentaire_$i"];
            $typeBatiment = $_POST["typeBatiment_$i"]; // Maintenant défini par un select
            
            // Traitement des capteurs pour ce bâtiment
            $capteurs = [];
            
            // Collecter tous les capteurs sélectionnés pour ce bâtiment
            foreach ($capteurMap as $capteurId => $capteurNom) {
                $checkboxName = "capteur_{$capteurId}_{$i}";
                if (isset($_POST[$checkboxName]) && $_POST[$checkboxName] == '1') {
                    $capteurs[] = [
                        'type' => $capteurId,
                        'nom' => $capteurNom
                    ];
                }
            }
            
            // Échapper les entrées utilisateur pour éviter les injections SQL
            $nomBatiment = $mysqli->real_escape_string($nomBatiment);
            $typeBatiment = $mysqli->real_escape_string($typeBatiment);
            $adresse = $mysqli->real_escape_string($adresse);
            $codePostal = $mysqli->real_escape_string($codePostal);
            $ville = $mysqli->real_escape_string($ville);
            $commentaire = $mysqli->real_escape_string($commentaire);
            
            // Insertion des données du bâtiment dans la base de données
            $query = "INSERT INTO batiments (nom_batiment, type_batiment, adresse, code_postal, ville, commentaire) 
                    VALUES ('$nomBatiment', '$typeBatiment', '$adresse', '$codePostal', '$ville', '$commentaire')";
            
            $res = $mysqli->query($query);
            
            if ($res) {
                // Récupérer l'id du bâtiment créé
                $batimentId = $mysqli->insert_id;
                
                // Insérer les capteurs sélectionnés
                $capteurSuccess = true;
                
                foreach ($capteurs as $capteur) {
                    // Convertir le type de capteur au format correct pour la BD
                    $typeCapteurOriginal = $capteur['type'];
                    $typeCapteurNormalise = isset($capteurMap[$typeCapteurOriginal]) ? 
                                        $capteurMap[$typeCapteurOriginal] : $typeCapteurOriginal;
                    
                    $typeCapteurSafe = $mysqli->real_escape_string($typeCapteurNormalise);
                    
                    $queryCapteur = "INSERT INTO capteurs (id_batiment, type_capteur, statut) 
                                    VALUES ($batimentId, '$typeCapteurSafe', 'Actif')";
                    
                    if (!$mysqli->query($queryCapteur)) {
                        $capteurSuccess = false;
                        $errorBatiments[] = "$nomBatiment: Erreur lors de l'ajout du capteur {$typeCapteurNormalise}";
                    }
                }
                
                if ($capteurSuccess) {
                    $successCount++;
                } else {
                    $errorBatiments[] = "$nomBatiment: Erreur lors de l'ajout des capteurs";
                }
            } else {
                $errorBatiments[] = "$nomBatiment: Erreur lors de l'ajout du bâtiment";
            }
        }
        
        // Vérifier si tous les bâtiments ont été ajoutés avec succès
        if ($successCount == $nombreBatiments) {
            // Stocker les informations de succès dans la session
            $_SESSION['creation_success'] = true;
            $_SESSION['nombre_batiments'] = $nombreBatiments;
            $_SESSION['ville'] = $ville;
            
            // Vider le tampon de sortie avant la redirection
            ob_end_clean();
            
            // Redirection avec JavaScript comme solution de secours
            echo "<script>window.location.replace('confirmation.php');</script>";
            
            // Redirection PHP standard
            header("Location: confirmation.php");
            exit;
        } else {
            // Certains bâtiments ont échoué
            $error_message = $successCount . " bâtiment(s) ajouté(s) sur " . $nombreBatiments . ". Erreurs: " . implode("; ", $errorBatiments);
            if ($successCount > 0) {
                $success_message = $successCount . " bâtiment(s) ajouté(s) avec succès.";
            }
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
    <title>Ajout de bâtiments</title>
    <link rel="stylesheet" href="../backend/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            color: black;
        }
        .form-container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            color: black;
        }
        .form-group {
            margin-bottom: 15px;
            color: black;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: black;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            color: black;
        }
        .form-group textarea {
            height: 100px;
            color: black;
        }
        .checkbox-group {
            margin: 15px 0;
            color: black;
        }
        .checkbox-item {
            margin-bottom: 10px;
            color: black;
            display: flex;
            align-items: center;
        }
        .checkbox-item input[type="checkbox"] {
            margin-right: 10px;
        }
        .btn-submit {
            background-color: #665264;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-submit:hover {
            background-color: #563f54;
        }
        h2, h3 {
            color: black;
        }
        .type-icon {
            margin-right: 10px;
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
        .capteurs-section {
            margin-top: 15px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 5px;
            border-left: 4px solid #665264;
            margin-bottom: 20px;
        }
        .capteurs-heading {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .capteurs-heading i {
            margin-right: 10px; 
            color: #665264;
        }
        .capteur-description {
            font-size: 0.9em;
            color: #555;
            margin-left: 25px;
        }
        .capteurs-container {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .capteur-counter {
            background-color: #665264;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 8px;
            font-size: 0.8em;
        }
        .capteur-summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #e8f5e9;
            border-radius: 5px;
            display: none;
        }
        .capteur-summary.visible {
            display: block;
        }
        .capteur-summary ul {
            list-style-type: none;
            padding-left: 0;
        }
        .capteur-summary li {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        .capteur-summary li i {
            margin-right: 10px;
            color: #2e7d32;
        }
        .selected-type {
            display: inline-block;
            padding: 5px 10px;
            margin-top: 10px;
            background-color: #e1bee7;
            border-radius: 15px;
            font-size: 0.9em;
            color: #4a148c;
        }
        .batiment-container {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f5f5f5;
            position: relative;
        }
        .batiment-header {
            background-color: #665264;
            color: white;
            padding: 10px;
            margin: -15px -15px 15px -15px;
            border-radius: 5px 5px 0 0;
            display: flex;
            align-items: center;
        }
        .batiment-header i {
            margin-right: 10px;
        }   
        .collapsed .batiment-content {
            display: none;
        }
        .toggle-batiment {
            cursor: pointer;
            position: absolute;
            top: 10px;
            right: 10px;
            color: white;
        }
        .type-batiment-select {
            background-color: #f0f0f0;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #665264;
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
</div>

<div class="form-container">
    <?php if (!empty($error_message)): ?>
    <div class="error">
        <h3><i class="fa-solid fa-circle-exclamation"></i> Erreur</h3>
        <p><?php echo $error_message; ?></p>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($success_message)): ?>
    <div class="success">
        <h3><i class="fa-solid fa-circle-check"></i> Succès</h3>
        <p><?php echo $success_message; ?></p>
    </div>
    <?php endif; ?>
    
    <h2><i class="fa-solid fa-building"></i> Ajouter des bâtiments</h2>
    
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="form-group">
            <label for="ville">Ville :</label>
            <?php
            // Essayer d'abord de récupérer les villes depuis la table clients
            $queryVilles = "SELECT DISTINCT nom_ville, code_postal, adresse_ville FROM clients ORDER BY nom_ville";
            $resultVilles = $mysqli->query($queryVilles);
            
            // Si la table clients existe et contient des données
            if ($resultVilles && $resultVilles->num_rows > 0) {
                echo '<select id="ville" name="ville" required onchange="updateInfos(this.value)">';
                echo '<option value="">-- Sélectionnez une ville --</option>';
                
                // Stocker les informations de villes dans un tableau JavaScript
                echo '<script>const villesInfo = {};</script>';
                
                while ($row = $resultVilles->fetch_assoc()) {
                    $villeNom = htmlspecialchars($row['nom_ville']);
                    $villeCodePostal = htmlspecialchars($row['code_postal']);
                    $villeAdresse = htmlspecialchars($row['adresse_ville']);
                    
                    echo '<option value="' . $villeNom . '">' . $villeNom . '</option>';
                    
                    // Stocker les informations pour un accès JavaScript
                    echo '<script>villesInfo["' . $villeNom . '"] = {
                        codePostal: "' . $villeCodePostal . '",
                        adresse: "' . $villeAdresse . '"
                    };</script>';
                }
                
                echo '</select>';
            } else {
                // Sinon, récupérer les villes distinctes depuis la table batiments (comportement original)
                $queryVillesBackup = "SELECT DISTINCT ville FROM batiments ORDER BY ville";
                $resultVillesBackup = $mysqli->query($queryVillesBackup);
                
                echo '<select id="ville" name="ville" required>';
                echo '<option value="">-- Sélectionnez une ville --</option>';
                
                if ($resultVillesBackup && $resultVillesBackup->num_rows > 0) {
                    while ($row = $resultVillesBackup->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['ville']) . '">' . 
                             htmlspecialchars($row['ville']) . '</option>';
                    }
                }
                
                echo '</select>';
            }
            ?>
        </div>
        
        <div class="form-group">
            <label for="codePostal">Code postal :</label>
            <input type="text" id="codePostal" name="codePostal" required>
        </div>
        
        <div class="form-group">
            <label for="nombreBatiments">Nombre de bâtiments à ajouter :</label>
            <select id="nombreBatiments" name="nombreBatiments" required onchange="genererFormulaires()">
                <option value="">-- Sélectionnez un nombre --</option>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        
        <!-- Conteneur pour les formulaires de bâtiments générés dynamiquement -->
        <div id="batiments-container">
            <!-- Les formulaires de bâtiments seront générés ici par JavaScript -->
        </div>
        
        <button type="submit" name="submit" class="btn-submit" style="display:none;" id="submit-button">
            <i class="fa-solid fa-floppy-disk"></i> Enregistrer
        </button>
    </form>
</div>

<script>
// Définition des icônes et descriptions pour chaque type de bâtiment
const typeBatimentInfo = {
    'mairie': {
        icon: 'fa-city',
        name: 'Mairie'
    },
    'gymnase': {
        icon: 'fa-dumbbell',
        name: 'Gymnase'
    },
    'ecole': {
        icon: 'fa-school',
        name: 'École'
    }
};

// Définition des descriptions des capteurs pour chaque type de bâtiment
const capteurDescriptions = {
    'CapteurCO2LoRaWAN': {
        'mairie': 'Contrôle la qualité de l\'air et le taux de CO2 dans les bureaux',
        'gymnase': 'Surveille la qualité de l\'air du gymnase pendant les activités sportives',
        'ecole': 'Surveille la qualité de l\'air dans les salles de classe'
    },
    'ControleurMultiEntrees': {
        'mairie': 'Surveille les accès multiples du bâtiment',
        'gymnase': 'Gère les accès et comptabilise les entrées/sorties',
        'ecole': 'Contrôle les accès et sécurise les entrées de l\'école'
    },
    'CapteurFlashO': {
        'mairie': 'Mesure la consommation d\'eau du bâtiment',
        'gymnase': 'Surveille la consommation d\'eau des douches et sanitaires',
        'ecole': 'Surveille la consommation d\'eau dans les sanitaires et la cantine'
    }
};

// Fonction pour générer les formulaires de bâtiments
function genererFormulaires() {
    const nombreBatiments = document.getElementById('nombreBatiments').value;
    const container = document.getElementById('batiments-container');
    const submitButton = document.getElementById('submit-button');
    
    // Afficher ou masquer le bouton d'envoi
    if (nombreBatiments > 0) {
        submitButton.style.display = 'block';
    } else {
        submitButton.style.display = 'none';
    }
    
    // Vider le conteneur
    container.innerHTML = '';
    
    // Types de bâtiments disponibles
    const typesBatimentsArray = ['mairie', 'gymnase', 'ecole'];
    
    // Générer un formulaire pour chaque bâtiment
    for (let i = 1; i <= nombreBatiments; i++) {
        // Définir le type par défaut en fonction de l'index (pour garantir un de chaque type si possible)
        const typeDefaultIndex = (i - 1) % typesBatimentsArray.length;
        const typeDefault = typesBatimentsArray[typeDefaultIndex];
        const typeInfo = typeBatimentInfo[typeDefault];
        
        // Créer le conteneur pour ce bâtiment
        const batimentContainer = document.createElement('div');
        batimentContainer.className = 'batiment-container';
        batimentContainer.id = `batiment-${i}`;
        
        // Créer l'en-tête avec un bouton pour réduire/agrandir
        const batimentHeader = document.createElement('div');
        batimentHeader.className = 'batiment-header';
        batimentHeader.innerHTML = `
            <i class="fa-solid ${typeInfo.icon}"></i>
            <h3>Bâtiment ${i} - ${typeInfo.name}</h3>
            <div class="toggle-batiment" onclick="toggleBatiment(${i})">
                <i class="fa-solid fa-chevron-up" id="toggle-icon-${i}"></i>
            </div>
        `;
        
        // Créer le contenu du formulaire pour ce bâtiment
        const batimentContent = document.createElement('div');
        batimentContent.className = 'batiment-content';
        
        // Section pour la sélection du type de bâtiment
        let typeSelectHTML = `
            <div class="type-batiment-select">
                <h3><i class="fa-solid fa-building-user"></i> Type de bâtiment</h3>
                <div class="form-group">
                    <label for="typeBatiment_${i}">Sélectionnez le type :</label>
                    <select id="typeBatiment_${i}" name="typeBatiment_${i}" required onchange="updateBatimentType(${i})">
        `;
        
        // Ajouter les options pour les types de bâtiments
        for (const type in typeBatimentInfo) {
            const selected = type === typeDefault ? 'selected' : '';
            typeSelectHTML += `<option value="${type}" ${selected}>${typeBatimentInfo[type].name}</option>`;
        }
        
        typeSelectHTML += `
                    </select>
                </div>
            </div>
        `;
        
        // Ajouter le reste du contenu du formulaire
        batimentContent.innerHTML = `
            ${typeSelectHTML}
            
            <div class="form-group">
                <label for="nomBatiment_${i}">Nom du bâtiment :</label>
                <input type="text" id="nomBatiment_${i}" name="nomBatiment_${i}" required>
            </div>
            
            <div class="form-group">
                <label for="adresse_${i}">Adresse :</label>
                <input type="text" id="adresse_${i}" name="adresse_${i}" required>
            </div>
            
            <div class="form-group">
                <label for="commentaire_${i}">Commentaire :</label>
                <textarea id="commentaire_${i}" name="commentaire_${i}"></textarea>
            </div>
            
            <!-- Section des capteurs pour ce bâtiment -->
            <div class="capteurs-container">
                <h3><i class="fa-solid fa-microchip"></i> Sélection des capteurs <span id="capteurs-count-${i}" class="capteur-counter">0</span></h3>
                <p>Sélectionnez les capteurs pour ce bâtiment :</p>
                
                <!-- Section des capteurs commune à tous les types de bâtiments -->
                <div class="capteurs-section" id="capteurs-section-${i}">
                    <div class="capteurs-heading">
                        <i class="fa-solid ${typeInfo.icon}"></i>
                        <h3>Capteurs disponibles</h3>
                    </div>
                    
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" id="capteur_CapteurCO2LoRaWAN_${i}" 
                                   name="capteur_CapteurCO2LoRaWAN_${i}" value="1"
                                   onchange="updateCapteurCount(${i})">
                            <label for="capteur_CapteurCO2LoRaWAN_${i}">Capteur CO2 LoRaWAN</label>
                        </div>
                        <p class="capteur-description" id="desc-CapteurCO2LoRaWAN-${i}">
                            ${capteurDescriptions.CapteurCO2LoRaWAN[typeDefault]}
                        </p>
                        
                        <div class="checkbox-item">
                            <input type="checkbox" id="capteur_ControleurMultiEntrees_${i}" 
                                   name="capteur_ControleurMultiEntrees_${i}" value="1"
                                   onchange="updateCapteurCount(${i})">
                            <label for="capteur_ControleurMultiEntrees_${i}">Contrôleur Multi-Entrées</label>
                        </div>
                        <p class="capteur-description" id="desc-ControleurMultiEntrees-${i}">
                            ${capteurDescriptions.ControleurMultiEntrees[typeDefault]}
                        </p>
                        
                        <div class="checkbox-item">
                            <input type="checkbox" id="capteur_CapteurFlashO_${i}" 
                                   name="capteur_CapteurFlashO_${i}" value="1"
                                   onchange="updateCapteurCount(${i})">
                            <label for="capteur_CapteurFlashO_${i}">Capteur Flash'O</label>
                        </div>
                        <p class="capteur-description" id="desc-CapteurFlashO-${i}">
                            ${capteurDescriptions.CapteurFlashO[typeDefault]}
                        </p>
                    </div>
                </div>
                
                <!-- Résumé des capteurs sélectionnés -->
                <div id="capteur-summary-${i}" class="capteur-summary">
                    <h3><i class="fa-solid fa-list-check"></i> Capteurs sélectionnés</h3>
                    <ul id="capteurs-list-${i}">
                        <!-- Liste générée dynamiquement -->
                        <li class="empty-message">Aucun capteur sélectionné</li>
                    </ul>
                </div>
            </div>
        `;
        
        // Ajouter l'en-tête et le contenu au conteneur du bâtiment
        batimentContainer.appendChild(batimentHeader);
        batimentContainer.appendChild(batimentContent);
        
        // Ajouter ce bâtiment au conteneur principal
        container.appendChild(batimentContainer);
    }
}

// Fonction pour mettre à jour l'affichage du type de bâtiment
function updateBatimentType(index) {
    const selectElement = document.getElementById(`typeBatiment_${index}`);
    const selectedType = selectElement.value;
    const typeInfo = typeBatimentInfo[selectedType];
    
    // Mettre à jour l'icône et le titre dans l'en-tête
    const header = document.querySelector(`#batiment-${index} .batiment-header`);
    header.innerHTML = `
        <i class="fa-solid ${typeInfo.icon}"></i>
        <h3>Bâtiment ${index} - ${typeInfo.name}</h3>
        <div class="toggle-batiment" onclick="toggleBatiment(${index})">
            <i class="fa-solid fa-chevron-up" id="toggle-icon-${index}"></i>
        </div>
    `;
    
    // Mettre à jour les descriptions des capteurs pour ce type de bâtiment
    for (const capteurId in capteurDescriptions) {
        const descElement = document.getElementById(`desc-${capteurId}-${index}`);
        if (descElement) {
            descElement.textContent = capteurDescriptions[capteurId][selectedType];
        }
    }
    
    // Mettre à jour le compteur de capteurs (au cas où)
    updateCapteurCount(index);
}

// Fonction pour réduire/agrandir un bâtiment
function toggleBatiment(index) {
    const container = document.getElementById(`batiment-${index}`);
    const icon = document.getElementById(`toggle-icon-${index}`);
    
    if (container.classList.contains('collapsed')) {
        container.classList.remove('collapsed');
        icon.className = 'fa-solid fa-chevron-up';
    } else {
        container.classList.add('collapsed');
        icon.className = 'fa-solid fa-chevron-down';
    }
}

// Fonction pour mettre à jour le compteur de capteurs et le résumé
function updateCapteurCount(index) {
    const capteurs = [
        document.getElementById(`capteur_CapteurCO2LoRaWAN_${index}`),
        document.getElementById(`capteur_ControleurMultiEntrees_${index}`),
        document.getElementById(`capteur_CapteurFlashO_${index}`)
    ];
    
    // Compter les capteurs sélectionnés
    let count = 0;
    const selectedCapteurs = [];
    
    if (capteurs[0].checked) {
        count++;
        selectedCapteurs.push({
            id: 'CapteurCO2LoRaWAN',
            name: 'Capteur CO2 LoRaWAN',
            icon: 'fa-wind'
        });
    }
    
    if (capteurs[1].checked) {
        count++;
        selectedCapteurs.push({
            id: 'ControleurMultiEntrees',
            name: 'Contrôleur Multi-Entrées',
            icon: 'fa-door-open'
        });
    }
    
    if (capteurs[2].checked) {
        count++;
        selectedCapteurs.push({
            id: 'CapteurFlashO',
            name: 'Capteur Flash\'O',
            icon: 'fa-faucet'
        });
    }
    
    // Mettre à jour le compteur
    const countElement = document.getElementById(`capteurs-count-${index}`);
    countElement.textContent = count;
    
    // Mettre à jour le résumé des capteurs
    const summaryElement = document.getElementById(`capteur-summary-${index}`);
    const listElement = document.getElementById(`capteurs-list-${index}`);
    
    if (count > 0) {
        summaryElement.classList.add('visible');
        
        // Générer la liste des capteurs sélectionnés
        let listHTML = '';
        for (const capteur of selectedCapteurs) {
            listHTML += `
                <li>
                    <i class="fa-solid ${capteur.icon}"></i>
                    ${capteur.name}
                </li>
            `;
        }
        
        listElement.innerHTML = listHTML;
    } else {
        summaryElement.classList.remove('visible');
        listElement.innerHTML = '<li class="empty-message">Aucun capteur sélectionné</li>';
    }
}

// Fonction pour mettre à jour les informations de la ville
function updateInfos(villeNom) {
    // Vérifier si les informations de la ville existent dans notre tableau JavaScript
    if (typeof villesInfo !== 'undefined' && villesInfo[villeNom]) {
        document.getElementById('codePostal').value = villesInfo[villeNom].codePostal;
        
        // Mettre à jour l'adresse dans tous les champs d'adresse des bâtiments
        const nombreBatiments = document.getElementById('nombreBatiments').value;
        for (let i = 1; i <= nombreBatiments; i++) {
            const adresseField = document.getElementById(`adresse_${i}`);
            if (adresseField) {
                adresseField.value = villesInfo[villeNom].adresse;
            }
        }
    } else {
        // Réinitialiser le code postal si la ville n'est pas dans notre tableau
        document.getElementById('codePostal').value = '';
    }
}

// Initialiser le formulaire au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Si un nombre de bâtiments est déjà sélectionné, générer les formulaires
    const nombreBatiments = document.getElementById('nombreBatiments').value;
    if (nombreBatiments > 0) {
        genererFormulaires();
    }
    
    // Si une ville est déjà sélectionnée, mettre à jour les informations
    const villeSelect = document.getElementById('ville');
    if (villeSelect.value) {
        updateInfos(villeSelect.value);
    }
});
</script>

</body>
</html>
<?php
// Fermer la connexion à la base de données
$mysqli->close();

// Terminer la mise en mémoire tampon et envoyer le contenu
ob_end_flush();
?>