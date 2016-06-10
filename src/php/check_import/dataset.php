<?php

class dataset {

  public function dataset(){
    include('log_error.php');
  }

  public function initialisationDatasetAll($chemin, $bdd){
    $reponse = false;
    $tabName = $this->getTabNameDataset($chemin);
    $tableau = $this->initDataset($chemin, $tabName, $bdd);
    $verif = $this->verificationDataset($tableau);
    if($verif){
      $reponse = $this->insertionDataset($tableau, $bdd);
    }
    return $reponse;
  }

  public function getTabNameDataset($nameDataset){
    $tabName = array();
    $row = 0;

    if (($handle = fopen($nameDataset, "r")) !== FALSE) {
      $nbr_lignes = count(file($nameDataset));

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

  public function initDataset($nameDataset, $tabName, $bdd){
    $log = new log_error();
    $log->resetLog();
    $row = 0;
    $nbr_champs = 0;
    $tableau = array(array());


    if (($handle = fopen($nameDataset, "r")) !== FALSE) {
      $nbr_lignes = count(file($nameDataset));

      while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $nbr_champs = count($data);

      if($row >= 1){
        for ($c=0; $c < $nbr_champs; $c++) {
          $tableau[$row-1][$tabName[$c]] = $data[$c];


        }
        $requete = $bdd->query("SELECT id_user FROM users WHERE pseudo='".$_SESSION['pseudo']."'");
       $donnees = $requete->fetch();
       $tableau[$row-1]["ID_SUPPLIER"] = $donnees['id_user'];
        
      }
        $row++;
      }
      fclose($handle);
    }
    return $tableau;
  }


  public function verificationDataset ($tableau){
	$log = new log_error();
    $error = true;
	$test = true;
		  if(!(array_key_exists("PROJECT", $tableau[0]))){
		  $logError = "ERREUR, MAUVAIS FICHIER";
			$log->writeLog($logError);
			$test = false;
	  }
	 if($test == true){
		$errorObligatoire = $this->verifDatasetObligatoire($tableau);
		$errorType = $this->verifTypeDonnees($tableau);
		if($errorObligatoire == false || $errorType == false){
			$error = false;
		}

	 }
	 else {
		 $error = false;
	 }


    return $error;

  }


  public function verifTypeDonnees($tableau){
      $log = new log_error();
      $error = true;
      for($row=0; $row < count($tableau)-1 ; $row++){
      if(!(is_string($tableau[$row]["PROJECT"]))){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN STRING , LIGNE : ".$row." / COLONNE : PROJECT";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_numeric($tableau[$row]["REGIME"]))){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN INT , LIGNE : ".$row." / COLONNE : REGIME";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_string($tableau[$row]["TYPE"]))){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN STRING , LIGNE : ".$row." / COLONNE : TYPE";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_string($tableau[$row]["ORGANIZATION"]))) {
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN STRING , LIGNE : ".$row." / COLONNE : ORGANIZATION";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_string($tableau[$row]["TAXUM_REFERENCE"]))) {
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN STRING , LIGNE : ".$row." / COLONNE : TAXUM_REFERENCE";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_numeric($tableau[$row]["ID_SUPPLIER"]))){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN INT , LIGNE : ".$row." / COLONNE : ID_SUPPLIER";
        $log->writeLog($logError);
        $error = false;
      }
    }
    return $error;
  }

  // public function verifIdUniqueDataset($tableau, $bdd){
  //   $log = new log_error();
  //   $error = true;
  //   $sql = $bdd->query("SELECT id_dataset FROM dataset");
  //   $dataset = $sql->fetchAll(PDO::FETCH_COLUMN);
  //
  //   for($row = 0 ; $row < count($dataset) ; $row++ ){
  //     for($i = 0 ; $i < count($tableau) ; $i++){
  //       if($tableau[$i]["PROJECT"] == $dataset[$row]){
  //         $error = false;
  //           $log->writeLog("ERREUR , L'IDENTIFIANT NAME_RELEVE: ".$tableau[$i]["PROJECT"]." EXISTE DEJA DANS LA BASE DE DONNEES");
  //       }
  //     }
  //   }

