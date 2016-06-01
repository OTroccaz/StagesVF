<?php

class int_parameters{


  public function insertionIntParam ($tableau, $bdd){

    $sql = $bdd->query("SELECT name_parameter FROM int_parameters");
    $champsInt = $sql->fetchAll(PDO::FETCH_COLUMN);

    $sql = $bdd->query("SELECT id_parameter FROM int_parameters");
    $champsIntCorres = $sql->fetchAll(PDO::FETCH_COLUMN);


    for($champs = 0 ; $champs < count($champsInt) ; $champs++){

      $sqlInsert = "INSERT INTO int_type (id_vegfr, id_parameter, value) VALUES (?,?,?)";

      for($row = 0 ; $row < count($tableau) ; $row++){

        $sql = $bdd->query("SELECT id_vegfr FROM survey WHERE name_releve = '".$tableau[$row]["NAME_RELEVE"]."'");
        $idVegfr = $sql->fetchAll(PDO::FETCH_COLUMN);

        if($tableau[$row][$champsInt[$champs]] != NULL){

          $query=$bdd->prepare($sqlInsert);
          $query->execute(array(
            $idVegfr[0],
            $champsIntCorres[$champs],
            $tableau[$row][$champsInt[$champs]],
          ));
        }
    }
  }
  }


  public function verifInt($tableau, $bdd){
    $log = new log_error();
    $error = true;
    $sql = $bdd->query("SELECT name_parameter FROM int_parameters");
    $champsInt = $sql->fetchAll(PDO::FETCH_COLUMN);

    for($champs = 0 ; $champs < count($champsInt) ; $champs++){
      for($row = 0; $row < count($tableau) ; $row++){
        if(is_numeric($tableau[$row][$champsInt[$champs]]) || $tableau[$row][$champsInt[$champs]] == NULL){
        }
        else{
            $log->writeLog("ERREUR, LA DONNEE N'EST PAS UN INT , LIGNE : ".$row." / COLONNE : ".$champsInt[$champs]);
          $error = false;
        }
      }
    }
    return $error;
  }


}
?>
