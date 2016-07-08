<?php

class vegetation{

  public function vegetation(){
    include('log_error.php');

  }
  public function initialisationVegetationAll($chemin, $bdd, $Nbr_row){
	 
	if($Nbr_row == 499){

		$chemin = substr($chemin, 12);
		$chemin = "../../../uploads/vegetation/".$chemin;
		$chemin = trim($chemin);
	}	 
	$nbrLines = $this->getNbrLines($chemin);
    $verif = false;
    $reponse = true;
    $tabName = $this->getTabNameVegetation($chemin);
    $tableau = $this->initVegetation($chemin, $tabName);
    $tableau = $this->correspondanceVegetation($tableau, $bdd);
	$tableau = $this->corresintNull($tableau);
    $verif = $this->verificationVegetation($tableau, $bdd);
    if($verif){
    		$inc = $this->insertionVegetation($tableau, $bdd);
		if($Nbr_row + $inc > $nbrLines){
			$Nbr_row = $nbrLines;
		}
		else{
			$Nbr_row = $Nbr_row + $inc;
		}
			
		echo $Nbr_row;
		echo " / ";
		echo $nbrLines;

		if($Nbr_row < $nbrLines){
			header( "Refresh:1; url=https://vegfrance.univ-rennes1.fr/StagesVF/src/php/check_import/lancementVegetation.php?row=".$Nbr_row."&chemin=".$chemin, true);

		}
		else{
			echo " L'insertion est terminé ! Vous allez être redirigé vers la page d'importation.";
			header( "Refresh:5; url=https://vegfrance.univ-rennes1.fr/StagesVF/src/php/fileupload.php?verif=1", true);
		}
    }
	else{
		$reponse = false;
		echo " L'insertion a rencontrée une erreur ! Vous allez être redirigé vers la page d'importation.";
		header( "Refresh:5; url=https://vegfrance.univ-rennes1.fr/StagesVF/src/php/fileupload.php?verif=0", true);
	}
    return $reponse;
 }

