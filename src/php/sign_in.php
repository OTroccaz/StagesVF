<?php
/* CONNEXION BDD */
include ('../../config/connection.php');
$bdd = connexionMySQL();
/* CONNEXION FAITE */

session_start();
if(!isset($_SESSION['pseudo']))
{
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
      $pseudo = htmlspecialchars(htmlspecialchars($_POST['pseudo']));
      $pass = sha1($_POST['pass']);

      // On recupère le password de la table qui correspond au pseudo du visiteur
      $reponse = $bdd->query("SELECT password FROM users WHERE pseudo='".$pseudo."'");
      $donnees = $reponse->fetch();

      if($donnees['password'] != $pass) {
         echo '<p style="text-align:center;color:red">Mauvais identifiant / password. Merci de recommencer</p>';
         include('../html/sign_in.htm'); // On inclut le formulaire d'identification
         exit;
      }
      else {
         $_SESSION['connect'] = 1;
         $_SESSION['pseudo'] = $pseudo;

         // On va chercher le status correspondant au pseudo
         $reponse = $bdd->query("SELECT status FROM status WHERE id = (SELECT id_status FROM users WHERE pseudo='".$pseudo."')");
         $donnees = $reponse->fetch();
         $_SESSION['status'] = $donnees['status'];

         include('../html/blank_page.htm');

         echo '<p style="text-align:center;margin-top:10%">Vous êtes bien logué(e)';

         $reponse = $bdd->query("SELECT last_visit FROM users WHERE pseudo='".$pseudo."'");
         $donnees = $reponse->fetch();

         echo ' | Dernière visite : ' . $donnees['last_visit'] . '</p>';
         header("refresh:3; url=../../index.php");
      }
   }
   else {
      include('../html/sign_in.htm'); // On inclut le formulaire d'identification
      exit;
   }
} else {
   include('../html/blank_page.htm'); //une fois connecté, si on retourne sur la page de connexion alors on ne revoit pas le formulaire de login
   ?>
   <head>
      <meta charset="utf-8" />
      <title>Connexion VegFrance</title>
   </head>
   <?php
   echo '<label style="display:block;text-align:center;margin-top:10%"> Vous êtes déjà connecté en tant que ' . $_SESSION['pseudo'] . ', si vous souhaitez vous déconnecter, cliquez <a href="disconnect.php">ici</a>.';
}
?>
