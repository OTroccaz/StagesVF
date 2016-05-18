<?php

/* CONNEXION BDD */
include ('connexion.php');
$bdd = connexionMySQL();
/* CONNEXION FAITE */
session_start();

if (empty($_POST['pseudo'])) // Si on la variable est vide, on peut considérer qu'on est sur la page de formulaire
{
   include('register.htm');
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
   $temps = date("Y-m-d H:i:s");
   $firstname = htmlspecialchars($_POST['firstname']);
   $lastname = htmlspecialchars($_POST['lastname']);
   $pseudo = htmlspecialchars($_POST['pseudo']);
   $email = htmlspecialchars($_POST['email']);
   $pass = md5($_POST['password']);
   $confirm = md5($_POST['confirm']);

   // Vérification du pseudo
   $requete=$bdd->prepare('SELECT COUNT(*) AS nb_users FROM users WHERE pseudo =:pseudo');
   $requete->bindValue(':pseudo',$pseudo, PDO::PARAM_STR);
   $requete->execute();
   $pseudo_free=($requete->fetchColumn()==0)?1:0;
   $requete->CloseCursor();
   if(!$pseudo_free)
   {
      $pseudo_erreur1 = "Votre pseudo est déjà utilisé par un membre";
      $i++;
   }

   if (strlen($pseudo) < 3 || strlen($pseudo) > 15)
   {
      $pseudo_erreur2 = "Votre pseudo est soit trop grand, soit trop petit";
      $i++;
   }

   // Vérification du mdp
   if ($pass != $confirm || empty($confirm) || empty($pass))
   {
      $mdp_erreur = "Votre mot de passe et votre confirmation diffèrent, ou sont vides";
      $i++;
   }

   // Vérification de l'adresse email

   // Il faut que l'adresse email n'ait jamais été utilisée
   $requete=$bdd->prepare('SELECT COUNT(*) AS nb_users FROM users WHERE email =:mail');
   $requete->bindValue(':mail',$email, PDO::PARAM_STR);
   $requete->execute();
   $mail_free=($requete->fetchColumn()==0)?1:0;
   $requete->CloseCursor();

   if(!$mail_free)
   {
      $email_erreur1 = "Votre adresse email est déjà utilisée par un membre";
      $i++;
   }
   // On vérifie la forme maintenant
   if (!preg_match("#^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]{2,}\.[a-z]{2,4}$#", $email) || empty($email))
   {
      $email_erreur2 = "Votre adresse E-Mail n'a pas un format valide";
      $i++;
   }

   if ($i==0)
   {
      include('blank_page.htm');
      echo'<div  style="text-align:center;margin-top:10%"><h1>Inscription terminée</h1>';
      echo'<p>Bienvenue '.stripslashes(htmlspecialchars($_POST['pseudo'])).' vous êtes maintenant inscrit(e) sur le site VegFrance</p>
      <p>Cliquez <a href="sign_in.php">ici</a> pour revenir à la page de connexion</p></div>';


      $requete=$bdd->prepare("INSERT INTO users(firstname, lastname, pseudo, password, email, sign_up, last_visit, id_status) VALUES(?,?,?,?,?,?,?,?)");
      $requete->execute(array(
         $firstname,
         $lastname,
         $pseudo,
         $pass,
         $email,
         $temps,
         $temps,
         2
      ));
      $requete->CloseCursor();

      // Message
      $message = "Bienvenue sur le site de VegFrance " . $firstname . " " . $lastname ." !</BR>";
      $message .= "Pour rappel, </BR>";
      $message .= " - votre identifiant est : " . $pseudo . "</BR>";
      $message .= " et votre mot de passe est " . $_POST['password'];
      // Titre
      $titre = "Inscription à VegFrance";

      //mail($_POST['email'], $titre, $message);
   }
   else
   {
      include('blank_page.htm');
      echo'<div style="text-align:center;margin-top:10%"><h1>Inscription interrompue</h1>';
      echo"<p>Une ou plusieurs erreurs se sont produites pendant l'inscription</p>";
      echo'<p>'.$i.' erreur(s)</p>';
      echo'<p>'.$pseudo_erreur1.'</p>';
      echo'<p>'.$pseudo_erreur2.'</p>';
      echo'<p>'.$mdp_erreur.'</p>';
      echo'<p>'.$email_erreur1.'</p>';
      echo'<p>'.$email_erreur2.'</p></div>';

      echo'<p>Cliquez <a href="register.php">ici</a> pour recommencer</p>';
   }
}
?>
