<?php
$fichier = '../../doc/20160526_Tableau_insertion_relevés.xlsx';
$fichier_taille = filesize($fichier);
header("Content-disposition: attachment; filename=Tableau d'insertion.xlsx");
header("Content-Type: application/force-download");
header('Content-Transfer-Encoding: binary');
header("Content-Length: $fichier_taille");
header("Pragma: no-cache");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
header("Expires: 0");
readfile($fichier);
?>
