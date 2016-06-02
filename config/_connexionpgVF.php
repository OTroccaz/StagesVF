<?php
header("Content-type: text/html; charset=UTF-8");
//Connexion à la bdd postgres
$link = pg_connect("host=129.20.88.134 port=5432 dbname=vegfrance user=mapVF password=0206pam?");
if (!$link) {
   die ("connection impossible".pg_result_error($link));
}
?>
