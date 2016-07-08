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

   include('../html/blank_page.htm');
   include('check_import/survey.php');
   include('check_import/dataset.php');
   include('check_import/vegetation.php');
   

   
   ?> <title>Importation</title>
   <?php
   
   	if (isset($_GET['verif']) && $_GET['verif'] == 1) // On vérifie que le variable existe.
	{
		?>
		<div class="alert alert-success" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
		Fichier accepté par la vérification !</div><br>
		<?php
	}
	if(isset($_GET['verif']) && $_GET['verif'] == 0){
		?>
		<div class="alert alert-danger" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
		Fichier non accepté par la vérification ...<br> Cliquez <a href="download_log.php">ici</a> pour télécharger le fichier d'erreurs.</div><br>
		<?php
	}
   

   function upload($formulaireUpload,$destination,$maxsize=FALSE,$extensions=FALSE,$nomFichier,$type,$bdd) {
      $check = TRUE; $verification = FALSE;
      if (!isset($_FILES[$formulaireUpload]) OR $_FILES[$formulaireUpload]['error'] > 0) { ?>
         <div class="alert alert-danger" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
            Erreur survenue lors de l'upload.</div> <br>
         <?php $check = FALSE;
      }
      if ($maxsize !== FALSE AND $_FILES[$formulaireUpload]['size'] > $maxsize){ ?>
         <div class="alert alert-danger" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
            Fichier trop important (taille maximale : 50 MB).</div> <br>
         <?php $check = FALSE;
      }
      $ext = substr(strrchr($_FILES[$formulaireUpload]['name'],'.'),1);
      if ($extensions !== FALSE AND !in_array($ext,$extensions)) { ?>
         <div class="alert alert-danger" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
            Mauvaise extension de fichier (seuls les fichier .csv sont acceptés).</div><br>
         <?php $check = FALSE;
      }
      $success = move_uploaded_file($_FILES[$formulaireUpload]['tmp_name'], '../../check/'.$nomFichier); // On upload vers le dossier de fichiers non vérifiés
      if ($success) { // S'il a bien été uploadé
         if ($check) { // Si pas d'erreurs concernant sa taille, etc
            if ($type == 'dataset') { // S'il est de type dataset
               $dataset = new dataset();
               $verification = $dataset->initialisationDatasetAll('../../check/'.$nomFichier, $bdd); // On fait la vérification
            } else if ($type == 'survey') { // S'il est de type survey
				$survey = new survey();
				$verification = $survey->initialisationSurveyAll('../../check/'.$nomFichier, $bdd, 0); // On fait la vérification
            } else if ($type == 'vegetation') { // S'il est de type vegetation
               $vegetation = new vegetation();
               $verification = $vegetation->initialisationVegetationAll('../../check/'.$nomFichier, $bdd); // On fait la vérification
            }
         }
         if ($check && $verification) { // Si tout s'est bien passé et que la vérification n'a eu aucune erreur
            rename('../../check/'.$nomFichier, $destination); // On copie le fichier qui était dans le dossier de fichiers non vérifiés au bon dossier ?>

            <?php
         } else if (!$verification){
            ?>
            <div class="alert alert-danger" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
               Fichier non accepté par la vérification ...<br> Cliquez <a href="download_log.php">ici</a> pour télécharger le fichier d'erreurs.</div><br>
            <?php

         }
      } else {$check = FALSE;}
      return $check && $verification;
   } ?>




   <center>
      <h2> Confirmation de l'upload </h2>
      <?php

      $fichier = htmlspecialchars($_FILES['fichier_importe']['name']);

      $path_parts = pathinfo($fichier);
      $fichier = $path_parts['filename'].'_'.time().'.'.$path_parts['extension'];

      $fichier = strtr($fichier,
      'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ', // On remplace les lettres accentutées par les non accentuées dans $fichier.
      'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy'); // Et on récupère le résultat dans fichier

      // En dessous, il y a l'expression régulière qui remplace tout ce qui n'est pas une lettre non accentuées ou un chiffre
      // dans $fichier par un tiret "-" et qui place le résultat dans $fichier.
      $fichier = preg_replace('/([^.a-z0-9_]+)/i', '-', $fichier);

      // Si le repertoire uploads/ n'est pas créé, on fait mkdir
      if (!is_dir("../../check/")) mkdir('../../check/', 0777, true);
      // Si le repertoire uploads/ n'est pas créé, on fait mkdir
      if (!is_dir("../../uploads/")) mkdir('../../uploads/', 0777, true);
      // De même pour les 3 sous-dossiers correspondants aux types de données
      if (!is_dir("../../uploads/dataset/")) mkdir('../../uploads/dataset/', 0777, true);
      if (!is_dir("../../uploads/survey/")) mkdir('../../uploads/survey/', 0777, true);
      if (!is_dir("../../uploads/vegetation/")) mkdir('../../uploads/vegetation/', 0777, true);

      // Upload du fichier dans le bon répertoire
      $directory = htmlspecialchars($_POST['type_de_donnees']);
      // Renommage de la variable pour le bon dossier, français vers anglais
      if ($directory == 'Jeu de données') $true_directory = 'dataset';
      if ($directory == 'Relevé') $true_directory = 'survey';
      if ($directory == 'Végétation') $true_directory = 'vegetation';

      if(isset($true_directory))
      {
         // On test si l'upload est ok
         $upload_possible = upload('fichier_importe', '../../uploads/'.$true_directory.'/'.$fichier, 52428800, array('csv'), $fichier, $true_directory, $bdd);
         // Confirmation
         if ($upload_possible) {
            ?>
            <div class="alert alert-success" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
               Upload du fichier <?php echo htmlspecialchars($fichier); ?> réussi !
            </div>
            <?php

            // Insertion dans la BDD des données du fichier importé
            $req = $bdd->prepare('INSERT INTO files(up_filename, up_type, up_filesize, up_finalname, up_filedate) VALUES(?,?,?,?,?)');
            $req->execute(array(
               $fichier,
               htmlspecialchars($_POST['type_de_donnees']),
               htmlspecialchars($_FILES['fichier_importe']['size']),
               htmlspecialchars($_POST['type_de_donnees']). '_'.$fichier,
               date("Y-m-d")
            ));

         } else {
            ?>
            <div class="alert alert-danger" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
               Upload du fichier <?php echo htmlspecialchars($fichier); ?> raté...
            </div>
            <?php
         }
      }
      ?>
      <form action="import.php">
         <input type="submit" class="btn btn-secondary" value="Retour">
      </form>
   </center>
</div>
</html>
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
