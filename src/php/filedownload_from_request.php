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
   $bdd = connexionPgSQL();
   /* CONNEXION FAITE */

   // On récupère les infos du fichier correspondant à l'id indiqué dans l'url
   $reponse = $bdd->query("SELECT up_filename, up_type, up_filesize, up_finalname FROM files WHERE up_id = " . htmlspecialchars($_POST['id']));
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
      include('../html/blank_page.htm');
      ?>
      <center>
         <label style="margin-top:10%">Le fichier n'existe pas</label>
      </center>
      <?php
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
