<?php

//classe contenant les fonctions d'initialisation, de vérification et d'insertion des datasets

class dataset {

  public function dataset(){
    include('log_error.php');
  }

  
   // Cette fonction lance toutes les fonctions nécessaires à l'insertion des données
   //(initialisation, vérification et insertion)
   
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

  //Cette fonction permet de récuperer les noms de champs et de les mettre dans un tableau
  
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

  //Cette fonction permet de passer les données du fichier CSV sous la forme
  //d'un tableau PHP avec pour index des colonne le nom des champs
  
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


  // Cette fonction permet de lancer les fonctions de vérification et de récuperer le résultat
  
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
  
  // Cette fonction permet de vérifier si le type de données des champs est correct

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

	// Cette fonction permet de vérifier si certaines données obligatoires ne sont pas nul.

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
	
		// Cette fonction permet d'inserer les données ayant passé la vérification dans la BDD

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

  //Supression des données d'un dataset et les données associés
  
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

  
  
  //Permet de récupérer les liste des dataset

  public function getProjects($bdd){
    $sql = $bdd->query("SELECT projects FROM dataset");
    $listProjects = $sql->fetchAll(PDO::FETCH_COLUMN);

    return $listProjects;
  }


}



?>
