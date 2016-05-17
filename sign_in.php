<?php
/* CONNEXION BDD */
include ('connexion.php');
$bdd = connexionMySQL();
/* CONNEXION FAITE */

session_start();
if(!isset($_SESSION['connect']))
{
   $_SESSION['connect'] = 0; //Initialise la variable 'connect'.
}
?>
<head>
   <meta charset="utf-8" />
   <title>Connexion VegFrance</title>
</head>
<?php

if(isset($_POST) && !empty($_POST['pseudo']) && !empty($_POST['pass'])) {
   $pseudo = htmlspecialchars($_POST['pseudo']);
   $pass = md5($_POST['pass']);

   // On recupère le password de la table qui correspond au pseudo du visiteur
   $reponse = $bdd->query("SELECT password FROM users WHERE pseudo='".$pseudo."'");
   $donnees = $reponse->fetch();

   if($donnees['password'] != $pass) {
      echo '<p>Mauvais identifiant / password. Merci de recommencer</p>';
      include('sign_in.htm'); // On inclut le formulaire d'identification
      exit;
   }
   else {
      $_SESSION['connect'] = 1;
      $_SESSION['pseudo'] = $pseudo;

      // On va chercher le status correspondant au pseudo
      $reponse = $bdd->query("SELECT status FROM status WHERE id = (SELECT id_status FROM users WHERE pseudo='".$pseudo."')");
      $donnees = $reponse->fetch();
      $_SESSION['status'] = $donnees['status'];

      include('blank_page.htm');

      echo '<p style="text-align:center;margin-top:10%">Vous êtes bien logué(e)';

      $reponse = $bdd->query("SELECT last_visit FROM users WHERE pseudo='".$pseudo."'");
      $donnees = $reponse->fetch();

      echo ' | Dernière visite : ' . $donnees['last_visit'] . '</p>';
      header("refresh:3; url=import.php");
   }
}
else {
   echo '<p style="text-align:center">Vous avez oublié de remplir un champ.</p>';
   include('sign_in.htm'); // On inclut le formulaire d'identification
   exit;
}
?>