  //  return $error;


//  }


  public function verifDatasetObligatoire($tableau){
		$error = true;
		for($row = 0 ; $row < count($tableau)-1 ; $row++){
			if($tableau[$row]["PROJECT"] == NULL){
				$logError = "ERREUR, VALEUR OBLIGATOIRE MANQUANTE , LIGNE : ".$row." / COLONNE : PROJECT";
				$log->writeLog($logError);
				$error = false;
			}
			if($tableau[$row]["REGIME"] == NULL){
				$logError = "ERREUR, VALEUR OBLIGATOIRE MANQUANTE , LIGNE : ".$row." / COLONNE : REGIME";
				$log->writeLog($logError);
				$error = false;
			}
			if($tableau[$row]["TYPE"] == NULL){
				$logError = "ERREUR, VALEUR OBLIGATOIRE MANQUANTE , LIGNE : ".$row." / COLONNE : TYPE";
				$log->writeLog($logError);
				$error = false;
			}
			if($tableau[$row]["ORGANIZATION"] == NULL){
				$logError = "ERREUR, VALEUR OBLIGATOIRE MANQUANTE , LIGNE : ".$row." / COLONNE : ORGANIZATION";
				$log->writeLog($logError);
				$error = false;
			}

		}
		return $error;

	}

  public function insertionDataset ($tableau, $bdd){
    $verif = false;
    $log = new log_error();

    $sql = "INSERT INTO dataset (project, id_regime, type, organization, taxum_reference, id_supplier) VALUES (?,?,?,?,?,?)";

    $bdd->beginTransaction();
    try
    {
      for($row = 0 ; $row < count($tableau) ; $row++){

      $query=$bdd->prepare($sql);
      $query->execute(array(
        $tableau[$row]["PROJECT"],
        $tableau[$row]["REGIME"],
        $tableau[$row]["TYPE"],
        $tableau[$row]["ORGANIZATION"],
        $tableau[$row]["TAXUM_REFERENCE"],
        $tableau[$row]["ID_SUPPLIER"]
      ));

      }
      $bdd->commit();
      $verif = true;
    }
    catch ( Exception $e )
    {
      $bdd->rollBack ();
      $log->writeLog($e->getMessage());
      $log->writeLog("INSERTION NON EFFECTUE");
    }
    return $verif;
  }

  public function recupSupplier(){

  }

  public function deleteDataset($bdd, $datasetName){

    $sql = $bdd->query("SELECT id_dataset FROM dataset WHERE project='".$datasetName."'");
    $datasetId = $sql->fetchAll(PDO::FETCH_COLUMN);
    $sql = $bdd->query("SELECT id_vegfr FROM survey WHERE id_dataset='".$datasetId[0]."'");
    $idVegFr = $sql->fetchAll(PDO::FETCH_COLUMN);

    for($row = 0 ; $row < count($idVegFr) ; $row++ ){
        $bdd->query("DELETE FROM vegetation WHERE id_vegfr='".$idVegFr[$row]."'");
        $bdd->query("DELETE FROM int_type WHERE id_vegfr='".$idVegFr[$row]."'");
        $bdd->query("DELETE FROM varchar_type WHERE id_vegfr='".$idVegFr[$row]."'");
        $bdd->query("DELETE FROM list_parameters WHERE id_vegfr='".$idVegFr[$row]."'");
    }

    $bdd->query("DELETE FROM survey WHERE id_dataset='".$datasetId[0]."'");
    $bdd->query("DELETE FROM dataset WHERE id_dataset='".$datasetId[0]."'");
  }

  public function updateDataset($bdd, $datasetId){
    $this->deleteDataset($bdd, $datasetId);


  }
  public function DeleteAll($bdd){
    $bdd->query("TRUNCATE survey CASCADE");
  }

  public function getProjects($bdd){
    $sql = $bdd->query("SELECT projects FROM dataset");
    $listProjects = $sql->fetchAll(PDO::FETCH_COLUMN);

    return $listProjects;
  }


}



?>
