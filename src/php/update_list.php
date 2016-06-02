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
   if ($_SESSION['status'] == 'Administrateur')
   {
      /* CONNEXION BDD */
      include ('../../config/connection.php');
      $bdd = connexionPgSQL();
      /* CONNEXION FAITE */
      include('../html/update_list.htm');
      include('check_import/list_parameters.php');

      if (isset($_POST['maj']))
      {
         $param = new list_parameters();
         $param->updateListAll($bdd);
      }

   } else {
      ?>
      <head>
         <meta charset="utf-8" />
         <title>Modération</title>
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
