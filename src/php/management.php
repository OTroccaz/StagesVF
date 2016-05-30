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
   include ('../../config/connection.php');
   $bdd = connexionMySQL();
   /* CONNEXION FAITE */

   if(!isset($_SESSION['messagesParPage']))
   {
      $_SESSION['messagesParPage'] = 10;
   }

   if(isset($_POST['nb_elem']))
   {
      $_SESSION['messagesParPage'] = htmlspecialchars($_POST['select_nb_elem']);
   }

   include ('../html/management.htm');

   $retour_total = $bdd->query('SELECT COUNT(*) AS total FROM files'); //Nous récupérons le contenu de la requête dans $retour_total
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

   ?>
   <table class="table table-striped sortable" style="margin:auto; width:600px;table-layout:fixed; word-wrap:break-word;">
      <thead>
         <tr>
            <th class="col-md-1 col-xs-1">ID</th>
            <th class="col-md-2 col-xs-2">Type de données</th>
            <th class="col-md-3 col-xs-3">Nom fichier</th>
            <th class="col-md-2 col-xs-2">Date upload</th>
            <th class="col-md-2 col-xs-2">Action</th>
         </tr>
      </thead>
      <tbody class="searchable">
         <?php
         while ($donnees = $reponse->fetch())
         {
            ?>
            <tr>
               <td> <?php echo "<b>".$donnees['up_id']."</b>"; ?> </td>
               <td> <?php echo $donnees['up_type']; ?> </td>
               <td> <?php echo "<b>".$donnees['up_filename']."</b>"; ?> </td>
               <td> <?php echo $donnees['up_filedate']; ?> </td>
               <td>
                  <form method="post" action="filedownload.php">
                     <input type="hidden" name="id" value="<?php echo htmlspecialchars($donnees['up_id']); ?>" />
                     <input type="submit" class="btn btn-primary" value="<?php
                     if ($_SESSION['status'] == 'Administrateur' || $_SESSION['status'] == 'Gestionnaire')
                     {
                        echo 'Télécharger';
                     } else {
                        echo 'Ajout export';
                     }?>">
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
               <a href="management.php?page=<?php echo $pageActuelle ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
               </a>
               <?php
            } else { ?>
               <a href="management.php?page=<?php echo $pageActuelle - 1?>" aria-label="Previous">
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
               echo '<li><a href="management.php?page='.$i.'">'.$i.'</a></li>';
            }
            else if($i == ($pageActuelle - 1) OR $i == ($pageActuelle + 1))
            {
               echo '<li><a href="management.php?page='.$i.'">'.$i.'</a></li>';
               $points = FALSE;
            }
            else if($i >= ($nombreDePages - 1))
            {
               echo '<li><a href="management.php?page='.$i.'">'.$i.'</a></li>';
            }
            else if(!$points)
            {
               echo '<li><a href="management.php?page='.$i.'">'.'...'.'</a></li>';
               $points = TRUE;
            }
         }
         ?>
         <li>
            <?php if ($pageActuelle + 1 > $nombreDePages) { ?>
               <a href="management.php?page=<?php echo $pageActuelle ?>" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
               </a>
               <?php
            } else { ?>
               <a href="management.php?page=<?php echo $pageActuelle + 1 ?>" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
               </a>
               <?php
            }
            ?>
         </li>
      </ul>
   </div>
   <?php
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
