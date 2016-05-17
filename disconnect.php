<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
   <title>Déconnexion</title>
   <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
   <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
         <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            </button>
            <a class="navbar-brand" href="index.php">Accueil</a>
         </div>
         <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
               <li><a href="sign_in.php">Identification</a></li>
               <li><a href="import.php">Importation de données</a></li>
               <li><a href="export.php">Exportation de données</a></li>
               <?php
               if(isset($_SESSION['status'])) {
                  if($_SESSION['status']=="Administrateur")
                  { ?>
                     <li><a href="moderation.php">Modération</a></li>
                     <?php
                  }
               }
               ?>
            </ul>
         </div><!--/.nav-collapse -->
      </div>
   </div>
   <p style="text-align:center;margin-top:10%">Vous êtes à présent déconnecté <br />
      Cliquez <a href="index.php">ici</a> pour revenir à la page principale</p>
      <?php
      header("refresh:2; url=index.php");
      ?>
</body>
</html>
