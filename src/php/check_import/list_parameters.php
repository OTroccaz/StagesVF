<?php

class list_parameters{

  public function insertionListParam ($tableau, $bdd){

    $sql = $bdd->query("SELECT list_name FROM list_names");
    $champsList = $sql->fetchAll(PDO::FETCH_COLUMN);

    $sql = $bdd->query("SELECT id_name FROM list_names");
    $champsListCorres = $sql->fetchAll(PDO::FETCH_COLUMN);

    for($champs = 0 ; $champs < count($champsList) ; $champs++){


      $sqlInsert = "INSERT INTO list_parameters (id_vegfr, id_liste_name, id_value) VALUES (?,?,?)";


        for($row = 0 ; $row < count($tableau) ; $row++){

          $sql = $bdd->query("SELECT id_vegfr FROM survey WHERE name_releve = '".$tableau[$row]["NAME_RELEVE"]."'");
          $idVegfr = $sql->fetchAll(PDO::FETCH_COLUMN);

          if($tableau[$row][$champsList[$champs]] != NULL){

            $query=$bdd->prepare($sqlInsert);
            $query->execute(array(
              $idVegfr[0],
              $champsListCorres[$champs],
              $tableau[$row][$champsList[$champs]],
            ));
          }

      }

  }

  }

  public function verifList($tableau, $bdd){
    $log = new log_error();
    $error = true;
    $sql = $bdd->query("SELECT list_name FROM list_names");
    $champsList = $sql->fetchAll(PDO::FETCH_COLUMN);

    for($champs = 0 ; $champs < count($champsList) ; $champs++){
      for($row = 0; $row < count($tableau) ; $row++){
        if(is_numeric($tableau[$row][$champsList[$champs]]) || $tableau[$row][$champsList[$champs]] == NULL){
        }
        else{
            $log->writeLog("ERREUR, LA DONNEE N'EST PAS UN INT , LIGNE : ".$row." / COLONNE : ".$champsList[$champs]);
          $error = false;
        }
      }
    }
    return $error;
  }


public function updateList($listName){
  $list = array(array());
  $row = 0;

  if (($handle = fopen($listName, "r")) !== FALSE) {
    $nbr_lignes = count(file($listName));

    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
      $nbr_champs = count($data);

      if($row < 1){
        for($i = 0 ; $i < $nbr_champs ; $i++){
          $data[$c] = str_replace("'", chr(39), $data[$c]);
          $tableau[$line][$tabName[$c]] = $data[$c];

        }
      }
      $row++;

    }
    fclose($handle);
  }
  return $tabName;
  $sql = $bdd->query("UPDATE ".$listName."");

}



}

?>
