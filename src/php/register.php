<?php

/* CONNEXION BDD */
include ('../../config/connection.php');
$bdd = connexionPgSQL();
/* CONNEXION FAITE */
session_start();

if (empty($_POST['pseudo'])) // Si on la variable est vide, on peut considérer qu'on est sur la page de formulaire
{
   include('../html/register.htm');
} // Fin de la partie formulaire
else // On est dans le cas traitement
{
   $pseudo_erreur1 = NULL;
   $pseudo_erreur2 = NULL;
   $mdp_erreur = NULL;
   $email_erreur1 = NULL;
   $email_erreur2 = NULL;

   // On récupère les variables
   $i = 0;
   $temps = date("Y-m-d");
   $firstname = htmlspecialchars($_POST['firstname']);
   $lastname = htmlspecialchars($_POST['lastname']);
   $pseudo = htmlspecialchars($_POST['pseudo']);
   $email = htmlspecialchars($_POST['email']);
   $domain = htmlspecialchars($_POST['domain']);
   $pass = sha1($_POST['password']);
   $confirm = sha1($_POST['confirm']);

   // Vérification du pseudo
   $requete=$bdd->prepare('SELECT COUNT(*) AS nb_users FROM users WHERE pseudo =:pseudo');
   $requete->bindValue(':pseudo',$pseudo, PDO::PARAM_STR);
   $requete->execute();
   $pseudo_free=($requete->fetchColumn()==0)?1:0;
   $requete->CloseCursor();
   if(!$pseudo_free)
   {
      $pseudo_erreur1 = "Votre pseudo est déjà utilisé par un membre.";
      $i++;
   }

   if (strlen($pseudo) < 3 || strlen($pseudo) > 15)
   {
      $pseudo_erreur2 = "Votre pseudo est soit trop grand, soit trop petit.";
      $i++;
   }

   // Vérification du mdp
   if ($pass != $confirm || empty($confirm) || empty($pass))
   {
      $mdp_erreur = "Votre mot de passe et votre confirmation diffèrent, ou sont vides.";
      $i++;
   }

   // Vérification de l'adresse email

   // Il faut que l'adresse email n'ait jamais été utilisée
   $requete=$bdd->prepare('SELECT COUNT(*) AS nb_users FROM users WHERE email =:email');
   $requete->bindValue(':email',$email, PDO::PARAM_STR);
   $requete->execute();
   $mail_free=($requete->fetchColumn()==0)?1:0;
   $requete->CloseCursor();

   if(!$mail_free)
   {
      $email_erreur1 = "Votre adresse email est déjà utilisée par un membre.";
      $i++;
   }
   // On vérifie la forme maintenant
   if (!preg_match("#^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]{2,}\.[a-z]{2,4}$#", $email) || empty($email))
   {
      $email_erreur2 = "Votre adresse E-Mail n'a pas un format valide.";
      $i++;
   }

   if ($i==0)
   {
      include('../html/blank_page.htm');
      ?>
      <head>
         <title>Inscription VegFrance</title>
      </head>
      <div  style="text-align:center;margin-top:10%"><h1>Inscription terminée</h1>
      <p>Bienvenue <?php stripslashes(htmlspecialchars($_POST['pseudo']))?> vous êtes maintenant inscrit(e) sur le site VegFrance</p>
      <p>Cliquez <a href="sign_in.php">ici</a> pour revenir à la page de connexion</p></div>

      <?php
      $requete=$bdd->prepare("INSERT INTO users(firstname, lastname, pseudo, password, email, domaine, sign_up, last_visit, id_status) VALUES(?,?,?,?,?,?,?,?,?)");
      $requete->execute(array(
         $firstname,
         $lastname,
         $pseudo,
         $pass,
         $email,
         $domain,
         $temps,
         $temps,
         1
      ));
      $requete->CloseCursor();

      $subject = "Inscription à VegFrance";
      $message = "Bienvenue sur le site de VegFrance " . $firstname . " " . $lastname ." ! \n";
      $message .= "Pour rappel, \n";
      $message .= "votre identifiant est : " . $pseudo . "\n";
      $message .= "et votre mot de passe est : " . htmlspecialchars($_POST['password']);
      $message .= 'Connectez-vous <a href="https://vegfrance.univ-rennes1.fr/Gestion_BDD/src/php/sign_in.php">ici</a>.';
      $expeditor = "From: noreply@VegFrance.fr";

      $succes = mail($email, $subject, $message, $expeditor);
      if($succes)
      {
         ?>
         <br>
         <center>
            <div class="alert alert-success" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
               Email de confirmation envoyé avec succès.
            </div> <br>
         </center>
         <?php
         $recover = true;
      }
      else {
         ?>
         <br>
         <center>
            <div class="alert alert-danger" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
               Erreur survenue lors de l'envoi d'email de confirmation.
            </div> <br>
         </center>
         <?php
      }
   }
   else
   {
      include('../html/blank_page.htm');
      ?>
      <head>
         <title>Inscription VegFrance</title>
      </head>
      <div style="text-align:center;margin-top:10%"><h1>Inscription interrompue</h1>
      <label>Une ou plusieurs erreurs se sont produites pendant l'inscription :</label><br>
      <label><b><?php $i ?> erreur(s)</b></label><br>
      <label><?php $pseudo_erreur1 ?></label><br>
      <label><?php $pseudo_erreur2 ?></label><br>
      <label><?php $mdp_erreur ?></label><br>
      <label><?php $email_erreur1 ?></label><br>
      <label><?php $email_erreur2 ?></label><br>
      <label>Cliquez <a href="register.php">ici</a> pour recommencer</label>
      </div>
      <?php
   }
}
?>
