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

   include('../html/blank_page.htm');
   ?> <title>Importation</title>
   <?php

   function upload($index,$destination,$maxsize=FALSE,$extensions=FALSE,$nomFichier)
   {
      $check = TRUE;
      //Test1: fichier correctement uploadé
      if (!isset($_FILES[$index]) OR $_FILES[$index]['error'] > 0)
      {
         ?>
         <div class="alert alert-danger" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
            Erreur survenue lors de l'upload.
         </div> <br>
         <?php
         $check = FALSE;
      }
      //Test2: taille limite
      if ($maxsize !== FALSE AND $_FILES[$index]['size'] > $maxsize){
         ?>
         <div class="alert alert-danger" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
            Fichier trop important (taille maximale : 500 MB).
         </div> <br>
         <?php
         $check = FALSE;
      }
      //Test3: extension
      $ext = substr(strrchr($_FILES[$index]['name'],'.'),1);
      if ($extensions !== FALSE AND !in_array($ext,$extensions))
      {
         ?>
         <div class="alert alert-danger" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
            Mauvaise extension de fichier (seuls les fichier .csv sont acceptés).
         </div> <br>
         <?php
         $check = FALSE;
      }

      $success = move_uploaded_file($_FILES[$index]['tmp_name'], '../../check/'.$nomFichier);
      if ($success)
      {
         // Faire ici la VERIFICATION (algorithme de Mikael)

         // if verification_mikael() + $bool
         if ($check)
         {
            rename('../../check/'.$nomFichier, $destination); // on déplace le fichier vérifé au bon endroit
            ?>
            <div class="alert alert-success" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
               Fichier accepté par la vérification !
            </div> <br>
            <?php
         }
      } else {
         $check = FALSE;
      }

      return $check;

      // } else {
      //    $taille = filesize('log.txt'):
      //    header('Content-Transfer-Encoding: binary'); // Transfert en binaire (fichier)
      //    header('Content-Disposition: attachment; filename="log.txt"'); // Nom du fichier
      //    header('Content-Length: '.$taille); // Taille du fichier
      //    readfile("errors/log.txt"); // Envoi du fichier dont le chemin est passé en paramètre
      //    echo 'Erreurs survenues (checkez le fichier log.txt)';
      //    # code...
      // }
   }
   ?>
   <center>
      <h2> Confirmation de l'upload </h2>
      <?php

      $fichier = htmlspecialchars($_FILES['fichier_importe']['name']);

      $path_parts = pathinfo($fichier);
      $fichier = $path_parts['filename'].'_'.time().'.'.$path_parts['extension'];

      $fichier = strtr($fichier,
      'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ',// On remplace les lettres accentutées par les non accentuées dans $fichier.
      'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');// Et on récupère le résultat dans fichier

      //En dessous, il y a l'expression régulière qui remplace tout ce qui n'est pas une lettre non accentuées ou un chiffre
      //dans $fichier par un tiret "-" et qui place le résultat dans $fichier.
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
         $upload_possible = upload('fichier_importe','../../uploads/' . $true_directory . '/' . $fichier , 524288000, array('png','csv','jpg','jpeg'), $fichier );
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
