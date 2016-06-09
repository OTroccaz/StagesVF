<?php

class log_error{
  
  public function writeLog($log){

  $fp = fopen ("check_import\log.txt", "r+");
  $contenu_du_fichier = $log;
  $contenu_du_fichier .= "\n";
  fseek ($fp, SEEK_END);
  fputs ($fp, $contenu_du_fichier);
  fclose ($fp);

  }

  public function resetLog(){
    $fp = fopen ("check_import\log.txt", "r+");
    $contenu_du_fichier = "";
    ftruncate($fp, 0);
    fclose ($fp);
  }

}



 ?>
