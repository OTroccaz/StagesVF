<?php

//Classe permettant d'écrire dans le fichier log les erreurs de la vérification

class log_error{
  
  //Cette fonction permet d'écrire dans le fichier log
  
  public function writeLog($log){

  $fp = fopen ("check_import\log.txt", "r+");
  $contenu_du_fichier = $log;
  $contenu_du_fichier .= "\n";
  fseek ($fp, SEEK_END);
  fwrite ($fp, $contenu_du_fichier);
  fclose ($fp);

  }
  //Cette fonction permet de vider le fichier log
  public function resetLog(){
    $fp = fopen ("check_import\log.txt", "r+");
    $contenu_du_fichier = "";
    ftruncate($fp, 0);
    fclose ($fp);
  }

}



 ?>
