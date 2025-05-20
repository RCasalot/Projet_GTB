<?php
// Démarrer la session
session_start();

// Connexion à la base de données
$mysqli = new mysqli("localhost", "root", "", "mairie");

// Vérification de la connexion
if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

// Vérifie que l'utilisateur est connecté et qu'il a le statut "admin"
if (!isset($_SESSION["Statut"]) || $_SESSION["Statut"] !== "Responsable") {
    // Redirige vers la page d'erreur d'accès refusé
    header("Location: acces_refuse.php");
    exit;
}

// Récupérer le nom de l'utilisateur depuis la session
$Nom = isset($_SESSION["Nom"]) ? $_SESSION["Nom"] : 'Invité';
$Statut = isset($_SESSION["Statut"]) ? $_SESSION["Statut"] : '';

// Fonction pour récupérer les données
function getDonnees($mysqli, $dateDebut = null, $dateFin = null, $type = null) {
    // Construction de la requête
    $query = "SELECT ID, ";
    
    // Sélection des colonnes en fonction du type
    if ($type == 'temperature') {
        $query .= "Temp as valeur";
    } elseif ($type == 'consigne_temp') {
        $query .= "ConsT as valeur";
    } elseif ($type == 'luminosite') {
        $query .= "Luminosité as valeur";
    } elseif ($type == 'consigne_lum') {
        $query .= "ConsL as valeur";
    } elseif ($type == 'eau') {
        $query .= "Eau as valeur";
    } elseif ($type == 'electricite') {
        $query .= "Electricité as valeur";
    } elseif ($type == 'gaz') {
        $query .= "Gaz as valeur";
    } else {
        // Par défaut, sélectionner toutes les colonnes numériques importantes
        $query .= "Temp, ConsT, Luminosité, ConsL, Eau, Electricité, Gaz";
    }
    
    $query .= ", Date FROM variables WHERE 1=1";
    
    // Ajout des filtres de date
    if ($dateDebut) {
        $query .= " AND Date >= '$dateDebut'";
    }
    
    if ($dateFin) {
        $query .= " AND Date <= '$dateFin'";
    }
    
    $query .= " ORDER BY Date ASC";
    
    // Exécution de la requête
    $result = $mysqli->query($query);
    
    // Préparation des données
    $donnees = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $donnees[] = $row;
        }
    }
    
    return $donnees;
}

// Traitement de l'API
if (isset($_GET['api']) && $_GET['api'] == 'donnees') {
    header('Content-Type: application/json');
    
    $dateDebut = isset($_GET['debut']) ? $_GET['debut'] : null;
    $dateFin = isset($_GET['fin']) ? $_GET['fin'] : null;
    $type = isset($_GET['type']) ? $_GET['type'] : null;
    
    $donnees = getDonnees($mysqli, $dateDebut, $dateFin, $type);
    
    echo json_encode($donnees);
    exit();
}

// Vérifier s'il y a des données dans la table variables
$checkData = "SELECT COUNT(*) as count FROM variables";
$result = $mysqli->query($checkData);
$row = $result->fetch_assoc();

