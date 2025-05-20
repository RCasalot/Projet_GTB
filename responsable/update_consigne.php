<?php
// Démarrer la session
session_start();

// Vérification de l'authentification
if (!isset($_SESSION["Nom"]) || !isset($_SESSION["Statut"])) {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

// Connexion à la base de données
$mysqli = new mysqli("172.40.20.145", "root", "CIEL12000", "GTB");

// Vérification de la connexion
if ($mysqli->connect_error) {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']);
    exit();
}

// Vérifier si les données POST sont reçues
if (isset($_POST['type']) && isset($_POST['value'])) {
    $type = $_POST['type'];
    $value = floatval($_POST['value']);
    
    // Vérifier la validité des valeurs
    if ($type === 'temperature') {
        if ($value < 15 || $value > 30) {
            header("Content-Type: application/json");
            echo json_encode(['success' => false, 'message' => 'La consigne de température doit être entre 15°C et 30°C']);
            exit();
        }
        
        // Mettre à jour la consigne de température (dernière valeur)
        $query = "UPDATE variables SET ConsT = ? ORDER BY Date DESC LIMIT 1";
    } elseif ($type === 'luminosite') {
        if ($value < 0 || $value > 100) {
            header("Content-Type: application/json");
            echo json_encode(['success' => false, 'message' => 'La consigne de luminosité doit être entre 0% et 100%']);
            exit();
        }
        
        // Mettre à jour la consigne de luminosité (dernière valeur)
        $query = "UPDATE variables SET ConsL = ? ORDER BY Date DESC LIMIT 1";
    } else {
        header("Content-Type: application/json");
        echo json_encode(['success' => false, 'message' => 'Type de consigne non valide']);
        exit();
    }
    
    // Préparer et exécuter la requête
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("d", $value);
    
    if ($stmt->execute()) {
        header("Content-Type: application/json");
        echo json_encode(['success' => true]);
    } else {
        header("Content-Type: application/json");
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour: ' . $mysqli->error]);
    }
    
    $stmt->close();
} else {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
}

$mysqli->close();
?>