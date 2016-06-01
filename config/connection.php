<?php
include('global.php');

function connexionMySQL()
{
   try
   {
   	// On se connecte à MySQL
   	$bdd = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER_MYSQL, DB_PASSWORD_MYSQL);
   	$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $bdd;
   }
   catch(Exception $e)
   {
   	// En cas d'erreur, on affiche un message et on arrète tout
      die('Erreur : '.$e->getMessage());
   }
}

function connexionPgSQL()
{
   try
   {
   	// On se connecte à PgSQL
   	$bdd = new PDO('pgsql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER_PGSQL, DB_PASSWORD_PGSQL);
   	$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $bdd;
   }
   catch(Exception $e)
   {
   	// En cas d'erreur, on affiche un message et on arrète tout
      die('Erreur : '.$e->getMessage());
   }
}
?>