// Si la table est vide, générer des données de test
if ($row['count'] == 0) {
    // Générer des données pour les 30 derniers jours
    for ($i = 30; $i >= 0; $i--) {
        $date = date('Y-m-d H:i:s', strtotime("-$i days"));
        
        // Valeurs avec tendances et variations aléatoires
        $temp = 20 + sin($i/3) + (rand(-10, 10) / 10);
        $consT = 21 + (rand(-5, 5) / 10);
        $luminosite = 70 + cos($i/2) * 20 + rand(-5, 5);
        $consL = 75 + (rand(-10, 10) / 10);
        $mouvement = rand(0, 1);
        $eau = 50 + $i * 0.5 + sin($i/5) * 10 + rand(-3, 3);
        $electricite = 120 + $i * 0.7 + cos($i/4) * 15 + rand(-5, 5);
        $gaz = 80 + $i * 0.3 + sin($i/6) * 8 + rand(-4, 4);
        $air = ['Bonne', 'Moyenne', 'Mauvaise'][rand(0, 2)];
        
        $insertQuery = "INSERT INTO variables (Temp, ConsT, Luminosité, ConsL, Mouvement, Eau, Electricité, Gaz, Air, Date) 
                       VALUES ($temp, $consT, $luminosite, $consL, $mouvement, $eau, $electricite, $gaz, '$air', '$date')";
        $mysqli->query($insertQuery);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Visualisation des Données - Mairie</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../backend/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.0.1/dist/chartjs-adapter-moment.min.js"></script>
    <style>
        .chart-container {
            width: 90%;
            margin: 15px auto;
            height: 400px;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 15px;
        }
        .chart-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 25px auto;
            padding: 20px;
            width: 85%;
        }
        .chart-title {
            color: #665264;
            text-align: center;
            margin-bottom: 15px;
            font-size: 1.2rem;
            font-weight: bold;
            border-bottom: 2px solid #eaeaea;
            padding-bottom: 10px;
        }
        .controls {
            width: 90%;
            margin: 15px auto;
            padding: 15px;
            background-color: #f2f2f2;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .controls label, .controls select, .controls button {
            margin: 5px;
            padding: 5px;
        }
        .controls button {
            background-color: #665264;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .controls button:hover {
            background-color: #533f52;
        }
        .dashboard {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin: 20px auto;
            width: 90%;
        }
        .dashboard-card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin: 10px;
            padding: 15px;
            width: 250px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .dashboard-card h3 {
            margin-top: 0;
            color: #665264;
        }
        .dashboard-card .value {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        /* Nouvelles classes pour les onglets */
        .tab-container {
            width: 85%;
            margin: 20px auto;
        }
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
            padding: 12px 20px;
            transition: 0.3s;
            font-size: 16px;
            position: relative;
            z-index: 1;
        }
        .tab button:hover {
            background-color: #ddd;
        }
        .tab button.active {
            background-color: #665264;
            color: white;
            border-bottom: none;
        }
        .tabcontent {
            display: none;
            border-radius: 0 0 8px 8px;
            animation: fadeEffect 0.5s;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-top: none;
            padding: 20px 0;
        }
        @keyframes fadeEffect {
            from {opacity: 0;}
            to {opacity: 1;}
        }
        
        /* Fix couleur texte */
        body {
            color: #333;
        }
        h1 {
            color: #665264;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .modify-btn {
            background-color: #665264;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            margin-top: 10px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .modify-btn:hover {
            background-color: #533f52;
        }

        /* Style pour la fenêtre modale */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 300px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .close-modal {
            float: right;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .modal-content h3 {
            color: #665264;
            margin-top: 0;
        }

        .modal-content input {
            width: 90%;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .modal-content button {
            background-color: #665264;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header" style="background-color:#665264">
        <a href="index2.php" target="_blank"><img src="../images/smica.png" width="500"></a>
    </div>

    <div class="topnav">
        <a href="responsable.php"><i class="fa fa-fw fa-home"></i> Accueil</a>
        <a href="https://www.carnus.fr" target="_blank"><i class="fa-solid fa-circle-info"></i> Info</a>
        <a href="../backend/logout.php" style="float:right"><i class="fa-solid fa-power-off"></i> Déconnexion</a>
        <a style="float:right"><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($Nom); ?></a>
        <?php if ($Statut === "Administrateur"): ?>
        <a href="ajout.php" style="float:right"><i class="fa-solid fa-user-plus"></i> Ajout</a>
        <?php endif; ?>
    </div>

    <h1 style="text-align: center; margin-top: 20px;">Tableau de bord - Visualisation des Données</h1>

    <!-- Cartes du tableau de bord pour les dernières valeurs -->
    <div class="dashboard">
        <?php
        // Récupérer les dernières valeurs
        $latestQuery = "SELECT * FROM variables ORDER BY Date DESC LIMIT 1";
        $latestResult = $mysqli->query($latestQuery);
        if ($latestResult && $row = $latestResult->fetch_assoc()) {
            echo '<div class="dashboard-card">';
            echo '<h3>Température</h3>';
            echo '<div class="value">' . number_format($row['Temp'], 1) . ' °C</div>';
            echo '<div>Consigne: ' . number_format($row['ConsT'], 1) . ' °C</div>';
            echo '<button class="modify-btn" data-type="temperature" data-value="' . $row['ConsT'] . '"><i class="fa-solid fa-pen-to-square"></i> Modifier</button>';
            echo '</div>';

            echo '<div class="dashboard-card">';
            echo '<h3>Luminosité</h3>';
            echo '<div class="value">' . number_format($row['Luminosité'], 1) . ' %</div>';
            echo '<div>Consigne: ' . number_format($row['ConsL'], 1) . ' %</div>';
            echo '<button class="modify-btn" data-type="luminosite" data-value="' . $row['ConsL'] . '"><i class="fa-solid fa-pen-to-square"></i> Modifier</button>';
            echo '</div>';

            echo '<div class="dashboard-card">';
            echo '<h3>Consommation Eau</h3>';
            echo '<div class="value">' . number_format($row['Eau'], 1) . ' m³</div>';
            echo '</div>';

            echo '<div class="dashboard-card">';
            echo '<h3>Consommation Électricité</h3>';
            echo '<div class="value">' . number_format($row['Electricité'], 1) . ' kWh</div>';
            echo '</div>';

            echo '<div class="dashboard-card">';
            echo '<h3>Consommation Gaz</h3>';
            echo '<div class="value">' . number_format($row['Gaz'], 1) . ' m³</div>';
            echo '</div>';

            echo '<div class="dashboard-card">';
            echo '<h3>Qualité de l\'air</h3>';
            echo '<div class="value">' . htmlspecialchars($row['Air']) . '</div>';
            echo '</div>';

            
        }
        ?>
    </div>

    <!-- Graphiques dans des cadres séparés -->
    <div class="tab-container">
        <div class="tab">
            <button class="tablinks active" onclick="openTab(event, 'temperature')">Température</button>
            <button class="tablinks" onclick="openTab(event, 'luminosite')">Luminosité</button>
            <button class="tablinks" onclick="openTab(event, 'consommation')">Consommation</button>
        </div>
    </div>

    <!-- Contenu des onglets -->
    <div id="temperature" class="tabcontent" style="display: block;">
        <div class="chart-card">
            <div class="chart-title">Évolution de la Température</div>
            <div class="controls">
                <label for="periode-temp">Période:</label>
                <select id="periode-temp">
                    <option value="1">Dernières 24h</option>
                    <option value="7">7 derniers jours</option>
                    <option value="14">14 derniers jours</option>
                    <option value="30" selected>30 derniers jours</option>
                </select>
                
                <button id="actualiser-temp"><i class="fa-solid fa-arrows-rotate"></i> Actualiser</button>
            </div>
            
            <div class="chart-container">
                <canvas id="graphique-temp"></canvas>
            </div>
        </div>
    </div>

    <div id="luminosite" class="tabcontent">
        <div class="chart-card">
            <div class="chart-title">Évolution de la Luminosité</div>
            <div class="controls">
                <label for="periode-lum">Période:</label>
                <select id="periode-lum">
                    <option value="1">Dernières 24h</option>
                    <option value="7">7 derniers jours</option>
                    <option value="14">14 derniers jours</option>
                    <option value="30" selected>30 derniers jours</option>
                </select>
                
                <button id="actualiser-lum"><i class="fa-solid fa-arrows-rotate"></i> Actualiser</button>
            </div>
            
            <div class="chart-container">
                <canvas id="graphique-lum"></canvas>
            </div>
        </div>
    </div>

    <div id="consommation" class="tabcontent">
        <div class="chart-card">
            <div class="chart-title">Évolution de la Consommation</div>
            <div class="controls">
                <label for="type-conso">Type:</label>
                <select id="type-conso">
                    <option value="eau" selected>Eau</option>
                    <option value="electricite">Électricité</option>
                    <option value="gaz">Gaz</option>
                </select>
                
                <label for="periode-conso">Période:</label>
                <select id="periode-conso">
                    <option value="7">7 derniers jours</option>
                    <option value="14">14 derniers jours</option>
                    <option value="30" selected>30 derniers jours</option>
                </select>
                
                <button id="actualiser-conso"><i class="fa-solid fa-arrows-rotate"></i> Actualiser</button>
            </div>
            
            <div class="chart-container">
                <canvas id="graphique-conso"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Fonction pour ouvrir un onglet
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
        
        // Graphiques
        let chartTemp, chartLum, chartConso;
        
        // Fonction pour charger les données de température
        async function chargerDonneesTemp() {
            const periodeJours = parseInt(document.getElementById('periode-temp').value);
            
            const dateFin = new Date();
            const dateDebut = new Date();
            dateDebut.setDate(dateFin.getDate() - periodeJours);
            
            // Construire l'URL de l'API
            let url = '?api=donnees';
            url += `&debut=${dateDebut.toISOString().split('T')[0]}&fin=${dateFin.toISOString().split('T')[0]}`;
            
            try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const donnees = await response.json();
            console.log("Données reçues:", donnees); // Debug
        
            // Mettre à jour le graphique
            mettreAJourGraphiqueTemp(donnees);
        } catch (error) {
            console.error('Erreur lors du chargement des données:', error);
            alert('Erreur lors du chargement des données: ' + error.message);
        }
}
        
        // Fonction pour mettre à jour le graphique de température
        function mettreAJourGraphiqueTemp(donnees) {
            if (!donnees || donnees.length === 0) {
                console.error('Aucune donnée disponible pour le graphique.');
                return;
            }

            const ctx = document.getElementById('graphique-temp').getContext('2d');
            
            // Préparer les données
            const labels = donnees.map(item => new Date(item.Date));
            const dataTemp = donnees.map(item => item.Temp);
            const dataConsTemp = donnees.map(item => item.ConsT);
            
            // Détruire le graphique existant s'il existe
            if (chartTemp) {
                chartTemp.destroy();
            }
            
            // Créer un nouveau graphique
            chartTemp = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Température',
                            data: dataTemp,
                            borderColor: 'rgb(255, 99, 132)',
                            backgroundColor: 'rgba(255, 99, 132, 0.1)',
                            tension: 0.2,
                            fill: true
                        },
                        {
                            label: 'Consigne',
                            data: dataConsTemp,
                            borderColor: 'rgb(54, 162, 235)',
                            borderDash: [5, 5],
                            tension: 0.2,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: 'day',
                                displayFormats: {
                                    day: 'dd/MM'
                                }
                            },
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Température (°C)'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: false, // Titre déjà géré par HTML
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                title: function(context) {
                                    const date = new Date(context[0].parsed.x);
                                    return date.toLocaleDateString('fr-FR');
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Fonction pour charger les données de luminosité
        async function chargerDonneesLum() {
            const periodeJours = parseInt(document.getElementById('periode-lum').value);
            
            const dateFin = new Date();
            const dateDebut = new Date();
            dateDebut.setDate(dateFin.getDate() - periodeJours);
            
            // Construire l'URL de l'API
            let url = '?api=donnees';
            url += `&debut=${dateDebut.toISOString().split('T')[0]}&fin=${dateFin.toISOString().split('T')[0]}`;
            
            try {
                const response = await fetch(url);
                const donnees = await response.json();
                
                // Mettre à jour le graphique
                mettreAJourGraphiqueLum(donnees);
            } catch (error) {
                console.error('Erreur lors du chargement des données:', error);
            }
        }
        
        // Fonction pour mettre à jour le graphique de luminosité
        function mettreAJourGraphiqueLum(donnees) {
            const ctx = document.getElementById('graphique-lum').getContext('2d');
            
            // Préparer les données
            const labels = donnees.map(item => new Date(item.Date));
            const dataLum = donnees.map(item => item.Luminosité);
            const dataConsLum = donnees.map(item => item.ConsL);
            
            // Détruire le graphique existant s'il existe
            if (chartLum) {
                chartLum.destroy();
            }
            
            // Créer un nouveau graphique
            chartLum = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Luminosité',
                            data: dataLum,
                            borderColor: 'rgb(255, 159, 64)',
                            backgroundColor: 'rgba(255, 159, 64, 0.1)',
                            tension: 0.2,
                            fill: true
                        },
                        {
                            label: 'Consigne',
                            data: dataConsLum,
                            borderColor: 'rgb(153, 102, 255)',
                            borderDash: [5, 5],
                            tension: 0.2,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: 'day',
                                displayFormats: {
                                    day: 'dd/MM'
                                }
                            },
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Luminosité (%)'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: false, // Titre déjà géré par HTML
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                title: function(context) {
                                    const date = new Date(context[0].parsed.x);
                                    return date.toLocaleDateString('fr-FR');
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Fonction pour charger les données de consommation
        async function chargerDonneesConso() {
            const typeConso = document.getElementById('type-conso').value;
            const periodeJours = parseInt(document.getElementById('periode-conso').value);
            
            const dateFin = new Date();
            const dateDebut = new Date();
            dateDebut.setDate(dateFin.getDate() - periodeJours);
            
            // Construire l'URL de l'API
            let url = '?api=donnees';
            url += `&debut=${dateDebut.toISOString().split('T')[0]}&fin=${dateFin.toISOString().split('T')[0]}`;
            
            try {
                const response = await fetch(url);
                const donnees = await response.json();
                
                // Mettre à jour le graphique
                mettreAJourGraphiqueConso(donnees, typeConso);
            } catch (error) {
                console.error('Erreur lors du chargement des données:', error);
            }
        }
        
        // Fonction pour mettre à jour le graphique de consommation
        function mettreAJourGraphiqueConso(donnees, type) {
            const ctx = document.getElementById('graphique-conso').getContext('2d');
            
            // Préparer les données
            const labels = donnees.map(item => new Date(item.Date));
            let data, title, yLabel;
            
            if (type === 'eau') {
                data = donnees.map(item => item.Eau);
                title = 'Consommation d\'eau';
                yLabel = 'Consommation (m³)';
                color = 'rgb(75, 192, 192)';
            } else if (type === 'electricite') {
                data = donnees.map(item => item.Electricité);
                title = 'Consommation d\'électricité';
                yLabel = 'Consommation (kWh)';
                color = 'rgb(255, 205, 86)';
            } else if (type === 'gaz') {
                data = donnees.map(item => item.Gaz);
                title = 'Consommation de gaz';
                yLabel = 'Consommation (m³)';
                color = 'rgb(54, 162, 235)';
            }
            
            // Mettre à jour le titre dans le HTML
            document.querySelector('#consommation .chart-title').innerText = title;
            
            // Détruire le graphique existant s'il existe
            if (chartConso) {
                chartConso.destroy();
            }
            
            // Créer un nouveau graphique
            chartConso = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: title,
                            data: data,
                            backgroundColor: color,
                            borderColor: color,
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: 'day',
                                displayFormats: {
                                    day: 'dd/MM'
                                }
                            },
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: yLabel
                            },
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        title: {
                            display: false, // Titre déjà géré par HTML
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                title: function(context) {
                                    const date = new Date(context[0].parsed.x);
                                    return date.toLocaleDateString('fr-FR');
                                }
                            }
                        }
                    }
                }
            });
            // Gestion de la fenêtre modale pour les consignes
