<?php
/* CONNEXION BDD */
include ('connexion.php');
$bdd = connexionMySQL();
/* CONNEXION FAITE */

session_start();
$_SESSION['connect'] = 0; //Initialise la variable 'connect'.

if(isset($_POST) && !empty($_POST['pseudo']) && !empty($_POST['pass'])) {
   $pseudo = $_POST['pseudo'];
   $pass = md5($_POST['pass']);

   // On recupère le password de la table qui correspond au pseudo du visiteur
   $reponse = $bdd->query("SELECT password FROM users WHERE pseudo='".$pseudo."'");
   $donnees = $reponse->fetch();

   if($donnees['password'] != $pass) {
      echo '<p>Mauvais identifiant / password. Merci de recommencer</p>';
      include('login.htm'); // On inclut le formulaire d'identification
      exit;
   }
   else {
      $_SESSION['connect'] = 1;
      $_SESSION['pseudo'] = $pseudo;

      echo '<h3>Vous êtes bien logué(e)';

      $reponse = $bdd->query("SELECT last_visit FROM users WHERE pseudo='".$pseudo."'");
      $donnees = $reponse->fetch();

      echo ' | Dernière visite : ' . $donnees['last_visit'] . '</h3>';
      header("refresh:1; url=import.php");
   }
}
else {
   echo '<p>Vous avez oublié de remplir un champ.</p>';
   include('login.htm'); // On inclut le formulaire d'identification
   exit;
}
?>
