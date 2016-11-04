<?php

//classe contenant les fonctions de vérification et d'insertion pour les champs facultatif de type varchar

class varchar_parameters{


	// Cette fonction permet d'inserer les données ayant passé la vérification dans la BDD

  public function insertionVarcharParam ($tableau, $bdd){

    $sql = $bdd->query("SELECT label FROM list_varchar");
    $champsVarchar = $sql->fetchAll(PDO::FETCH_COLUMN);

    $sql = $bdd->query("SELECT id_parameter FROM list_varchar");
    $champsVarcharCorres = $sql->fetchAll(PDO::FETCH_COLUMN);

    for($champs = 0 ; $champs < count($champsVarchar) ; $champs++){


      $sqlInsert = "INSERT INTO varchar_type (id_vegfr, id_parameter, value) VALUES (?,?,?)";


      for($row = 0 ; $row < count($tableau) ; $row++){


        $sql = $bdd->query("SELECT id_vegfr FROM survey WHERE name_releve = '".$tableau[$row]["NAME_RELEVE"]."'");
        $idVegfr = $sql->fetchAll(PDO::FETCH_COLUMN);

        if(  $tableau[$row][$champsVarchar[$champs]] != NULL){

          $query=$bdd->prepare($sqlInsert);
          $query->execute(array(
            $idVegfr[0],
            $champsVarcharCorres[$champs],
            $tableau[$row][$champsVarchar[$champs]],
          ));
        }
    }
  }
}


  // Cette fonction permet de vérifier que toutes les données sont bien des varchar

  public function verifVarchar($tableau, $bdd){
    $log = new log_error();
    $error = true;
    $sql = $bdd->query("SELECT label FROM list_varchar");
    $champsVarchar = $sql->fetchAll(PDO::FETCH_COLUMN);

    for($champs = 0 ; $champs < count($champsVarchar) ; $champs++){
      for($row = 0; $row < count($tableau) ; $row++){
        if(is_string($tableau[$row][$champsVarchar[$champs]]) || $tableau[$row][$champsVarchar[$champs]] == NULL){
        }
        else{
          $log->writeLog( "ERREUR, LA DONNEE N'EST PAS UN STRING , LIGNE : ".$row." / COLONNE : ".$champsVarchar[$champs]);
          $error = false;
        }
      }
    }
    return $error;
  }

}

?>
