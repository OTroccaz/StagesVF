<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
   <title>Déconnexion</title>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <link rel="icon" href="../../favicon.ico">
   <link href="../../css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
   <div class="navbar navbar-default navbar-fixed-top">
      <div class="container">
         <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
               <span class="sr-only">Toggle navigation</span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="../../index.php">Accueil</a>
         </div>
         <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
               <li><a href="sign_in.php">Identification</a></li>
               <li><a href="import.php">Importation</a></li>
               <li><a href="management.php">Gestion</a></li>
               <li><a href="exit_mapVF.php">Consultation</a></li>
            </ul>
         </div><!--/.nav-collapse -->
      </div>
   </div>
   <p style="text-align:center;margin-top:10%">Vous êtes à présent déconnecté(e) <br />
      Cliquez <a href="sign_in.php">ici</a> pour revenir à la page de connexion</p>
      <?php
      header("refresh:2; url=sign_in.php");
      ?>
   </body>
   </html>
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
   <script src="../../js/bootstrap.min.js"></script>
