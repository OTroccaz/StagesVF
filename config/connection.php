<?php
include('global.php');

function connexionMySQL()
{
   try
   {
   	// On se connecte à MySQL
      //$bdd = new PDO('mysql:host='.DB_LOCALHOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASSWORD);
   	$bdd = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASSWORD);
   	$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $bdd;
   }
   catch(Exception $e)
   {
   	// En cas d'erreur, on affiche un message et on arrête tout
      die('Erreur : '.$e->getMessage());
   }
}

function connexionPgSQL()
{
   try
   {
   	// On se connecte à PgSQL
      //$bdd = new PDO('pgsql:host='.DB_LOCALHOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASSWORD);
   	$bdd = new PDO('pgsql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASSWORD);
   	$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $bdd;
   }
   catch(Exception $e)
   {
   	// En cas d'erreur, on affiche un message et on arrête tout
      die('Erreur : '.$e->getMessage());
   }
}
?>
