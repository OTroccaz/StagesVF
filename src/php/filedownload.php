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

   // On récupère les infos du fichier correspondant à l'id indiqué dans l'url
   $reponse = $bdd->query("SELECT up_filename, up_type, up_filesize, up_finalname, up_id FROM files WHERE up_id = " . htmlspecialchars($_POST['id']));
   $donnees = $reponse->fetch();
   if($_SESSION['status'] == 'Utilisateur' || $_SESSION['status'] == 'Fournisseur')
   {
      include ('../html/blank_page.htm');
      ?>
      <head>
         <title>Ajouter demande d'exportation</title>
      </head>
      <body>
         <div style="margin:0 auto; width:500px">
            <center>
               <div>
                  <h2>Exportation des fichiers</h2>
               </div>
               <br><label>Voulez-vous ajouter ce fichier à votre liste de demandes d'exportation ?</label><br><br>
               <form method="post" action="../php/requests.php" class="form-horizontal">
                  <div class="form-group">
                     <input type="hidden" name="id_file" value="<?php echo $donnees['up_id']; ?>">
                     <input type="hidden" name="filename" value="<?php echo $donnees['up_filename']; ?>">
                     <input type="submit" class="btn btn-md btn-primary" name="yes_export" value="Oui">
                     <input type="submit" class="btn btn-md btn-secondary" name="no_export" value="Non">
                  </div>
               </form>
            </center>
         </div>
      </body>
      <?php
   } else {
      // Si le fichier est trouvé, alors
      if ($donnees = $reponse->fetch()) {
         // On renomme la variable des sous-dossiers en anglais
         $directory = $donnees['up_type'];
         if ($directory == 'Jeu de données') $directory = 'dataset';
         if ($directory == 'Relevé') $directory = 'survey';
         if ($directory == 'Végétation') $directory = 'vegetation';
         // Création des headers, pour indiquer au navigateur qu'il s'agit d'un fichier à télécharger
         header('Content-Transfer-Encoding: binary'); // Transfert en binaire (fichier)
         header('Content-Disposition: attachment; filename="'.$donnees["up_finalname"].'"'); // Nom du fichier
         header('Content-Length: '.$donnees["up_filesize"]); // Taille du fichier
         // Envoi du fichier dont le chemin est passé en paramètre
         readfile("../../uploads/".$directory.'/'.$donnees["up_filename"].'"');
      } else {
         // Sinon on ne fait rien
         echo "Le fichier n'existe pas";
      }
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
