<?php
$fichier = 'check_import/log.txt';
$fichier_taille = filesize($fichier);
header("Content-disposition: attachment; filename=erreurs_importation_vegfrance.txt");
header("Content-Type: application/force-download");
header("Content-Transfer-Encoding: application/octet-stream");
header("Content-Length: $fichier_taille");
header("Pragma: no-cache");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
header("Expires: 0");
readfile($fichier);
?>
