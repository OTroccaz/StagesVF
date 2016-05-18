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
   include ('export.htm');



   $retour_total = $bdd->query('SELECT COUNT(*) AS total FROM files'); //Nous récupérons le contenu de la requête dans $retour_total
   $donnees_total = $retour_total->fetch(); //On range retour sous la forme d'un tableau.
   $total = $donnees_total['total']; //On récupère le total pour le placer dans la variable $total.

   $messagesParPage = 5; //Nous allons afficher 5 messages par page.

   //Nous allons maintenant compter le nombre de pages.
   $nombreDePages = ceil($total / $messagesParPage);

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

   $premiereEntree=($pageActuelle-1)*$messagesParPage; // On calcul la première entrée à lire

   // La requête sql pour récupérer les messages de la page actuelle.
   $reponse = $bdd->query('SELECT * FROM files ORDER BY up_id DESC LIMIT '.$premiereEntree.', '.$messagesParPage.'');

   ?>
   <table class="table table-striped" style="margin:auto; width:600px">
      <thead>
         <tr>
            <th class="col-md-1 col-xs-1">ID</th>
            <th class="col-md-1 col-xs-1">Type de données</th>
            <th class="col-md-1 col-xs-1">Nom fichier</th>
            <th class="col-md-1 col-xs-1">Date upload</th>
            <th class="col-md-1 col-xs-1">Action</th>
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
               <td> <?php echo "<b>".$donnees['up_filedate']."</b>"; ?> </td>
               <td>
                  <form method="post" action="filedownload.php">
                     <input type="hidden" name="id" value="<?php echo $donnees['up_id']; ?>" />
                     <input type="submit" class="btn btn-primary" value="Télécharger">
                  </form>
               </td>
            </tr>
            <?php
         }
         ?>
      </tbody>
   </table>
   <?php
   echo '<p align="center" >Page : '; //Pour l'affichage, on centre la liste des pages
   for ($i = 1; $i <= $nombreDePages; $i++) //On fait notre boucle
   {
      //On va faire notre condition
      if($i == $pageActuelle) //S'il s'agit de la page actuelle...
      {
         echo ' [ '.$i.' ] ';
      }
      else //Sinon...
      {
         echo ' <a href="export.php?page='.$i.'">'.$i.'</a> ';
      }
   }
   echo '</p>';
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
