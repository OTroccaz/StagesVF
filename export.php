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
   include ('connexion.php');
   $bdd = connexionMySQL();
   /* CONNEXION FAITE */
   ?>
   <!DOCTYPE html>
   <html>
   <head>
      <meta charset="utf-8" />
      <title>Téléchargements</title>
      <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
   </head>
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
               <li class="active"><a href="export.php">Exportation de données</a></li>
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
   <button type="button" style="margin-left:5%" onclick="location.href='disconnect.php'" class="btn btn-secondary">Se déconnecter</button>
   <div style="text-align:justify; margin-left:20%; margin-right:20%">
      <center>
         <h2> Téléchargement des fichiers importés </h2>
         <h5> Cliquez sur les boutons numérotés afin de récupérer le fichier correspondant </h5>
      </center>
   <?php
   // On récupère toute la base de données dans un tableau
   $reponse = $bdd->query('SELECT * FROM files');

   // On parcourt chaque ligne de ce tableau, ligne = $donnees
   while ($donnees = $reponse->fetch())
   {
   ?>
      <ul>
         <?php echo "Description : TYPE DE DONNEES = ".htmlspecialchars($donnees['up_type']) ." || NOM = ".htmlspecialchars($donnees['up_filename'])." || DATE = ".$donnees['up_filedate']; ?>
   		<form method="get" action="filedownload.php">
   			<i>Télécharger le fichier n°</i>
            <input type="submit" value="<?php echo $donnees['up_id']?>" name="file">
         </form>
      </ul>
   <?php
   }
   ?>
   </div>
   </html>
   <?php
} else {
   echo '<p style="text-align:center">Vous n\'êtes pas autorisé(e) à acceder à cette zone</p>';
   ?>
   <head>
      <meta charset="utf-8" />
      <title>Connexion VegFrance</title>
   </head>
   <?php
   include('sign_in.htm');
   exit;
}
?>
