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
   if ($_SESSION['status'] == 'Administrateur' || $_SESSION['status'] == 'Gestionnaire')
   {
      /* CONNEXION BDD */
      include ('../../config/connection.php');
      $bdd = connexionPgSQL();
      /* CONNEXION FAITE */

      function afficherPage($bdd)
      {
         if(!isset($_SESSION['messagesParPage']))
         {
            $_SESSION['messagesParPage'] = 10;
         }
         if(isset($_POST['nb_elem']))
         {
            $_SESSION['messagesParPage'] = htmlspecialchars($_POST['select_nb_elem']);
         }
         include ('../html/authorizations_management.htm');
         $retour_total = $bdd->query("SELECT COUNT(*) AS total FROM request WHERE allowed = 'oui'"); //Nous récupérons le contenu de la requête dans $retour_total
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
         $reponse = $bdd->query("SELECT * FROM request WHERE allowed = 'attente' ORDER BY requester LIMIT ".$_SESSION['messagesParPage']." OFFSET ".$premiereEntree);
         ?>
         <table class="table table-striped sortable" style="margin:auto; width:600px;table-layout:fixed; word-wrap:break-word;">
            <thead>
               <tr>
                  <th class="col-md-1 col-xs-1">ID</th>
                  <th class="col-md-3 col-xs-3">Nom fichier</th>
                  <th class="col-md-2 col-xs-2">Date</th>
                  <th class="col-md-2 col-xs-2">Demandeur</th>
                  <th class="col-md-2 col-xs-2">Action 1</th>
                  <th class="col-md-2 col-xs-2">Action 2</th>
               </tr>
            </thead>
            <tbody class="searchable">
               <?php
               while ($donnees = $reponse->fetch())
               {
                  ?>
                  <tr>
                     <td> <?php echo "<b>".$donnees['id_request']."</b>"; ?> </td>
                     <td> <?php echo "<b>".$donnees['filename']."</b>"; ?> </td>
                     <td> <?php echo $donnees['date_request']; ?> </td>
                     <td> <?php echo "<b>".$donnees['requester']."</b>"; ?> </td>
                     <td>
                        <form method="post" action="authorizations_management.php">
                           <input type="hidden" name="id" value="<?php echo htmlspecialchars($donnees['id_file']); ?>" />
                           <input type="submit" name="allow_yes" class="btn btn-primary" value="Autoriser">
                        </form>
                     </td>
                     <td>
                        <form method="post" action="authorizations_management.php">
                           <input type="hidden" name="id" value="<?php echo htmlspecialchars($donnees['id_file']); ?>" />
                           <input type="submit" name="allow_no" class="btn btn-danger" value="Refuser">
                        </form>
                     </td>
                  </tr>
                  <?php
               }
               ?>
            </tbody>
         </table>
         <div class="text-center">
            <ul class="pagination">
               <li>
                  <?php
                  if ($pageActuelle - 1 < 1) { ?>
                     <a href="authorizations_management.php?page=<?php echo $pageActuelle ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                     </a>
                     <?php
                  } else { ?>
                     <a href="authorizations_management.php?page=<?php echo $pageActuelle - 1?>" aria-label="Previous">
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
                     echo '<li><a href="authorizations_management.php?page='.$i.'">'.$i.'</a></li>';
                  }
                  else if($i == ($pageActuelle - 1) OR $i == ($pageActuelle + 1))
                  {
                     echo '<li><a href="authorizations_management.php?page='.$i.'">'.$i.'</a></li>';
                     $points = FALSE;
                  }
                  else if($i >= ($nombreDePages - 1))
                  {
                     echo '<li><a href="authorizations_management.php?page='.$i.'">'.$i.'</a></li>';
                  }
                  else if(!$points)
                  {
                     echo '<li><a href="authorizations_management.php?page='.$i.'">'.'...'.'</a></li>';
                     $points = TRUE;
                  }
               }
               ?>
               <li>
                  <?php if ($pageActuelle + 1 > $nombreDePages) { ?>
                     <a href="authorizations_management.php?page=<?php echo $pageActuelle ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                     </a>
                     <?php
                  } else { ?>
                     <a href="authorizations_management.php?page=<?php echo $pageActuelle + 1 ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                     </a>
                     <?php
                  }
                  ?>
               </li>
            </ul>
         </div>
         <?php
      }

      // Si admin clique sur Autoriser ou Refuser en face d'un fichier
      if (isset($_POST['allow_yes']) || isset($_POST['allow_no']))
      {
         if (isset($_POST['allow_yes']))
         {
            // On update la valeur allowed à 'oui' pour le fichier
            $requete2 = $bdd->exec("UPDATE request SET allowed = 'oui' WHERE id_file ='".$_POST['id']."'");
            afficherPage($bdd);

            $requete = $bdd->query("SELECT email FROM users WHERE pseudo ='". $_SESSION['pseudo'] ."'");
            $donnees = $requete->fetch();

            $email = $donnees['email'];
            $subject = "VegFrance - Droit d'exportation accordé";
            $message = "Une de vos demandes d'exportation effectuée a été autorisée par un administrateur. \n";
            $message .= "Vous pouvez dès à présent télécharger le fichier depuis le lien suivant : https://vegfrance.univ-rennes1.fr/StagesVF/src/php/requests.php";
            $expeditor = "From:noreply@VegFrance.fr";
            mail($email, $subject, $message, $expeditor);

         } else if (isset($_POST['allow_no']))
         {
            // On update la valeur allowed à 'non' pour le fichier
            $requete2 = $bdd->exec("UPDATE request SET allowed = 'non' WHERE id_file ='".$_POST['id']."'");
            afficherPage($bdd);
         }
      } else {
         afficherPage($bdd);
      }
   } else {
      ?>
      <head>
         <meta charset="utf-8" />
         <title>Autorisations</title>
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
