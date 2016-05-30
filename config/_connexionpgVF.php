<?php
header("Content-type: text/html; charset=UTF-8");
//Connexion à la bdd postgres
$link = pg_connect("host=localhost port=5432 dbname=vegfrance user=vegfrance password=2909vf!");
if (!$link) {
   die ("connection impossible".pg_result_error($link));
}
?>