const modal = document.getElementById('consigneModal');
const modalTitle = document.getElementById('modal-title');
const consigneType = document.getElementById('consigne-type');
const consigneValue = document.getElementById('consigne-value');
const closeBtn = document.querySelector('.close-modal');
const modifyBtns = document.querySelectorAll('.modify-btn');

// Ouvrir la fenêtre modale lors du clic sur un bouton modifier
modifyBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        const type = this.getAttribute('data-type');
        const value = this.getAttribute('data-value');
        
        if (type === 'temperature') {
            modalTitle.textContent = 'Modifier la consigne de température';
        } else if (type === 'luminosite') {
            modalTitle.textContent = 'Modifier la consigne de luminosité';
        }
        
        consigneType.value = type;
        consigneValue.value = value;
        modal.style.display = 'block';
    });
});

// Fermer la fenêtre modale
closeBtn.addEventListener('click', function() {
    modal.style.display = 'none';
});

// Fermer la fenêtre modale en cliquant en dehors
window.addEventListener('click', function(event) {
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});

// Soumettre le formulaire
document.getElementById('consigne-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const type = consigneType.value;
    const value = consigneValue.value;
    
    try {
        const response = await fetch('update_consigne.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `type=${type}&value=${value}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Consigne mise à jour avec succès!');
            location.reload(); // Recharger la page pour afficher les nouvelles valeurs
        } else {
            alert('Erreur: ' + result.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Une erreur est survenue lors de la mise à jour de la consigne.');
    }
    
    modal.style.display = 'none';
});
        }
        
        // Initialiser l'application
        document.addEventListener('DOMContentLoaded', () => {
            // Charger les données initiales
            chargerDonneesTemp();
            chargerDonneesLum();
            chargerDonneesConso();
            
            // Configurer les événements
            document.getElementById('actualiser-temp').addEventListener('click', chargerDonneesConso);
            document.getElementById('periode-temp').addEventListener('change', chargerDonneesConso);
            
            document.getElementById('actualiser-lum').addEventListener('click', chargerDonneesLum);
            document.getElementById('periode-lum').addEventListener('change', chargerDonneesLum);
            
            document.getElementById('actualiser-conso').addEventListener('click', chargerDonneesConso);
            document.getElementById('periode-conso').addEventListener('change', chargerDonneesConso);
            document.getElementById('type-conso').addEventListener('change', chargerDonneesConso);
        });
    </script>    
    <!-- Fenêtre modale pour la modification des consignes -->
<div id="consigneModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h3 id="modal-title">Modifier consigne</h3>
        <form id="consigne-form">
            <input type="hidden" id="consigne-type" name="type">
            <label for="consigne-value">Nouvelle valeur:</label>
            <input type="number" id="consigne-value" name="value" step="0.1" required>
            <button type="submit">Enregistrer</button>
        </form>
    </div>
</div>
</body>
</html>