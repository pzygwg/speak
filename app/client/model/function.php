<?php 

require 'config.php';
include 'config.php';

global $bdd;
global $utilisateur;

session_start();
setlocale(LC_TIME, 'fr_FR.UTF-8', 'French_France.1252');
$bdd = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$messages = [];

function loginUser($bdd, $email, $mdp) {
    try {
        $stmt = $bdd->prepare("SELECT * FROM Utilisateurs WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $utilisateur = $stmt->fetch();

        if ($utilisateur['status'] === 'bloquer') {
            header("Location: blocked.php");
            exit();
        }

        if ($utilisateur && $mdp === $utilisateur['mot_de_passe']) {
            session_start();
            $_SESSION['id_utilisateur'] = $utilisateur['id_utilisateur'];
            $_SESSION['nom'] = $utilisateur['nom'];
            $_SESSION['prenom'] = $utilisateur['prenom'];
            $_SESSION['email'] = $utilisateur['email'];
            $_SESSION['logged_in'] = false;
            $_SESSION['est_admin'] = $utilisateur['est_admin'];
            
            if($_SESSION['est_admin'] == 1){
                header("Location: main.php");
            } else {
                header("Location: main.php");
            }
            exit();
        } else {
            return "Adresse e-mail ou mot de passe incorrect.";
        }
    } catch (PDOException $e) {
        return "Erreur : " . $e->getMessage();
    }
}

function registerUser($bdd, $nom, $prenom, $adr_mail, $mdp, $datenaissance, $genre, $ville, $pays, $bio) {
    try {
        $stmt = $bdd->prepare("INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, date_de_naissance, genre, ville, pays, bio, photo_profil, est_admin, type_compte, date_inscription, status) VALUES (:nom, :prenom, :email, :mot_de_passe, :date_de_naissance, :genre, :ville, :pays, :bio, 'uploads/default.png', 0, 'Particulier', current_timestamp(), 'public')");
        $stmt->bindParam(":nom", $nom);
        $stmt->bindParam(":prenom", $prenom);
        $stmt->bindParam(":email", $adr_mail);
        $stmt->bindParam(":mot_de_passe", $mdp);
        $stmt->bindParam(":date_de_naissance", $datenaissance);
        $stmt->bindParam(":genre", $genre);
        $stmt->bindParam(":ville", $ville);
        $stmt->bindParam(":pays", $pays);
        $stmt->bindParam(":bio", $bio);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        return "Erreur lors de l'inscription : " . $e->getMessage();
    }
}

function estUtilisateurConnecte() {
    if(isset($_SESSION['utilisateur_connecte']) && $_SESSION['utilisateur_connecte'] === true) {
        return true;
    } else {
        return false;
    }
}

function verifierSession() {
    if (isset($_SESSION['id_utilisateur'])) {
        header("Location: main.php"); 
        exit();
    }
}

function verifierConnexion() {
    if (!isset($_SESSION['id_utilisateur'])) {
        header("Location: login.php");
        exit();
    }
}

function recupererInfosUtilisateurParId($bdd, $idUtilisateur) {
    try {
        $requete = $bdd->prepare("SELECT * FROM Utilisateurs WHERE id_utilisateur = :idUtilisateur");
        $requete->bindParam(':idUtilisateur', $idUtilisateur, PDO::PARAM_INT);
        $requete->execute();

        return $requete->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

function chargerInfosUtilisateur($bdd, $userId) {
    $utilisateur = getUserInfo($bdd, $userId);
    if ($utilisateur) {
        return [
            'nom' => $utilisateur['nom'],
            'prenom' => $utilisateur['prenom'],
            'pays' => $utilisateur['pays'],
            'ville' => $utilisateur['ville'],
            'datenaissance' => $utilisateur['date_de_naissance'],
            'genre' => $utilisateur['genre'],
            'bio' => $utilisateur['bio'],
            'est_admin' => $utilisateur['est_admin'],
            'photo_profil' => $utilisateur['photo_profil'],
            'publications' => getAllUserPublications($bdd, $userId),
            'totalPosts' => countUserPosts($bdd, $userId),
            'suivi' => countUserFollowing($bdd, $userId),
            'follower' => countUserFollowers($bdd, $userId),
            'nonFriends' => getNonFriends($bdd, $userId), 
            'enregistrer' => getPublicationsEnregistrees($bdd, $userId) 
        ];
    } else {
        return false;
    }
}
function traitementSoumissionFormulaireSignUp($bdd) {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $adr_mail = $_POST['email'];
        $mdp = $_POST['mot_de_passe'];
        $genre = $_POST['genre'];
        $ville = $_POST['ville'];
        $pays = $_POST['pays'];
        $datenaissance =$_POST['date'];
        $bio = $_POST['bio'];
        $type_compte = $_POST['type_compte'];
        $est_admin = $_POST['est_admin'];
        $photo_profil = $_FILES['photo_profil']['name']; 

        if (registerUser($bdd, $nom, $prenom, $adr_mail, $mdp, $datenaissance, $genre, $ville, $pays, $bio, $photo_profil, $est_admin, $type_compte)) {
            header("Location: login.php");
            $messageErreur = "Compte crée avec succès";
            exit();
        } else {
            $erreur = true;
            $messageErreur = "Erreur lors de la création du compte.";
        }
    }
}
function traiterSoumissionFormulaire($bdd) {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $mdp = isset($_POST['mot_de_passe']) ? $_POST['mot_de_passe'] : '';

        if (!empty($email) && !empty($mdp)) {
            return loginUser($bdd, $email, $mdp);
        } else {
            return "Veuillez remplir tous les champs.";
        }
    }
    return null;
}

function deconnecter() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: login.php");
    exit();
}
