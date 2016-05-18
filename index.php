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
   <link rel="icon" href="../../favicon.ico">
   <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
   <!-- Custom styles for this template -->
   <link href="starter-template.css" rel="stylesheet">
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
   <!-- Texte de présentation -->
   <img src="http://www.imbe.fr/docrestreint.api/1929/81574d7e0509c6cb6177bb6c6d98eb2b8265089b/jpg/vegfrance.jpg" alt="Mountain View" style="display:block;margin:0 auto;width:600px">
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
