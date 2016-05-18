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
   /* CONNEXION BDD */
   include ('connexion.php');
   $bdd = connexionMySQL();
   /* CONNEXION FAITE */

   function modifyUserRights($bdd, $newRight)
   {
      $user = $_POST['pseudo'];
      $requete = $bdd->exec("UPDATE users SET id_status = '" . $newRight . "' WHERE pseudo = '" . $user . "'");

      switch ($newRight) {
         case 2:
         echo '<p style="text-align:center"> L\'utilisateur ' . $user . ' est à présent Utilisateur</p>';
         break;
         case 3:
         echo '<p style="text-align:center"> L\'utilisateur ' . $user . ' est à présent Fournisseur</p>';
         break;
         case 4:
         echo '<p style="text-align:center"> L\'utilisateur ' . $user . ' est à présent Gestionnaire</p>';
         break;
         case 5:
         echo '<p style="text-align:center"> L\'utilisateur ' . $user . ' est à présent Administrateur</p>';
         break;
      }
   }

   include('moderation_user.htm');
   ?>
   <center>
      <div style="margin:auto; width:500px">
         <form method="post" action="moderation_user.php">
            <label for="change_status"> Statut de <b><?php echo $_POST['pseudo']; ?> : </label>
               <select class="form-control" style="width:200px; margin:auto" name="change_status" id="change_status" size="1">
                  <?php
                  // on met en évidence le  statut du user en le mettant par défaut dans la liste
                  if (isset($_POST['status'])) {
                     ?>
                     <option value="2" <?php if($_POST['status'] == "Utilisateur") echo 'selected="selected"';?> >Utilisateur</option>
                     <option value="3" <?php if($_POST['status'] == "Fournisseur") echo 'selected="selected"';?> >Fournisseur</option>
                     <option value="4" <?php if($_POST['status'] == "Gestionnaire") echo 'selected="selected"';?> >Gestionnaire</option>
                     <option value="5" <?php if($_POST['status'] == "Administrateur") echo 'selected="selected"';?> >Administrateur</option>
                     <?php
                  } else {
                     ?>
                     <option value="2">Utilisateur</option>
                     <option value="3">Fournisseur</option>
                     <option value="4">Gestionnaire</option>
                     <option value="5">Administrateur</option>
                     <?php
                  }
                  ?>
               </select></br></br></br>
               <input type="hidden" name="pseudo" value="<?php echo $_POST['pseudo']; ?>" />
               <input type="submit" value="Valider" class="btn btn-primary" name="status_validation"></br>
            </p>
         </form>
         <form action="moderation.php">
            <input type="submit" class="btn btn-secondary" value="Retour">
         </form>
      </div>
   </center>
   <?php
   // Si le bouton 'valider' est cliqué, alors on écrase le statut choisi sur le statut de l'utilisateur
   if (isset($_POST['status_validation']))
   {
      modifyUserRights($bdd, $_POST['change_status']);
   }
} else {
   echo '<p style="text-align:center">Vous n\'êtes pas autorisé(e) à acceder à cette zone</p>';
   ?>
   <head>
      <meta charset="utf-8" />
      <title>Connexion VegFrance</title>
   </head>
   <?php
   include('sign_in.htm');
   exit;
}
?>
