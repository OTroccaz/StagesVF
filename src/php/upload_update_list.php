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
   include('check_import/list_parameters.php');

   $fichier = htmlspecialchars($_FILES['fichier_importe']['name']);

   if (!is_dir("../../List_CSV/")) mkdir('../../List_CSV/', 0777, true);

   function upload($index,$destination,$maxsize=FALSE,$extensions=FALSE)
   {
      //Test1: fichier correctement uploadé
      if (!isset($_FILES[$index]) OR $_FILES[$index]['error'] > 0) return FALSE;
      //Test2: taille limite
      if ($maxsize !== FALSE AND $_FILES[$index]['size'] > $maxsize) return FALSE;
      //Test3: extension
      $ext = substr(strrchr($_FILES[$index]['name'],'.'),1);
      if ($extensions !== FALSE AND !in_array($ext,$extensions)) return FALSE;
      //Déplacement
      return move_uploaded_file($_FILES[$index]['tmp_name'],$destination);
   }

   $upload = upload('fichier_importe','../../List_CSV/'.$fichier,524288000, array('csv'));
   if ($upload)
   {
      ?>
      <div class="alert alert-succes" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
         Upload du fichier <?php echo htmlspecialchars($fichier); ?> réussi !
      </div>
      <?php
      $param = new list_parameters();
      $param->updateList($fichier, $bdd);
   } else {
      ?>
      <div class="alert alert-danger" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
         Upload du fichier <?php echo htmlspecialchars($fichier); ?> raté...
      </div>
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
