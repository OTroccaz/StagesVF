<?php
/* CONNEXION BDD */
include ('../../config/connection.php');
$bdd = connexionPgSQL();
/* CONNEXION FAITE */
session_start();

include('../html/forgotten_pass.htm');

// Génération d'une chaine aléatoire
function chaine_aleatoire($nb_car)
{
    $chaine = 'azertyuiopqsdfghjklmwxcvbn123456789';
    $nb_lettres = strlen($chaine) - 1;
    $generation = '';
    for($i=0; $i < $nb_car; $i++)
    {
        $pos = mt_rand(0, $nb_lettres);
        $car = $chaine[$pos];
        $generation .= $car;
    }
    return $generation;
}

if(isset($_POST['recover']))
{
   $recover = false;
   if (!$recover)
   {
      $pseudo = htmlspecialchars($_POST['pseudo']);
      $email = htmlspecialchars($_POST['email']);

      if(!empty($pseudo) && !empty($email)) {
         $requete = $bdd->prepare("SELECT email FROM users WHERE pseudo = '" . $speudo . "'");
         $donnees = $requete->fetch();
         if ($donnees['email'] == $email)
         {
            $req = $bdd->prepare('SELECT id_user FROM users WHERE pseudo = :pseudo AND email = :email');
            $req->execute(array('pseudo' => $pseudo,'email' => $email));

            $new_password = chaine_aleatoire(8);
            $sha1 = sha1($new_password);
            $subject = "VegFrance mot de passe perdu";
            $message = "Votre pseudo : " . $pseudo ."\n" .
            "Votre nouveau mot de passe : " . $new_password . "\n" .
            'Reconnectez vous en cliquant sur ce lien : https://vegfrance.univ-rennes1.fr/StagesVF/src/php/sign_in.php">';
            $expeditor = "From: noreply@VegFrance.fr";

            $req = $bdd->exec("UPDATE users SET password = '" . $sha1 . "' WHERE pseudo = '" . $pseudo ."' AND email = '" . $email . "'");

            $succes = mail($email, $subject, $message, $expeditor);
            if($succes)
            {
               ?>
               <br>
               <center>
                  <div class="alert alert-success" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
                     Email envoyé avec succès. Votre mot de passe a été réinitialisé.
                  </div> <br>
               </center>
               <?php
               $recover = true;
            }
            else {
               ?>
               <center>
                  <br>
                  <div class="alert alert-danger" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
                     Erreur survenue lors de l'envoi d'email.
                  </div> <br>
               </center>
               <?php
            }
         } else {
            ?>
            <center>
               <br>
               <div class="alert alert-danger" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
                  Pseudo et email non correspondants.
               </div> <br>
            </center>
            <?php
         }
      }
   }
}
?>
