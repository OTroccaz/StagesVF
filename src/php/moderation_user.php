<?php
session_start();
if (isset($_SESSION['connect'])) // On vérifie que le variable existe.
{
   $connect = $_SESSION['connect']; // On récupère la valeur de la variable de session.
}
else
{
   $connect = 0; // Si $_SESSION['connect'] n'existe pas, on donne la valeur "0".
}

if ($connect == "1") // Si le visiteur s'est identifié.
{
   if ($_SESSION['status'] == 'Administrateur')
   {
      /* CONNEXION BDD */
      include ('../../config/connection.php');
      $bdd = connexionPgSQL();
      /* CONNEXION FAITE */

      function modifyUserRights($bdd, $newRight)
      {
         $user = htmlspecialchars($_POST['pseudo']);
         $requete = $bdd->exec("UPDATE users SET id_status = '" . $newRight . "' WHERE pseudo = '" . $user . "'");
         ?>
         <br><br>
         <center>
            <?php
            switch ($newRight) {
               case 1:
               ?>
               <div class="alert alert-success" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
                  L'utilisateur <?php echo $user ?> est à présent Utilisateur
               </div> <br>
               <?php
               break;
               case 2:
               ?>
               <div class="alert alert-success" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
                  L'utilisateur <?php echo $user ?> est à présent Fournisseur
               </div> <br>
               <?php
               break;
               case 3:
               ?>
               <div class="alert alert-success" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
                  L'utilisateur <?php echo $user ?> est à présent Gestionnaire
               </div> <br>
               <?php
               break;
               case 4:
               ?>
               <div class="alert alert-success" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
                  L'utilisateur <?php echo $user ?> est à présent Administrateur
               </div> <br>
               <?php
               break;
            }
            ?>
         </center>
         <?php
      }

      include('../html/moderation_user.htm');

      // Si le bouton 'valider' est cliqué, alors on écrase le statut choisi sur le statut de l'utilisateur
      if (isset($_POST['status_validation']))
      {
         modifyUserRights($bdd, htmlspecialchars($_POST['change_status']));
      }
   } else {
      ?>
      <head>
         <meta charset="utf-8" />
         <title>Modération</title>
      </head>
      <?php
      include ('../html/blank_page.htm');
      echo '<p style="text-align:center;margin-top:10%">Votre statut ne vous permet pas d\'accéder à cette page</p>';
   }
} else {
   ?>
   <head>
      <meta charset="utf-8" />
      <title>Connexion VegFrance</title>
   </head>
   <?php
   include('../html/sign_in.htm');
   echo '<p style="text-align:center;color:red">Vous n\'êtes pas autorisé(e) à accéder à cette zone</p>';
   exit;
}
?>
