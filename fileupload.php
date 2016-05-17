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

   include('blank_page.htm');

   function upload($index,$destination,$maxsize=FALSE,$extensions=FALSE)
   {
      //Test1: fichier correctement uploadé
      if (!isset($_FILES[$index]) OR $_FILES[$index]['error'] > 0)
      {
         //Si aucun fichier choisi avec le browser, redirection vers la page d'import directement
         header('Location: import.php');
         return FALSE;
      }
      //Test2: taille limite
      if ($maxsize !== FALSE AND $_FILES[$index]['size'] > $maxsize) return FALSE;
      //Test3: extension
      $ext = substr(strrchr($_FILES[$index]['name'],'.'),1);
      if ($extensions !== FALSE AND !in_array($ext,$extensions)) return FALSE;
      //Déplacement
      return move_uploaded_file($_FILES[$index]['tmp_name'],$destination);
   }

   function checkFileExtension($ext)
   {
      // on ajoute l'extention PNG pour les tests ^_^
      if ($ext == 'csv' || $ext == 'png' || $ext == 'jpg') {
         $pass = (int)1;
      } else {
         $pass = (int)0;
      }
      return (int)$pass;
   }

   // Undefined | Multiple Files | $_FILES Corruption Attack
   // If this request falls under any of them, treat it invalid.
   if (
   !isset($_FILES['fichier_importe']['error']) ||
   is_array($_FILES['fichier_importe']['error'])
   ) {
      throw new RuntimeException('Paramètres invalides.');
   }

   // On vérifie la valeur de $_FILES['fichier_importe']['error']
   switch ($_FILES['fichier_importe']['error']) {
      case UPLOAD_ERR_OK:
      break;
      case UPLOAD_ERR_NO_FILE:
      throw new RuntimeException('Aucun fichier envoyé.');
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
      throw new RuntimeException('Limite de taille de fichier dépassée.');
      default:
      throw new RuntimeException('Erreurs inconnues.');
   }

   // On vérifie aussi la taille du fichier ici
   if ($_FILES['fichier_importe']['size'] > 10485760) {
      throw new RuntimeException('Limite de taille de fichier dépassée.');
   }

   $ext = substr(strrchr($_FILES['fichier_importe']['name'], "."), 1);
   $fileAccepted = checkFileExtension($ext);

   if($fileAccepted == 0)
   {
      // Si le fichier n'est pas accepté
      // Redirection page d'import
      header("refresh:3; url=import.php"); // 3 secondes avant redirection
      echo 'Mauvais format de fichier, upload impossible.';
   } else {
      // Sinon génération HTML + php adéquat
      ?>
      <!DOCTYPE html>
      <html>
      <head>
         <title> Upload </title>
         <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
      </head>
      <div>
         <button type="button" onclick="disconnect.php" class="btn btn-secondary">Se déconnecter</button>
         <center>
            <h2> Confirmation de l'upload </h2>
            <?php

            // Insertion dans la BDD des données du fichier importé
            // Attention à la dateTime, source d'erreurs
            $req = $bdd->prepare('INSERT INTO files(up_filename, up_type, up_filesize, up_finalname, up_filedate) VALUES(?,?,?,?,?)');
            $req->execute(array(
               htmlspecialchars($_FILES['fichier_importe']['name']),
               htmlspecialchars($_POST['type_de_donnees']),
               htmlspecialchars($_FILES['fichier_importe']['size']),
               htmlspecialchars($_POST['type_de_donnees']). '_'.htmlspecialchars($_FILES['fichier_importe']['name']),
               date("Y-m-d H:i:s")
            ));

            // Si le repertoire uploads/ n'est pas créé, on fait mkdir
            if (!is_dir("uploads/")) mkdir('uploads/', 0777, true);
            // De même pour les 3 sous-dossiers correspondants aux types de données présents
            if (!is_dir("uploads/dataset/")) mkdir('uploads/dataset/', 0777, true);
            if (!is_dir("uploads/survey/")) mkdir('uploads/survey/', 0777, true);
            if (!is_dir("uploads/vegetation/")) mkdir('uploads/vegetation/', 0777, true);

            // Upload du fichier dans le bon répertoire
            $directory = htmlspecialchars($_POST['type_de_donnees']);
            // Renommage de la variable pour le bon dossier, français vers anglais
            if ($directory == 'Jeu de données') $directory = 'dataset';
            if ($directory == 'Relevé') $directory = 'survey';
            if ($directory == 'Végétation') $directory = 'vegetation';

            // On upload le fichier dans son répertoire
            $upload = upload('fichier_importe','uploads/' . $directory . '/' . htmlspecialchars($_FILES['fichier_importe']['name']) , 10485760, FALSE );
            // Confirmation
            if ($upload) echo "</br><b>Upload du fichier " . htmlspecialchars($_FILES['fichier_importe']['name']) . " réussi !</b></br></br></br>";
            ?>

            <form action="import.php">
               <input type="submit" value="Retour">
            </form>

         </center>
      </div>
      </html>
      <?php
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
