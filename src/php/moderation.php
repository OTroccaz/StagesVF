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
      $bdd = connexionMySQL();
      /* CONNEXION FAITE */

      if(!isset($_SESSION['messagesParPage']))
      {
         $_SESSION['messagesParPage'] = 5;
      }

      if(isset($_POST['nb_elem']))
      {
         $_SESSION['messagesParPage'] = htmlspecialchars($_POST['select_nb_elem']);
      }

      include('../html/moderation.htm');

      $retour_total = $bdd->query("SELECT COUNT(*) AS total FROM users WHERE pseudo != '" . $_SESSION['pseudo'] . "'"); //Nous récupérons le contenu de la requête dans $retour_total
      $donnees_total = $retour_total->fetch(); //On range retour sous la forme d'un tableau.
      $total = $donnees_total['total']; //On récupère le total pour le placer dans la variable $total.

      //Nous allons maintenant compter le nombre de pages.
      $nombreDePages = ceil($total / $_SESSION['messagesParPage']);

      if(isset($_GET['page'])) // Si la variable $_GET['page'] existe...
      {
         $pageActuelle = intval($_GET['page']);
         if($pageActuelle > $nombreDePages) // Si la valeur de $pageActuelle (le numéro de la page) est plus grande que $nombreDePages...
         {
            $pageActuelle = $nombreDePages;
         }
      }
      else // Sinon
      {
         $pageActuelle = 1; // La page actuelle est la n°1
      }

      $premiereEntree=($pageActuelle-1)*$_SESSION['messagesParPage']; // On calcul la première entrée à lire

      // La requête sql pour récupérer les messages de la page actuelle.
      $reponse = $bdd->query('SELECT * FROM files ORDER BY up_id DESC LIMIT '.$premiereEntree.', '.$_SESSION['messagesParPage'].'');

      // On récupère tous les utilisateurs et on les liste avec leur statut hiérarchique
      $sql = "SELECT users.pseudo pseudo, users.id_user id_user, users.sign_up sign_up, users.id_status id_status, status.status status";
      $sql .= " FROM status INNER JOIN users AS users ON users.id_status = status.id";
      $sql .= " WHERE pseudo != '" . $_SESSION['pseudo'] . "'"; // on exclue l'admin, qui ne va pas s'ôter des droits quand même !
      $sql .= " ORDER BY id_user DESC LIMIT ".$premiereEntree.",".$_SESSION['messagesParPage']."";
      $reponse = $bdd->query($sql);

      ?>
      <table class="table table-striped" style="margin:auto; width:600px;table-layout:fixed; word-wrap:break-word;">
         <?php
         $i = 1;
         ?>
         <thead>
            <tr>
               <th class="col-md-1 col-xs-1">#</th>
               <th class="col-md-2 col-xs-2">Pseudo</th>
               <th class="col-md-2 col-xs-2">Inscription</th>
               <th class="col-md-3 col-xs-3">Statut</th>
               <th class="col-md-2 col-xs-2">Action 1</th>
               <th class="col-md-2 col-xs-3">Action 2</th>
            </tr>
         </thead>
         <tbody class="searchable">
            <?php
            while ($donnees = $reponse->fetch())
            {
               ?>
               <tr>
                  <th scope="row"> <?php echo $i; ?> </th>
                  <td> <?php echo "<b>".$donnees['pseudo']."</b>"; ?> </td>
                  <td> <?php echo $donnees['sign_up']; ?> </td>
                  <td> <?php echo "<b>".$donnees['status']."</b>"; ?> </td>
                  <td>
                     <form method="post" action="moderation_user.php">
                        <input type="hidden" name="status" value="<?php echo $donnees['status']; ?>" />
                        <input type="hidden" name="pseudo" value="<?php echo $donnees['pseudo']; ?>" />
                        <input type="submit" class="btn btn-primary" value="Modifier">
                     </form>
                  </td>
                  <td>
                     <form method="post" action="moderation.php">
                        <input type="hidden" name="pseudoDelete" value="<?php echo $donnees['pseudo']; ?>" />
                        <input type="submit" class="btn btn-danger" value="Supprimer">
                     </form>
                  </td>
               </tr>
               <?php
               $i++;
            }
            ?>
         </tbody>
      </table>
      <div class="text-center">
         <ul class="pagination">
            <li>
               <?php if ($pageActuelle - 1 < 1) { ?>
                  <a href="moderation.php?page=<?php echo $pageActuelle ?>" aria-label="Previous">
                     <span aria-hidden="true">&laquo;</span>
                  </a>
                  <?php
               } else { ?>
                  <a href="moderation.php?page=<?php echo $pageActuelle - 1 ?>" aria-label="Previous">
                     <span aria-hidden="true">&laquo;</span>
                  </a>
                  <?php
               }
               ?>
            </li>
            <?php
            $points = FALSE;
            for ($i = 1; $i <= $nombreDePages; $i++) //On fait notre boucle
            {
               if($i == $pageActuelle)
               {
                  echo '<li class="active"><a href="#">'.$i.'</a></li>';
               }
               else if($i <= 2)
               {
                  echo '<li><a href="export.php?page='.$i.'">'.$i.'</a></li>';
               }
               else if($i == ($pageActuelle - 1) OR $i == ($pageActuelle + 1))
               {
                  echo '<li><a href="export.php?page='.$i.'">'.$i.'</a></li>';
                  $points = FALSE;
               }
               else if($i >= ($nombreDePages - 1))
               {
                  echo '<li><a href="export.php?page='.$i.'">'.$i.'</a></li>';
               }
               else if(!$points)
               {
                  echo '<li><a href="export.php?page='.$i.'">'.'...'.'</a></li>';
                  $points = TRUE;
               }
            }
            ?>
            <li>
               <?php if ($pageActuelle + 1 > $nombreDePages) { ?>
                  <a href="moderation.php?page=<?php echo $pageActuelle ?>" aria-label="Next">
                     <span aria-hidden="true">&raquo;</span>
                  </a>
                  <?php
               } else { ?>
                  <a href="moderation.php?page=<?php echo $pageActuelle + 1 ?>" aria-label="Next">
                     <span aria-hidden="true">&raquo;</span>
                  </a>
                  <?php
               }
               ?>
            </li>
         </ul>
      </div>
      <?php

      function deleteUser($bdd, $user) {
         $reponse = $bdd->exec("DELETE FROM users WHERE pseudo = '" . $user . "'");
         echo '<label style="display:block;text-align:center">' . $user . ' a été supprimé(e) de la base de données</label>';
      }

      // si on appuie sur Supprimer (bouton rouge) en face d'un utilisateur
      if(isset($_POST['pseudoDelete']))
      {
         deleteUser($bdd, htmlspecialchars($_POST['pseudoDelete']));
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
