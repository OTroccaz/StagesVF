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
   if ($_SESSION['status'] == 'Utilisateur' || $_SESSION['status'] == 'Fournisseur')
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

         $retour_total = $bdd->query("SELECT COUNT(*) AS total FROM request WHERE requester='" . $_SESSION['pseudo'] . "'"); //Nous récupérons le contenu de la requête dans $retour_total
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
         $reponse = $bdd->query("SELECT * FROM request WHERE requester = '" . $_SESSION['pseudo'] . "' ORDER BY id_request DESC LIMIT ".$_SESSION['messagesParPage']." OFFSET ".$premiereEntree);
         ?>
         <center>
            <table class="table table-striped sortable" style="margin:auto; width:600px;table-layout:fixed; word-wrap:break-word;">
               <thead>
                  <tr>
                     <th class="col-md-1 col-xs-1">ID</th>
                     <th class="col-md-5 col-xs-3">Nom fichier</th>
                     <th class="col-md-3 col-xs-2">Date</th>
                     <th class="col-md-3 col-xs-2">Action</th>
                  </tr>
               </thead>
               <tbody class="searchable">
                  <?php
                  while ($donnees = $reponse->fetch())
                  {
                     ?>
                     <tr>
                        <td> <?php echo "<b>".$donnees['id_request']."</b>"; ?> </td>
                        <td> <?php echo $donnees['filename']; ?> </td>
                        <td> <?php echo "<b>".$donnees['date_request']."</b>"; ?> </td>
                        <td>
                           <?php
                           if ($donnees['allowed'] == 'oui')
                           {?>
                              <form method="post" action="filedownload_from_request.php">
                                 <input type="hidden" name="id" value="<?php echo htmlspecialchars($donnees['id_file']); ?>" />
                                 <input type="submit" class="btn btn-primary" value="Télécharger">
                              </form>
                              <?php
                           } else if ($donnees['allowed'] == 'non')
                           {
                              ?>
                              <input type="submit" disabled="disabled" class="btn btn-danger" value="Refusé">
                              <?php
                           } else {
                              ?>
                              <input type="submit" disabled="disabled" class="btn btn-primary" value="En attente">
                              <?php
                           }?>
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
                        <a href="requests.php?page=<?php echo $pageActuelle ?>" aria-label="Previous">
                           <span aria-hidden="true">&laquo;</span>
                        </a>
                        <?php
                     } else { ?>
                        <a href="requests.php?page=<?php echo $pageActuelle - 1?>" aria-label="Previous">
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
                        echo '<li><a href="requests.php?page='.$i.'">'.$i.'</a></li>';
                     }
                     else if($i == ($pageActuelle - 1) OR $i == ($pageActuelle + 1))
                     {
                        echo '<li><a href="requests.php?page='.$i.'">'.$i.'</a></li>';
                        $points = FALSE;
                     }
                     else if($i >= ($nombreDePages - 1))
                     {
                        echo '<li><a href="requests.php?page='.$i.'">'.$i.'</a></li>';
                     }
                     else if(!$points)
                     {
                        echo '<li><a href="requests.php?page='.$i.'">'.'...'.'</a></li>';
                        $points = TRUE;
                     }
                  }
                  ?>
                  <li>
                     <?php if ($pageActuelle + 1 > $nombreDePages) { ?>
                        <a href="requests.php?page=<?php echo $pageActuelle ?>" aria-label="Next">
                           <span aria-hidden="true">&raquo;</span>
                        </a>
                        <?php
                     } else { ?>
                        <a href="requests.php?page=<?php echo $pageActuelle + 1 ?>" aria-label="Next">
                           <span aria-hidden="true">&raquo;</span>
                        </a>
                        <?php
                     }
                     ?>
                  </li>
               </ul>
            </div>
         </center>
         <?php
      }
   } else {
      ?>
      <head>
         <meta charset="utf-8" />
         <title>Mes demandes d'exportation</title>
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

if (isset($_POST{'no_export'})) // Si on a cliqué sur NON lors de la demande d'exportation
{
   header('Location:management.php'); // On est renvoyé sur la page d'exportation
}
else if (isset($_POST['yes_export'])) // Si on a cliqué sur OUI lors de la demande d'exportation
{
   include('../html/requests.htm');
   // On insert la demande dans la BDD pour l'afficher avec les autres
   $requete = $bdd->exec("INSERT INTO request(requester, filename, date_request, id_file, allowed) VALUES ('".$_SESSION['pseudo']."','".$_POST['filename']."','".date("Y-m-d")."','".$_POST['id_file']."','attente')");
   ?>
   <center>
      <div class="alert alert-success" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
         Demande d'exportation ajoutée.
      </div> <br>
      <?php

      // Supprimez toute la partie ---email--- si vous ne souhaitez pas avoir d'email à chaque demande d'import
      // ---email---
      $requete = $bdd->query("SELECT firstname, lastname, email FROM users WHERE pseudo ='". $_SESSION['pseudo'] ."'");
      $donnees = $requete->fetch();
      $subject = "Demande d'exportation";
      $message = "Demande d'exportation de la part de " . $_SESSION['pseudo'] . " ( " . $donnees['firstname'] . " " . $donnees['lastname'] . " ), le " . date("Y-m-d") . " \n";
      $message .= "Pour le fichier : " . $_POST['filename'];
      $expeditor = "From:noreply@VegFrance.fr";

      // enlever les commentaires sur la ligne de requete et la boucle while (les 5 suivantes) lorsque le ligne sera opérationnel
      // $requete = $bdd->query("SELECT email FROM users WHERE id_status = 4 OR id_status = 5");
      // while ($donnees = $requete->fetch())
      // {
      //    $success = mail($donnees['email'], $subject, $message, $expeditor);
      // }

      // garder cette ligne pour éviter le spam d'emails pour le moment
      // email envoyé sur mon adresse perso pour ne pas gêner alexia par exemple lors des tests
      mail("adrienleblanc53@gmail.com", $subject, $message, $expeditor);

      // ---email---
      afficherPage($bdd);
      ?>
   </center>
   <?php
} else {
   include('../html/requests.htm');
   afficherPage($bdd);
}
