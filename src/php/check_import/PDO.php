<?php
function connexion()
{
   try
   {
      $bdd = new PDO('pgsql:host=localhost;dbname=postgres', 'postgres', 'saphiron');
   	//$bdd = new PDO('mysql:host=129.20.88.134;dbname=stage_vegfrance;charset=utf8', 'stage_vegfrance', '0905egats!');
   	$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	echo "connexion réussie";
      return $bdd;

   }
   catch(Exception $e)
   {
   	// En cas d'erreur, on affiche un message et on arrête tout
      die('Erreur : '.$e->getMessage());
   }
}
?>
