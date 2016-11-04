<?php

//classe contenant les fonctions d'initialisation, de correspondance, de vérification et d'insertion des données de végétations

class vegetation{


  public function vegetation(){
    include('log_error.php');

  }
  
  	// Cette fonction lance toutes les fonctions nécessaires à l'insertion des données
	//(initialisation, vérification et insertion)
	//Si l'insertion fait plus de 500 lignes, la page sera rechargée et la fonction relancé
	//pour insérer les 500 lignes suivantes
  
  public function initialisationVegetationAll($chemin, $bdd, $Nbr_row){
	 
	if($Nbr_row == 500){

		$chemin = substr($chemin, 12);
		$chemin = "../../../uploads/vegetation/".$chemin;
		$chemin = trim($chemin);
	}	 
  $nbrLines = $this->getNbrLines($chemin);
  $verif = false;
  $reponse = true;
  $tabName = $this->getTabNameVegetation($chemin);
  $tableau = $this->initVegetation($chemin, $tabName, $Nbr_row);
  $tableau = $this->correspondanceVegetation($tableau, $bdd);
  $tableau = $this->corresintNull($tableau);
  $verif = $this->verificationVegetation($tableau, $bdd);
	
	//Si la vérification est bonne on continue
  if($verif){
    $reponse = $this->insertionVegetation($tableau, $bdd);
		if($Nbr_row + 500 > $nbrLines){
			$Nbr_row = $nbrLines;
		}
		else{
			$Nbr_row = $Nbr_row + 500;
		}
			
    //echo(count($tableau).'<br>');
		echo $Nbr_row;
		echo " / ";
		echo $nbrLines;

		//Si il reste encore des lignes à insérer, on relance la fonction
		if($Nbr_row < $nbrLines){
			header( "Refresh:1; url=https://vegfrance.univ-rennes1.fr/Gestion_BDD/src/php/check_import/lancementVegetation.php?row=".$Nbr_row."&chemin=".$chemin, true);
		}
		//Si il n'y a plus de lignes, on stoppe l'insertion et on redirige vers la page d'importation
		else{
			echo " L'insertion est terminée ! Vous allez être redirigé vers la page d'importation.";
			header( "Refresh:5; url=https://vegfrance.univ-rennes1.fr/Gestion_BDD/src/php/fileupload.php?verif=1", true);
		}
  }
	//Si il y a eu une erreur, on redirige vers la page d'importation avec un lien pour télécharger le fichier d'erreur
	else{
		$reponse = false;
		echo " L'insertion a rencontré une erreur ! Vous allez être redirigé vers la page d'importation.";
		header( "Refresh:5; url=https://vegfrance.univ-rennes1.fr/Gestion_BDD/src/php/fileupload.php?verif=0", true);
	}
    return $reponse;
 }

 
   //Cette fonction permet de récuperer les noms des champs et de les mettre dans un tableau
   
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
  
  //Cette fonction permet de passer les données du fichier CSV sous la forme
  //d'un tableau PHP avec pour index des colonnes le nom des champs
  
  public function initVegetation($nameVegetation, $tabName, $row){
    $log = new log_error();
    $nbr_champs = 0;
    $tableau = array(array());
    $increment = 0;
    $line = 0;

    if (($handle = fopen($nameVegetation, "r")) !== FALSE) {
      $nbr_lignes = count(file($nameVegetation));
      $nbr_champs = count($tabName);
      $rowLow = $row + 1;
      if($nbr_lignes >= ($row + 500)){
        $rowHigh = $row + 500;
      }
      else{
        $rowHigh = $nbr_lignes; 
      }

      while (($data = fgetcsv($handle, 500, ";")) !== FALSE) {
        if($line >= 1 && $line >= $rowLow && $line <= $rowHigh){
          for ($c = 0; $c < $nbr_champs; $c ++) {
            $tableau[$increment][$tabName[$c]] = $data[$c];
          }
          $increment++;
        }
        $line++;
      }
      fclose($handle);
    }
    //var_dump($tableau);
    return $tableau;
  }
	
	
	//Permet de faire la correspondance d'un champ avec les listes prédéfinies
	
  public function correspondanceVegetation($tableau, $bdd){
  		for($row = 0 ; $row < count($tableau) ; $row++){
  			if($tableau[$row]["STRATUM"] != NULL){
  				 $dataStratum = $this->corresStratum($tableau[$row]["STRATUM"], $bdd);
  				 $tableau[$row]["STRATUM"] = $dataStratum;
  			}

  		}
    return $tableau;
  }
  
  
  //Renvoie une donnée de la liste "stratum" de la BDD en fonnction d'une autre donnée

  public function corresStratum($data, $bdd){


    $sql = $bdd->query("SELECT id_stratum FROM list_stratum WHERE label='".$data."'");
    $valeur = $sql->fetch();

    return $valeur[0];
  }

  
    //Initialise des données integer à NULL si elles le sont
  
public function corresintNull($tableau){
	for($row = 0 ; $row < count($tableau) ; $row++){
		if($tableau[$row]["STRATUM"] == NULL){
			$tableau[$row]["STRATUM"] = NULL;
		}
	}
	return $tableau;


}


	//Lance toutes les fonctions de vérification pour les données de végétation

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

	// Cette fonction permet de vérifier que toutes les données sont bien du type que l'on attend
	
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

  
  //Vérifie que les données obligatoire ne sont pas nulles
  
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
  
  //Vérifie si les données uniques ne sont pas présentes dans la BDD

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
        //echo 'toto : '.$row.'<br>';
        $log->writeLog("ERREUR , L'IDENTIFIANT NAME_RELEVE: toto ".$row." ".$tableau[$row]["NAME_RELEVE"]." N'EXISTE PAS DANS LA BASE DE DONNEES");
        $error = false;
      }
    }
    return $error;
  }
  
  
  // Vérifie si le CD_NOM associé existe dans la BDD
  
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
      
      if($this->rechercheDicho($cdNom, count($cdNom), $tableau[$row]["CD_NOM"] )){
        $error = true;
      }
      if($error == false){
        $line = $row +2;
        $log->writeLog("ERREUR , CD_NOM ".$tableau[$row]["CD_NOM"]." N'EXISTE PAS DANS LA BASE DE DONNEES , LIGNE ".$line);
      }

    }
    return $error;
  }
  
  //  //Insertion des données dans la table végétation

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
            $tableau[$row]["CD_NOM"],
            $tableau[$row]["STRATUM"],
            $tableau[$row]["COVER"],
            $tableau[$row]["SP_NAME"],
          ));
      }
        $bdd->commit();
        $verif = true;
      }
      catch ( Exception $e )
      {
        $bdd -> rollBack ();
        $log->writeLog($e->getMessage());
        $log->writeLog("INSERTION NON EFFECTUEE");
      }
      return $verif;
  }

  public function deleteVegetation($bdd, $surveyName){


    $sql = $bdd->query("SELECT id_vegfr FROM survey WHERE name_releve='".$surveyName."'");
    $idVegFr = $sql->fetchAll(PDO::FETCH_COLUMN);


    $bdd->query("DELETE FROM vegetation WHERE id_vegfr='".$idVegFr[0]."'");


  }
  


  //Algorithme de recherche utilisé pour la recherche du CD_NOM
  
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


  //Cette fonction permet de récuperer le nombre de lignes dans un fichier CSV

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
