<?php 

require '../model/config.php';
include '../model/config.php';

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


