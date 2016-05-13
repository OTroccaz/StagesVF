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

   <html>
   <div>
      <a href="disconnect.php">Se déconnecter</a>
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
         <?php echo "Description : ID = " . $donnees['up_id']. " <b>||</b> TYPE DE DONNEES = ".$donnees['up_type'] ." <b>||</b> NOM = ".$donnees['up_filename']." <b>||</b> DATE = ".$donnees['up_filedate']; ?>
   		<form method="get" action="filedownload.php">
   			<i>Télécharger le fichier n°</i>
            <input type="submit" value="<?php echo $donnees['up_id']?>" name="file">
         </form>
      </ul>
   <?php
   }
   ?>
   <form action="import.php">
      <input type="submit" value="Retour">
   </form>
   </div>
   </html>
   <?php
} else {
   echo '<p>Vous n\'êtes pas autorisé(e) à acceder à cette zone</p>';
   include('login.htm');
   exit;
}
?>
