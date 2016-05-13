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
?>

   <!DOCTYPE html>
   <html>
   <head>
      <meta charset="utf-8" />
      <title>Stage - Tests</title>
   </head>

   <body>
      <?php include("corps.htm"); ?>
   </body>
   </html>
<?php
} else {
   echo '<p>Vous n\'êtes pas autorisé(e) à acceder à cette zone</p>';
   include('login.htm');
   exit;
}
?>
