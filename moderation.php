<?php
session_start();

include('moderation.htm');

/* CONNEXION BDD */
include ('connexion.php');
$bdd = connexionMySQL();
/* CONNEXION FAITE */

function modifyUserRights($user, $newRight)
{
   $requete = $bdd->prepare('INSERT INTO users (pseudo, id_status) VALUES (?,?)');
   $requete->execute(array(
      $user,
      $newRight
   ));
   echo $user . " " . $newRight;
}

// On récupère tous les utilisateurs et on les liste avec leur statut hiérarchique
$reponse = $bdd->query("SELECT users.pseudo pseudo, users.id_user id_user, users.sign_up sign_up, users.id_status id_status, status.status status FROM status INNER JOIN users AS users ON users.id_status = status.id");

?>
<table style="margin-left:auto; margin-right:auto; text-align:justify; border-spacing:10px; border-collapse:separate;" border ="0" cellspacing="1" cellpadding="1">
   <?php
   while ($donnees = $reponse->fetch())
   {
      ?>
      <tr>
         <td>
            <?php echo "<b>".$donnees['pseudo']."</b>"; ?>
         </td>
         <td>
            <?php echo "Inscrit(e) le : ".$donnees['sign_up']; ?>
         </td>
         <td>
            <?php echo "<b>".$donnees['status']."</b>"; ?>
         </td>
         <td>
            <form method="get" action="moderation_user.php">
               <input type="submit" value="Modifier" name="<?php echo $donnees['status'];?>">
            </form>
         </td>
      </tr>
      <?php
   }
   ?>
</table>