  public function getTabNameVegetation($nameVegetation){
    $tabName = array();
    $row = 0;

    if (($handle = fopen($nameVegetation, "r")) !== FALSE) {
      $nbr_lignes = count(file($nameVegetation));

      while (($data = fgetcsv($handle, 300, ";")) !== FALSE) {
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
  public function initVegetation($nameVegetation, $tabName, $row){
    $log = new log_error();
    $row = 0;
    $nbr_champs = 0;
    $tableau = array(array());
	$increment = 0;
	$line = 0;

    if (($handle = fopen($nameVegetation, "r")) !== FALSE) {
      $nbr_lignes = count(file($nameVegetation));
		$rowLow = $row + 1;
		if($nbr_lignes >= ($row + 501)){
			$rowHigh = $row + 501;
		}
		else{
			$rowHigh += ($nbr_champs - $row); 
		}


      while (($data = fgetcsv($handle, 500, ";")) !== FALSE) {
        $nbr_champs = count($data);

      if($line > 1 && $line >= $rowLow && $line <= $rowHigh){
        for ($c=0; $c < $nbr_champs; $c++) {
          $tableau[$increment][$tabName[$c]] = $data[$c];

        }
		$increment++;
      }
        $line++;
    }
	fclose($handle);
    }
    return $tableau;
    }
	
  public function correspondanceVegetation($tableau, $bdd){
  		for($row = 0 ; $row < count($tableau) ; $row++){
  			if($tableau[$row]["STRATUM"] != NULL){
  				 $dataStratum = $this->corresStratum($tableau[$row]["STRATUM"], $bdd);
  				 $tableau[$row]["STRATUM"] = $dataStratum;
  			}

  		}
    return $tableau;
  }

  public function corresStratum($data, $bdd){


    $sql = $bdd->query("SELECT id_stratum FROM list_stratum WHERE label='".$data."'");
    $valeur = $sql->fetch();

    return $valeur[0];
  }

public function corresintNull($tableau){
	for($row = 0 ; $row < count($tableau) ; $row++){
		if($tableau[$row]["STRATUM"] == NULL){
			$tableau[$row]["STRATUM"] = NULL;
		}
	}
	return $tableau;


}

  public function verificationVegetation ($tableau, $bdd){
      $error = true;
      $errorType = $this->verifVegetationType($tableau);
      $errorObligatoire = $this->verifVegetationObligatoire($tableau);
      $errorUnique = $this->verifIdUniqueVegetation($tableau, $bdd);
      $errorCdNom = $this->verifCdNom($tableau, $bdd);
      if($errorType == false || $errorObligatoire == false || $errorUnique == false || $errorCdNom == false){
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
      if(!(is_string($tableau[$row]["SP_NAME"]))){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN STRING , LIGNE : ".$row." / COLONNE : SP_NAME";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_numeric($tableau[$row]["STRATUM"])) && !($tableau[$row]["ALTITUDE"] == NULL)){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN INT , LIGNE : ".$row." / COLONNE : STRATUM";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_string($tableau[$row]["COVER"]))){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN STRING , LIGNE : ".$row." / COLONNE : COVER";
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
  
  public function verifCdNom($tableau, $bdd){
	$log = new log_error();
    $error = false;
    $sql = $bdd->query("SELECT cd_nom FROM taxref_9");
    $cdNom = $sql->fetchAll(PDO::FETCH_COLUMN);
	$verifTri = sort($cdNom);
	$valeurDebut = 0;
	$valeurFin = (count($cdNom)-1);
	$tabCount = count($tableau);
	
	for($row = 0 ; $row < $tabCount ; $row++){
		
		 if($this->rechercheDicho($cdNom, count($cdNom),  $tableau[$row]["CD_NOM"] )){
			$error = true;
		}
		if($error == false){
			$line = $row +2;
			$log->writeLog("ERREUR , CD_NOM N'EXISTE PAS DANS LA BASE DE DONNEES , LIGNE ".$line);
		}

	}

	return $error;
	
  }
  

  public function insertionVegetation($tableau, $bdd){
    $verif = false;
	$inc = 0;
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
			$tableau[$row]["CD_NOM"],
            $tableau[$row]["STRATUM"],
            $tableau[$row]["COVER"],
			$tableau[$row]["SP_NAME"],
			
          ));

		$inc++;
      }
        $bdd->commit();
        $verif = true;
      }
      catch ( Exception $e )
      {
        $bdd -> rollBack ();
        $log->writeLog($e->getMessage());
        $log->writeLog("INSERTION NON EFFECTUE");
      }
      return $inc;
  }

  public function deleteVegetation($bdd, $surveyName){


    $sql = $bdd->query("SELECT id_vegfr FROM survey WHERE name_releve='".$surveyName."'");
    $idVegFr = $sql->fetchAll(PDO::FETCH_COLUMN);


    $bdd->query("DELETE FROM vegetation WHERE id_vegfr='".$idVegFr[0]."'");


  }
  


  
  
public function rechercheDicho($tab, $nbVal, $val){


  $trouve = false;
  $id = 0;
  $im = 0;
  $ifin = ($nbVal-1);

  while(($trouve == false) && (($ifin - $id) > 1)){

    $im = (($id + $ifin)/2);   
	if($tab[$im] == $val){
		$trouve = true;
	}
  
    if($tab[$im] > $val){
		$ifin = $im;
	}
    else{
	 $id = $im;  
	}
  }
  
  if($tab[$id] == $val){
	 return true;   
  }
  else{
	  return false; 
  } 
}

  public function getNbrLines($nameSurvey){
	  $nbr_lignes = 0;
	    if (($handle = fopen($nameSurvey, "r")) !== FALSE) {
			$nbr_lignes = count(file($nameSurvey));
	        fclose($handle);
		}
		return $nbr_lignes;
  }


}

 ?>
