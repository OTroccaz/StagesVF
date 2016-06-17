<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
   <title>Accueil VegFrance</title>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
   <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
         <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
               <span class="sr-only">Toggle navigation</span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php">Accueil</a>
         </div>
         <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
               <li><a href="src/php/sign_in.php">Identification</a></li>
               <li><a href="src/php/import.php">Importation</a></li>
               <li><a href="src/php/management.php">Gestion</a></li>
               <li><a href="src/php/exit_mapVF.php">Consultation</a></li>
               <?php
               if(isset($_SESSION['pseudo']))
               { ?>
                  <li><a href="src/php/myaccount.php">Mon compte</a></li>
                  <?php
               }
               if(isset($_SESSION['status']))
               {
                  if($_SESSION['status']!="Administrateur" && $_SESSION['status']!="Gestionnaire")
                  {?>
                     <li><a href="src/php/requests.php">Mes demandes d'exportation</a></li>
                     <?php
                  }
               }
               if(isset($_SESSION['status']))
               {
                  if($_SESSION['status']!="Administrateur" || $_SESSION['status']!="Gestionnaire")
                  {
                     ?>
                     <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Privilèges<span class="caret"></span></a>
                        <ul class="dropdown-menu">
                           <?php
                           if($_SESSION['status']=="Administrateur")
                           {
                              ?>
                              <li><a href="src/php/moderation.php">Modération</a></li>
                              <li><a href="src/php/update_list.php">Modification listes</a></li>
                              <?php
                           } ?>
                           <li><a href="src/php/authorizations_management.php">Autorisations</a></li>
                        </ul>
                     </li>
                     <?php
                  }
               }
               ?>
            </ul>
            <?php
            if (isset($_SESSION['status']))
            {?>
               <ul class="nav navbar-nav navbar-right">
                  <li><a href="src/php/disconnect.php"><span class="glyphicon glyphicon-off"></span> Se déconnecter</a></li>
               </ul>
               <?php
            }?>
         </div><!--/.nav-collapse -->
      </div><!-- /.container-fluid -->
   </nav>
   <!-- Texte de présentation -->
   <img src="images/vegfrance.png" class="img-responsive" alt="vegFrance" style="display:block;margin:0 auto;width:600px">
   <br>
   <div style="text-align:justify;margin:auto;width:600px">VegFrance est une plateforme nationale publique conçue de façon à pouvoir réunir toutes données disponibles pour décrire la végétation du territoire français (métropolitain et d’outre-mer). VegFrance est constitué de trois bases de données afin de couvrir la diversité de relevés de végétation :
      <br>
      <ol class="bbcode 1" style="list-style-type: 1">
         <li class="bbcode 1"> la base de données « Relevé » recense des relevés stationnels ;<br></li>
         <li class="bbcode 1">	la base « Syntaxon » compile des relevés synthétiques, à l’échelle des communautés végétales ;<br></li>
         <li class="bbcode 1">	la base « Landscape » enfin regroupe des relevés à l’échelle du paysage.<br></li>
      </ol>L’objectif principal est de répondre aux besoins de connaissances sur la végétation pour les acteurs de la recherche, de la protection et de la gestion des habitats et des espèces et de l’aménagement du territoire.<br>
   </br><b>L'usage de ce site est consacré à l'accès à la base de données de VegFrance. Pour plus d'informations sur le projet en lui-même, consultez le site de <a href="https://vegfrance.univ-rennes1.fr/">VegFrance</a>.</b>
</div>
</body>
</html>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
