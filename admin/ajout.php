<?php
// Démarrer la session
session_start();

// Connexion à la base de données
$mysqli = new mysqli("localhost", "root", "", "mairie");

// Vérification de la connexion
if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

// Traitement du formulaire
if (isset($_POST["submit"])) {
    $Nom = $_POST["Nom"];
    $Mdp = $_POST["Mdp"];
    $Statut = $_POST["Statut"];
    $Code = $_POST["Code"];

    // Hashage du mot de passe
    $Mdp = hash('sha256', $Mdp);

    // Insertion des données dans la base de données
    $query = "INSERT INTO connexions (Nom, Mdp, Statut, Code) VALUES ('$Nom', '$Mdp', '$Statut', '$Code')";
    $res = $mysqli->query($query);

    if ($res) {
        echo "<div class='succes'>
        <h3>L'utilisateur a été créé avec succès.</h3>
        </div>";
    } else {
        echo "<div class='error'>
        <h3>Erreur : " . $mysqli->error . "</h3>
        </div>";
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
    <title>Ajout d'un nouvel utilisateur</title>
    <link rel="stylesheet" href="../backend/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
<div class="header" style="background-color:#665264">
    <a href="index2.php" target="_blank"><img src="../images/smica.png" width="500"></a>
</div>

<div class="topnav">
    <a class="active" href="admin.php"><i class="fa fa-fw fa-home"></i> Accueil</a>
    <a href="https://www.carnus.fr" target="_blank"><i class="fa-solid fa-circle-info"></i> Info</a>
    <a href="../backend/logout.php" style="float:right"><i class="fa-solid fa-power-off"></i> Déconnexion</a>
    <a style="float:right"><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($Nom); ?></a>
    <a class="active" href="ajout.php" style="float:right"><i class="fa-solid fa-user-plus"></i> Ajout</a>
</div>
<h1 style="color:white;" align="center">Création d'un utilisateur</h1>
<form action="" method="post">
    <input type="text" class="box-input" name="Nom" placeholder="Nom d'utilisateur" required />
    <input type="password" class="box-input" name="Mdp" placeholder="Mot de passe" required />
    <?php
			$Code = rand(100000, 999999);
			?>
			<input type="text" class="box-input" name="Code" value="<?php echo $Code; ?>" readonly />
    <select name="Statut" id="Statut" required>
        <option value="">-- Choisir un statut --</option>
        <option value="Administrateur">Administrateur</option>
        <option value="Responsable">Responsable</option>
        <option value="Utilisateur">Utilisateur</option>
    </select><br>
    <input type="submit" name="submit" value="Créer l'utilisateur">
</form>
</body>
</html>

