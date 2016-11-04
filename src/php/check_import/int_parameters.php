<?php

//classe contenant les fonctions de vérification et d'insertion pour les champs facultatif de type integer

class int_parameters{
	
	// Cette fonction permet d'inserer les données ayant passé la vérification dans la BDD

  public function insertionIntParam ($tableau, $bdd){

    $sql = $bdd->query("SELECT label FROM list_int");
    $champsInt = $sql->fetchAll(PDO::FETCH_COLUMN);

    $sql = $bdd->query("SELECT id_parameter FROM list_int");
    $champsIntCorres = $sql->fetchAll(PDO::FETCH_COLUMN);

	$nbrInt = count($champsInt);
	$nbrTab = count($tableau);
	
    for($champs = 0 ; $champs < $nbrInt ; $champs++){

      $sqlInsert = "INSERT INTO int_type (id_vegfr, id_parameter, value) VALUES (?,?,?)";

      for($row = 0 ; $row < $nbrTab ; $row++){

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

  // Cette fonction permet de vérifier que toutes les données sont bien des integer

  public function verifInt($tableau, $bdd){
    $log = new log_error();
    $error = true;
    $sql = $bdd->query("SELECT label FROM list_int");
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
