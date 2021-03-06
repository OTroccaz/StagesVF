<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
   <title>Consultation</title>
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
               <li class="active"><a href="exit_mapVF.php">Consultation</a></li>
               <?php
               if(isset($_SESSION['pseudo'])) // Si on est connecté
               { ?>
                  <li><a href="../php/myaccount.php">Mon compte</a></li>
                  <?php
               }
               if(isset($_SESSION['status']))
               {
                  if($_SESSION['status']!="Administrateur" && $_SESSION['status']!="Gestionnaire") // Si on est connecté et qu'on est pas admin ou gestionnaire
                  {?>
                     <li><a href="../php/requests.php">Mes demandes d'exportation</a></li>
                     <?php
                  }
               }
               if(isset($_SESSION['status']))
               {
                  if($_SESSION['status']!="Utilisateur" && $_SESSION['status']!="Fournisseur") // Si on est connecté et qu'on est admin ou gestionnaire
                  {
                     ?>
                     <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Privilèges<span class="caret"></span></a>
                        <ul class="dropdown-menu">
                           <?php
                           if($_SESSION['status']=="Administrateur")
                           {
                              ?>
                              <li><a href="../php/moderation.php">Modération</a></li>
                              <li><a href="../php/update_list.php">Modification listes</a></li>
                              <?php
                           } ?>
                           <li><a href="../php/authorizations_management.php">Autorisations</a></li>
                        </ul>
                     </li>
                     <?php
                  }
               }
               ?>
            </ul>
            <?php
            if (isset($_SESSION['status'])) // Si on est connecté
            {?>
               <ul class="nav navbar-nav navbar-right">
                  <li><a href="../php/disconnect.php"><span class="glyphicon glyphicon-off"></span> Se déconnecter</a></li>
               </ul>
               <?php
            }?>
         </div><!--/.nav-collapse -->
      </div>
   </div>
   <div style="margin:0 auto; width:500px">
      <center>
         <div>
            <h2>Consultation des données</h2>
         </div>
         <br><p><b>Attention</b>, en voulant consulter les données, vous allez <b>quitter ce site</b><br>pour rejoindre le site initialement créé de VegFrance.<br><b>Voulez-vous continuer ?</b></p><br><br>
         <a href="https://ecobio-mapserver.univ-rennes1.fr/GS/mapVF_index.php?troli=ok"><button type="submit" class="btn btn-md btn-primary">Oui</button></a>
         <a href="../../index.php"><button type="submit" class="btn btn-md btn-secondary">Non</button></a>
      </center>
   </div>
</body>
</html>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="../../js/bootstrap.min.js"></script>
