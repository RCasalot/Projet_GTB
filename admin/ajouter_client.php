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

// Traitement du formulaire
if (isset($_POST["submit"])) {
    try {
        $nomVille = $_POST["nomVille"];
        $nomElu = $_POST["nomElu"];
        $fonctionElu = $_POST["fonctionElu"];
        $emailElu = $_POST["emailElu"];
        $telephoneElu = $_POST["telephoneElu"];
        $adresseVille = $_POST["adresseVille"];
        $codePostal = $_POST["codePostal"];
        $commentaire = $_POST["commentaire"];

        // Échapper les entrées utilisateur pour éviter les injections SQL
        $nomVille = $mysqli->real_escape_string($nomVille);
        $nomElu = $mysqli->real_escape_string($nomElu);
        $fonctionElu = $mysqli->real_escape_string($fonctionElu);
        $emailElu = $mysqli->real_escape_string($emailElu);
        $telephoneElu = $mysqli->real_escape_string($telephoneElu);
        $adresseVille = $mysqli->real_escape_string($adresseVille);
        $codePostal = $mysqli->real_escape_string($codePostal);
        $commentaire = $mysqli->real_escape_string($commentaire);

        // Insertion des données du client dans la base de données
        $query = "INSERT INTO clients (nom_ville, adresse_ville, code_postal, nom_elu, fonction_elu, email_elu, telephone_elu, commentaire) 
                VALUES ('$nomVille', '$adresseVille', '$codePostal', '$nomElu', '$fonctionElu', '$emailElu', '$telephoneElu', '$commentaire')";
        
        $res = $mysqli->query($query);

        if ($res) {
            // Stocker les informations de succès dans la session
            $_SESSION['creation_success'] = true;
            $_SESSION['nom_ville'] = $nomVille;
            
            // Vider le tampon de sortie avant la redirection
            ob_end_clean();
            
            // Redirection avec JavaScript comme solution de secours
            echo "<script>window.location.replace('confirmation_client.php');</script>";
            
            // Redirection PHP standard
            header("Location: confirmation_client.php");
            exit;
        } else {
            $error_message = "Erreur lors de l'ajout du client : " . $mysqli->error;
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
    <title>Ajout d'un nouveau client</title>
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
        .form-group input[type="email"],
        .form-group input[type="tel"],
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
        .form-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
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
        .error {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
<div class="header" style="background-color:#665264">
    <img src="../images/smica.png" width="500">
</div>

<div class="topnav">
    <a href="admin.php"><i class="fa fa-fw fa-home"></i> Accueil</a>
    <a href="liste_clients.php"><i class="fa-solid fa-list"></i> Liste des clients</a>
    <a href="https://www.carnus.fr" target="_blank"><i class="fa-solid fa-circle-info"></i> Info</a>
    <a href="../backend/logout.php" style="float:right"><i class="fa-solid fa-power-off"></i> Déconnexion</a>
    <a href style="float:right"><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($Nom); ?></a>
</div>

<div class="form-container">
    <?php if (!empty($error_message)): ?>
    <div class="error">
        <h3><?php echo $error_message; ?></h3>
    </div>
    <?php endif; ?>
    
    <h2><i class="fa-solid fa-city"></i> Ajouter un nouveau client</h2>
    
    <form method="POST" action="ajouter_client.php">
        <h3><i class="fa-solid fa-building-circle-check"></i> Informations de la ville</h3>
        
        <div class="form-group">
            <label for="nomVille">Nom de la ville :</label>
            <input type="text" id="nomVille" name="nomVille" required>
        </div>
        
        <div class="form-group">
            <label for="adresseVille">Adresse de la mairie :</label>
            <input type="text" id="adresseVille" name="adresseVille" required>
        </div>
        
        <div class="form-group">
            <label for="codePostal">Code postal :</label>
            <input type="text" id="codePostal" name="codePostal" required pattern="[0-9]{5}" title="Le code postal doit contenir 5 chiffres">
        </div>
        
        <div class="form-section">
            <h3><i class="fa-solid fa-user-tie"></i> Informations de l'élu</h3>
            
            <div class="form-group">
                <label for="nomElu">Nom et prénom de l'élu :</label>
                <input type="text" id="nomElu" name="nomElu" required>
            </div>
            
            <div class="form-group">
                <label for="fonctionElu">Fonction :</label>
                <select id="fonctionElu" name="fonctionElu" required>
                    <option value="">-- Sélectionnez une fonction --</option>
                    <option value="Maire">Maire</option>
                    <option value="Adjoint">Adjoint au maire</option>
                    <option value="Conseiller">Conseiller municipal</option>
                    <option value="Secrétaire">Secrétaire de mairie</option>
                    <option value="Autre">Autre fonction</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="emailElu">Email :</label>
                <input type="email" id="emailElu" name="emailElu" required>
            </div>
            
            <div class="form-group">
                <label for="telephoneElu">Téléphone :</label>
                <input type="tel" id="telephoneElu" name="telephoneElu" required pattern="[0-9]{10}" title="Le numéro de téléphone doit contenir 10 chiffres">
            </div>
        </div>
        
        <div class="form-section">
            <div class="form-group">
                <label for="commentaire">Commentaire :</label>
                <textarea id="commentaire" name="commentaire"></textarea>
            </div>
            
            <button type="submit" name="submit" class="btn-submit">
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer
            </button>
        </div>
    </form>
</div>

<script>
// Script pour ajouter des icônes au menu déroulant après chargement
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('fonctionElu');
    
    // Function pour afficher des icônes dans le texte affiché
    select.addEventListener('change', function() {
        const selectedOption = this.value;
        
        // Réinitialiser d'abord toutes les options
        for (let i = 0; i < this.options.length; i++) {
            this.options[i].innerHTML = this.options[i].innerHTML.replace(/<i class=".*?"><\/i> /, '');
        }
        
        // Ajouter l'icône à l'option sélectionnée
        if (selectedOption === 'Maire') {
            this.options[1].innerHTML = '<i class="fa-solid fa-crown"></i> Maire';
        } else if (selectedOption === 'Adjoint') {
            this.options[2].innerHTML = '<i class="fa-solid fa-user-shield"></i> Adjoint au maire';
        } else if (selectedOption === 'Conseiller') {
            this.options[3].innerHTML = '<i class="fa-solid fa-user-gear"></i> Conseiller municipal';
        } else if (selectedOption === 'Secrétaire') {
            this.options[4].innerHTML = '<i class="fa-solid fa-file-pen"></i> Secrétaire de mairie';
        } else if (selectedOption === 'Autre') {
            this.options[5].innerHTML = '<i class="fa-solid fa-user-plus"></i> Autre fonction';
        }
    });
});
</script>

</body>
</html>
<?php
// Vider et terminer la mise en mémoire tampon à la fin
ob_end_flush();
?>