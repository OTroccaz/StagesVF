<?php

class vegetation{

  public function vegetation(){
    include('log_error.php');

  }
  public function initialisationVegetationAll($chemin, $bdd){
    $reponse = false;
    $tabName = $this->getTabNameVegetation($chemin);
    $tableau = $this->initVegetation($chemin, $tabName);
    $tableau = $this->correspondanceVegetation($tableau, $bdd);
    $verif = $this->verificationSurvey($tableau, $bdd);
    if($verif){
    		$reponse = $survey->insertionSurveyAll($tableau, $bdd);
    }

    return $reponse;
  }

  public function getTabNameVegetation($nameVegetation){
    $tabName = array();
    $row = 0;

    if (($handle = fopen($nameVegetation, "r")) !== FALSE) {
      $nbr_lignes = count(file($nameVegetation));

      while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $nbr_champs = count($data);

        if($row == 0){
          for($i = 0 ; $i < $nbr_champs ; $i++){
            $tabName[$i] = $data[$i];
          }
        }
        $row++;

      }
      fclose($handle);
    }
      return $tabName;
  }
  public function initVegetation($nameVegetation, $tabName){
    $log = new log_error();
    $log->resetLog();
    $row = 0;
    $nbr_champs = 0;
    $tableau = array(array());



    if (($handle = fopen($nameVegetation, "r")) !== FALSE) {
      $nbr_lignes = count(file($nameVegetation));



      while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $nbr_champs = count($data);

      if($row >= 1){
        for ($c=0; $c < $nbr_champs; $c++) {
          $tableau[$row-1][$tabName[$c]] = $data[$c];

        }
      }
        $row++;
      }

    }
    return $tableau;
    }

  public function correspondanceVegetation($tableau, $bdd){
  		for($row = 0 ; $row < count($tableau) ; $row++){
  			if($tableau[$row]["STRATUM"] != NULL){
  				 $dataStratum = $this->corresStratum($tableau[$row]["STRATUM"], $bdd);
  				 $tableau[$row]["STRATUM"] = $dataStratum;
  			}
        $tableau[$row]["NAME"] = $tableau[$row]["SP_NAME"];
        if($tableau[$row]["SP_NAME"] != NULL){
           $dataSpName = $this->corresSpecies($tableau[$row]["SP_NAME"], $bdd);
           $tableau[$row]["SP_NAME"] = $dataSpName;
        }
  		}
      return $tableau;
  }

  public function corresStratum($data, $bdd){


    $sql = $bdd->query("SELECT id_stratum FROM stratum WHERE stratum_name='".$data."'");
    $valeur = $sql->fetch();

    return $valeur[0];
  }

  public function corresSpecies($data, $bdd){


    $sql = $bdd->query("SELECT id_species FROM TAXREF_5 WHERE cd_nom='".$data."'");
    $valeur = $sql->fetch();

    return $valeur[0];
  }



  public function verificationVegetation ($tableau, $bdd){
      $error = true;
      $errorType = $this->verifVegetationType($tableau);
      $errorObligatoire = $this->verifVegetationObligatoire($tableau);
      $errorUnique = $this->verifIdUniqueVegetation($tableau, $bdd);
      if($errorType == false || $errorObligatoire == false || $errorUnique == false){
        $error = false;
      }
      return $error;
	}

  public function verifVegetationType($tableau){
    $log = new log_error();
    $error = true;
    for($row = 0 ; $row < count($tableau) ; $row++){
      if(!(is_string($tableau[$row]["NAME_RELEVE"]))){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN STRING , LIGNE : ".$row." / COLONNE : NAME_RELEVE";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_numeric($tableau[$row]["SP_NAME"]))){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN INT , LIGNE : ".$row." / COLONNE : DATE_S";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_numeric($tableau[$row]["STRATUM"]))){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN INT , LIGNE : ".$row." / COLONNE : COMPLETE";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_string($tableau[$row]["COVER"]))){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN STRING , LIGNE : ".$row." / COLONNE : COMPLETE";
        $log->writeLog($logError);
        $error = false;
      }

  }
  return $error;
  }

  public function verifVegetationObligatoire($tableau){
	$log = new log_error();
    $error = true;
    for($row = 0 ; $row < count($tableau) ; $row++){
      if($tableau[$row]["NAME_RELEVE"] == NULL){
				$logError = "ERREUR, VALEUR OBLIGATOIRE MANQUANTE , LIGNE : ".$row." / COLONNE : NAME_RELEVE";
				$log->writeLog($logError);
        $error = false;
      }
      if($tableau[$row]["SP_NAME"] == NULL){
				$logError = "ERREUR, VALEUR OBLIGATOIRE MANQUANTE , LIGNE : ".$row." / COLONNE : SP_NAME";
				$log->writeLog($logError);
        $error = false;
      }
      if($tableau[$row]["COVER"] == NULL){
				$logError = "ERREUR, VALEUR OBLIGATOIRE MANQUANTE , LIGNE : ".$row." / COLONNE : COVER";
				$log->writeLog($logError);
        $error = false;
      }


    }
    return $error;
  }

  public function verifIdUniqueVegetation($tableau, $bdd){
    $log = new log_error();
    $error = true;
    $idPresent = false;
    $sql = $bdd->query("SELECT name_releve FROM survey");
    $survey = $sql->fetchAll(PDO::FETCH_COLUMN);

    for($row = 0 ; $row < count($tableau) ; $row++ ){
      $idPresent = false;
      for($i = 0 ; $i < count($survey) ; $i++){
        echo $tableau[$row]["NAME_RELEVE"];
        echo "    &&    ";
        echo $survey[$i];
        echo "     ||     ";
        if($tableau[$row]["NAME_RELEVE"] == $survey[$i]){
          $idPresent = true;
        }
      }
      if($idPresent == false){
          $log->writeLog("ERREUR , L'IDENTIFIANT NAME_RELEVE: ".$tableau[$row]["NAME_RELEVE"]." N'EXISTE PAS DANS LA BASE DE DONNEES");
          $error = false;
      }
    }

    return $error;


  }

  public function insertionVegetation($tableau, $bdd){
    $verif = false;
      $log = new log_error();
      $sqlInsert = "INSERT INTO vegetation (id_vegfr, id_species, id_stratum, cover, species_name) VALUES (?,?,?,?,?)";

      $bdd->beginTransaction();
      try
      {
        for($row = 0 ; $row < count($tableau) ; $row++){

          $sql = $bdd->query("SELECT id_vegfr FROM survey WHERE name_releve = '".$tableau[$row]["NAME_RELEVE"]."'");
          $idVegfr = $sql->fetchAll(PDO::FETCH_COLUMN);

          $query=$bdd->prepare($sqlInsert);

          $query->execute(array(
            $idVegfr[0],
            $tableau[$row]["SP_NAME"],
            $tableau[$row]["STRATUM"],
            $tableau[$row]["COVER"],
            $tableau[$row]["NAME"],
          ));


      }
        $bdd->commit();
        $verif = true;;
      }
      catch ( Exception $e )
      {
        $bdd -> rollBack ();
        $log->writeLog($e->getMessage());
        $log->writeLog("INSERTION NON EFFECTUE");
      }
      return $verif;
  }

  public function deleteVegetation($bdd, $surveyName){


    $sql = $bdd->query("SELECT id_vegfr FROM survey WHERE name_releve='".$surveyName."'");
    $idVegFr = $sql->fetchAll(PDO::FETCH_COLUMN);


    $bdd->query("DELETE FROM vegetation WHERE id_vegfr='".$idVegFr[0]."'");


  }



}

 ?>
