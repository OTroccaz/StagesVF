<?php

/* CONNEXION BDD */
include ('connexion.php');
$bdd = connexionMySQL();
/* CONNEXION FAITE */

if (empty($_POST['pseudo'])) // Si on la variable est vide, on peut considérer qu'on est sur la page de formulaire
{
   ?>
   <html>
   <body>
      <div>
         <h1>Inscription</h1>
         <form method="post" action="register.php">
            <fieldset><legend>Informations personnelles</legend>
               <label for="lastname">* Nom :</label>  <input name="lastname" type="text" id="lastname" /><br />
               <label for="firstname">* Prénom :</label><input name="firstname" type="text" id="firstname" /><br />
            </fieldset>
            <fieldset><legend>Identifiants</legend>
               <label for="pseudo">* Pseudo :</label>  <input name="pseudo" type="text" id="pseudo" /> (le pseudo doit contenir entre 3 et 15 caractères)<br />
               <label for="password">* Mot de Passe :</label><input type="password" name="password" id="password" /><br />
               <label for="confirm">* Confirmer le mot de passe :</label><input type="password" name="confirm" id="confirm" />
            </fieldset>
            <fieldset><legend>Contacts</legend>
               <label for="email">* Votre adresse Mail :</label><input type="text" name="email" id="email" /><br />
            </fieldset>
            <p>Les champs précédés d'un * sont obligatoires</p>
            <p><input type="submit" value="S'inscrire" /></p>
         </form>
         <form action="index.php">
            <input type="submit" value="Retour">
         </form>
      </div>
   </body>
   </html>

   <?php
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
   $firstname = $_POST['firstname'];
   $lastname = $_POST['lastname'];
   $pseudo = $_POST['pseudo'];
   $email = $_POST['email'];
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
      echo'<h1>Inscription terminée</h1>';
      echo'<p>Bienvenue '.stripslashes(htmlspecialchars($_POST['pseudo'])).' vous êtes maintenant inscrit(e) sur le site VegFrance</p>
      <p>Cliquez <a href="index.php">ici</a> pour revenir à la page de connexion</p>';


      $requete=$bdd->prepare("INSERT INTO users(firstname, lastname, pseudo, password, email, sign_up, last_visit) VALUES(?,?,?,?,?,?,?)");
      $requete->execute(array(
         $firstname,
         $lastname,
         $pseudo,
         $pass,
         $email,
         $temps,
         $temps
      ));

      // Et on définit les variables de sessions
      $_SESSION['pseudo'] = $pseudo;
      $_SESSION['id'] = $bdd->lastInsertId(); ;
      $_SESSION['connect'] = 1;
      $requete->CloseCursor();

      //Message
      $message = "Bienvenue sur le site de VegFrance " . $firstname . " " . $lastname ." !</BR>";
      $message .= "Pour rappel, </BR>";
      $message .= " - votre identifiant est : " . $pseudo . "</BR>";
      $message .= " et votre mot de passe est " . $_POST['password'];
      //Titre
      $titre = "Inscription à VegFrance";

      mail($_POST['email'], $titre, $message);
   }
   else
   {
      echo'<h1>Inscription interrompue</h1>';
      echo"<p>Une ou plusieurs erreurs se sont produites pendant l'inscription</p>";
      echo'<p>'.$i.' erreur(s)</p>';
      echo'<p>'.$pseudo_erreur1.'</p>';
      echo'<p>'.$pseudo_erreur2.'</p>';
      echo'<p>'.$mdp_erreur.'</p>';
      echo'<p>'.$email_erreur1.'</p>';
      echo'<p>'.$email_erreur2.'</p>';

      echo'<p>Cliquez <a href="register.php">ici</a> pour recommencer</p>';
   }
}
?>
</div>
</body>
</html>
