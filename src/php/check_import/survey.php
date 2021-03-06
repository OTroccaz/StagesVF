<?php

//classe contenant les fonctions d'initialisation, de correspondance, de vérification et d'insertion des relevés

class survey{
	
	public $row;
	public $limit;
	public $chemin;
	
	
	
	public function __construct()
    {
		include('log_error.php');
		include('int_parameters.php');
		include('varchar_parameters.php');
		include('list_parameters.php');
		ini_set('memory_limit', '-1');
		set_time_limit(0);
    }

	
	
	// Cette fonction lance toutes les fonctions nécessaires à l'insertion des données
	//(initialisation, vérification et insertion)
	//Si l'insertion fait plus de 500 lignes, la page sera rechargée et la fonction relancé
	//pour insérer les 500 lignes suivantes
	
	
  public function initialisationSurveyAll($chemin, $bdd, $Nbr_row){
	
    $log = new log_error();
    $reponse = false;
    $verif = false;
	
	if($Nbr_row == 500){

		$chemin = substr($chemin, 12);
		$chemin = "../../../uploads/survey/".$chemin;
		$chemin = trim($chemin);
	}
	
	$nbrLines = $this->getNbrLines($chemin);
    $tabName = $this->getTabNameSurvey($chemin);
    $tableau = $this->initSurvey($chemin, $tabName, $Nbr_row);
    $tableau = $this->correspondanceSurvey($tableau, $bdd);
    $tableau = $this->intNull($tableau, $bdd);
	$verif = $this->verificationSurvey($tableau, $bdd);
	
	//Si la vérification est bonne on continue
    if($verif){
		$reponse = $this->insertionSurveyAll($tableau, $bdd);
		
		
		if($Nbr_row + 500 > $nbrLines){
			$Nbr_row = $nbrLines;
		}
		else{
			$Nbr_row = $Nbr_row + 500;
		}
			
		echo $Nbr_row;
		echo " / ";
		echo $nbrLines;

		//Si il reste encore des lignes à insérer, on relance la fonction
		if($Nbr_row < $nbrLines){
			header( "Refresh:1; url=https://vegfrance.univ-rennes1.fr/Gestion_BDD/src/php/check_import/lancement.php?row=".$Nbr_row."&chemin=".$chemin, true);

		}
		//Si il n'y a plus de lignes, on stop l'insertion et on redirige vers la page d'importation
		else{
			echo " L'insertion est terminé ! Vous allez être redirigé vers la page d'importation.";
			header( "Refresh:5; url=https://vegfrance.univ-rennes1.fr/Gestion_BDD/src/php/fileupload.php?verif=1", true);
		}
    }
	//Si il y a eu une erreur, on redirige vers la page d'importation avec un lien pour télécharger le fichier d'erreur
	else{
		$reponse = false;
		echo " L'insertion a rencontrée une erreur ! Vous allez être redirigé vers la page d'importation.";
		header( "Refresh:5; url=https://vegfrance.univ-rennes1.fr/Gestion_BDD/src/php/fileupload.php?verif=0", true);
	}
    return $reponse;
 }

	//Cette fonction lance toutes les fonctions d'insertion nécéssaire pour les données des relevés
	//ainsi que les champs facultatifs
 
  public function insertionSurveyAll($tableau, $bdd){
    $verif = false;
    $log = new log_error();
    $bdd->beginTransaction();
    $intParam = new int_parameters();
    $varcharParam = new varchar_parameters();
    $listParam = new list_parameters();
    try{
		$this->insertionSurvey($tableau, $bdd);
        $intParam->insertionIntParam($tableau, $bdd);
		$varcharParam->insertionVarcharParam($tableau, $bdd);
        $listParam->insertionListParam($tableau, $bdd);
		$this->initGeom($bdd);
		$bdd->commit();
		$verif = true;
    }
    catch ( Exception $e )
    {
      $bdd -> rollBack ();
	  $msg = $e->getMessage();
      $log->writeLog($e->getMessage());
      $log->writeLog("INSERTION NON EFFECTUE");
    }
    return $verif;
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
  
  
  //Cette fonction permet de récuperer les noms de champs et de les mettre dans un tableau
  
  public function getTabNameSurvey($nameSurvey){
    $tabName = array();
    $row = 0;
    $offset = 0;

    if (($handle = fopen($nameSurvey, "r")) !== FALSE) {
      $nbr_lignes = count(file($nameSurvey));

      while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $nbr_champs = count($data);

        if($row == 1){
          for($i = 0 ; $i < $nbr_champs ; $i++){

            $tabName[$offset] = $data[$i];
            $offset++;

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

  public function initSurvey($nameSurvey, $tabName, $row){
    $log = new log_error();
    $nbr_champs = 0;
	$nbr_lignes = 0;
    $tableau = array(array());
	$increment = 0;
	$line = 0;

    if (($handle = fopen($nameSurvey, "r")) !== FALSE) {
		$nbr_lignes = count(file($nameSurvey));
		$nbr_champs = count($tabName);
		$rowLow = $row + 1;
		if($nbr_lignes >= ($row + 500)){
			$rowHigh = $row + 500;
		}
		else{
			$rowHigh = $nbr_lignes; 

		}
		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {

			if($line > 1 && $line >= $rowLow && $line <= $rowHigh){
				for ($c=0; $c < $nbr_champs ; $c++) {
					$data[$c] = str_replace("'", "&apos;", $data[$c]);
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
  
	//Cette fonction va récuperer les listes de la BDD pour faire la correspondances
	//entre les données du fichier CSV et celles de la BDD

  public function correspondanceSurvey($tableau, $bdd){
	
	  
      $sql = $bdd->query("SELECT project FROM dataset");
      $tableauDataset = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_dataset FROM dataset");
      $tableauDatasetId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_protocol");
      $tableauProtocol = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_protocol FROM list_protocol");
      $tableauProtocolId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_coverscale");
      $tableauCoverscale = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_coverscale FROM list_coverscale");
      $tableauCoverscaleId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT localisation_method FROM list_loca_metho");
      $tableauLocaMethod = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT label FROM list_loca_metho");
      $tableauPrecision = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_loca_metho FROM list_loca_metho");
      $tableauLocaMethodId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_sampling");
      $tableauSampling = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_sampling FROM list_sampling");
      $tableauSamplingId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_exposure");
      $tableauExposure = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_exposure FROM list_exposure");
      $tableauExposureId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_geol_subst");
      $tableauGeolSubst = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_geol_subst FROM list_geol_subst");
      $tableauGeolSubstId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_pedology");
      $tableauPedology = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_pedology FROM list_pedology");
      $tableauPedologyId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_management");
      $tableauManagement = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_management FROM list_management");
      $tableauManagementId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_lighting");
      $tableauLighting = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_lighting FROM list_lighting");
      $tableauLightingId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_soil_ph");
      $tableauSoilPh = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_soil_ph FROM list_soil_ph");
      $tableauSoilPhId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_hydro");
      $tableauHydro = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_hydro FROM list_hydro");
      $tableauHydroId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_nutrient");
      $tableauNutrient = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_nutrient FROM list_nutrient");
      $tableauNutrientId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_temperatur");
      $tableauTemperatur = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_temperatur FROM list_temperatur");
      $tableauTemperaturId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_salinity");
      $tableauSalinity = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_salinity FROM list_salinity");
      $tableauSalinityId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_typus");
      $tableauRelType = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_typus FROM list_typus");
      $tableauRelTypeId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_pvf1");
      $tableauPvf1 = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_pvf1 FROM list_pvf1");
      $tableauPvf1Id = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_pvf2");
      $tableauPvf2 = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT ID_PVF2 FROM list_pvf2");
      $tableauPvf2Id = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_clc");
      $tableauClc = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_clc FROM list_clc");
      $tableauClcId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_n2k");
      $tableauN2k = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_n2k FROM list_n2k");
      $tableauN2kId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_catminat");
      $tableauCatminat = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_catminat FROM list_catminat");
      $tableauCatminatId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_cah_hab");
      $tableauCahHab = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_cah_hab FROM list_cah_hab");
      $tableauCahHabId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_eunis");
      $tableauEunis = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_eunis FROM list_eunis");
      $tableauEunisId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_corine");
      $tableauCorine = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_corine FROM list_corine");
      $tableauCorineId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM list_nat_region");
      $tableauNatRegion = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_nat_region FROM list_nat_region");
      $tableauNatRegionId = $sql->fetchAll(PDO::FETCH_COLUMN);
	  


    for($i = 0 ; $i < count($tableau) ; $i++){

      if($tableau[$i]["PROJECT"] != NULL){
        $dataDataset = $this->corresList($tableau[$i]["PROJECT"], $bdd, $tableauDataset, $tableauDatasetId);
        $tableau[$i]["PROJECT"] = $dataDataset;
      }
      if($tableau[$i]["PROTOCOL"] != NULL){
        $dataProtocol = $this->corresList($tableau[$i]["PROTOCOL"], $bdd, $tableauProtocol, $tableauProtocolId);
        $tableau[$i]["PROTOCOL"] = $dataProtocol;
      }
      if($tableau[$i]["COVERSCALE"] != NULL){
        $dataCoverscale = $this->corresList($tableau[$i]["COVERSCALE"], $bdd, $tableauCoverscale, $tableauCoverscaleId);
        $tableau[$i]["COVERSCALE"] = $dataCoverscale;
      }

      if($tableau[$i]["LOCA_METHOD"] != NULL || $tableau[$i]["PRECISION"] != NULL){
        $dataLocaMethod = $this->corresLocaMethod($tableau[$i]["LOCA_METHOD"],$tableau[$i]["PRECISION"], $bdd, $tableauLocaMethod, $tableauPrecision, $tableauLocaMethodId);
        $tableau[$i]["LOCA_METHOD"] = $dataLocaMethod;
        $tableau[$i]["PRECISION"] = $dataLocaMethod;
      }
      if($tableau[$i]["SAMPLING"] != NULL){
        $dataSampling = $this->corresList($tableau[$i]["SAMPLING"], $bdd, $tableauSampling, $tableauSamplingId);
        $tableau[$i]["SAMPLING"] = $dataSampling;
      }
      if($tableau[$i]["EXPOSURE"] != NULL){
        $dataExposure = $this->corresList($tableau[$i]["EXPOSURE"], $bdd, $tableauExposure, $tableauExposureId);
        $tableau[$i]["EXPOSURE"] = $dataExposure;
      }
      if($tableau[$i]["GEOL_SUBST"] != NULL){
        $dataGeolSubst = $this->corresList($tableau[$i]["GEOL_SUBST"], $bdd, $tableauGeolSubst, $tableauGeolSubstId);
        $tableau[$i]["GEOL_SUBST"] = $dataGeolSubst;
      }
      if($tableau[$i]["PEDOLOGY"] != NULL){
        $dataPedology = $this->corresList($tableau[$i]["PEDOLOGY"], $bdd, $tableauPedology, $tableauPedologyId);
        $tableau[$i]["PEDOLOGY"] = $dataPedology;
      }
      if($tableau[$i]["MANAGEMENT"] != NULL){
        $dataManagement = $this->corresList($tableau[$i]["MANAGEMENT"], $bdd, $tableauManagement, $tableauManagementId);
        $tableau[$i]["MANAGEMENT"] = $dataManagement;
      }
      if($tableau[$i]["LIGHTING"] != NULL){
        $dataLighting = $this->corresList($tableau[$i]["LIGHTING"], $bdd, $tableauLighting, $tableauLightingId);
        $tableau[$i]["LIGHTING"] = $dataLighting;
      }
      if($tableau[$i]["SOIL_PH"] != NULL){
        $dataSoilPh = $this->corresList($tableau[$i]["SOIL_PH"], $bdd, $tableauSoilPh, $tableauSoilPhId);
        $tableau[$i]["SOIL_PH"] = $dataSoilPh;
      }
      if($tableau[$i]["HYDRO"] != NULL){
        $dataHydro = $this->corresList($tableau[$i]["HYDRO"], $bdd, $tableauHydro, $tableauHydroId);
        $tableau[$i]["HYDRO"] = $dataHydro;
      }
      if($tableau[$i]["NUTRIENT"] != NULL){
        $dataNutrient = $this->corresList($tableau[$i]["NUTRIENT"], $bdd, $tableauNutrient, $tableauNutrientId);
        $tableau[$i]["NUTRIENT"] = $dataNutrient;
      }
      if($tableau[$i]["TEMPERATUR"] != NULL){
        $dataTemperatur = $this->corresList($tableau[$i]["TEMPERATUR"], $bdd, $tableauTemperatur, $tableauTemperaturId);
        $tableau[$i]["TEMPERATUR"] = $dataTemperatur;
      }
      if($tableau[$i]["SALINITY"] != NULL){
        $dataSalinity = $this->corresList($tableau[$i]["SALINITY"], $bdd, $tableauSalinity, $tableauSalinityId);
        $tableau[$i]["SALINITY"] = $dataSalinity;
      }
      if($tableau[$i]["REL_TYPE"] != NULL){
        $dataRelType = $this->corresList($tableau[$i]["REL_TYPE"], $bdd, $tableauRelType, $tableauRelTypeId);
        $tableau[$i]["REL_TYPE"] = $dataRelType;
      }

      if($tableau[$i]["PVF1"] != NULL){
        $dataPvf1 = $this->corresList($tableau[$i]["PVF1"], $bdd, $tableauPvf1, $tableauPvf1Id);
        $tableau[$i]["PVF1"] = $dataPvf1;
      }
      if($tableau[$i]["PVF2_ASS"] != NULL){
         $dataPvf2 = $this->corresList($tableau[$i]["PVF2_ASS"], $bdd, $tableauPvf2,  $tableauPvf2Id);
         $tableau[$i]["PVF2_ASS"] = $dataPvf2;
       }
      if($tableau[$i]["CLC"] != NULL){
        $dataClc = $this->corresList($tableau[$i]["CLC"], $bdd, $tableauClc, $tableauClcId);
        $tableau[$i]["CLC"] = $dataClc;
      }
      if($tableau[$i]["CATMINAT"] != NULL){
        $dataCatminat = $this->corresList($tableau[$i]["CATMINAT"], $bdd, $tableauCatminat, $tableauCatminatId);
        $tableau[$i]["CATMINAT"] = $dataCatminat;
      }
      if($tableau[$i]["N_2000"] != NULL){
        $dataN2k = $this->corresList($tableau[$i]["N_2000"], $bdd, $tableauN2k, $tableauN2kId);
        $tableau[$i]["N_2000"] = $dataN2k;
      }
      if($tableau[$i]["CAH_HAB"] != NULL){
        $dataCahHab = $this->corresList($tableau[$i]["CAH_HAB"], $bdd, $tableauCahHab, $tableauCahHabId);
        $tableau[$i]["CAH_HAB"] = $dataCahHab;
      }
      if($tableau[$i]["EUNIS"] != NULL){
        $dataEunis = $this->corresList($tableau[$i]["EUNIS"], $bdd, $tableauEunis, $tableauEunisId);
        $tableau[$i]["EUNIS"] = $dataEunis;
      }
      if($tableau[$i]["CORINE"] != NULL){
        $dataCorine = $this->corresList($tableau[$i]["CORINE"], $bdd, $tableauCorine, $tableauCorineId);
        $tableau[$i]["CORINE"] = $dataCorine;
      }
      if($tableau[$i]["NAT_REGION"] != NULL){
        $dataNatRegion = $this->corresList($tableau[$i]["NAT_REGION"], $bdd, $tableauNatRegion, $tableauNatRegionId);
        $tableau[$i]["NAT_REGION"] = $dataNatRegion;
      }



    }
    $tableau = $this->corresBooleanAll($tableau);
    return $tableau;
  }
  
  //Fonction permettant de faire la comparaison et d'initialiser la donnée si elle correspond

  public function corresList($data, $bdd, $tableau1, $tableauId){
    $row = 0;
    $verif = false;
    while($verif == false && $row < count($tableau1)){

      if($data == $tableau1[$row]){
        $data = $tableauId[$row];
        $verif = true;
      }
      $row++;
    }
    if($verif == false){
      $data = NULL;
    }
    return $data;
  }

  //Fonction permettant de faire la comparaison et d'initialiser la donnée si elle correspond
  //Cas particulier
  
  public function corresLocaMethod($data, $data2, $bdd, $tableau, $tableau2, $tableauId){
    $row = 0;
    $verif = false;
    while($verif == false && $row < count($tableau)){
      if($data == $tableau[$row] && $data2 == $tableau2[$row]){
        $data = $tableauId[$row];
        $verif = true;
      }
      $row++;
    }
    return $data;
  }


	//Cette fonction permet de faire la correspondances pour les booléens
	
  public function corresBoolean($data){

    if($data == "Oui"){
      $tmp = TRUE;
    }
    else if($data == "Non"){
      $tmp = FALSE;
    }
    $data = $tmp;
    return $data;

  }

  	//Cette fonction permet de faire la correspondances pour les booléens prédéfinis
	
  public function corresBooleanAll($tableau){
    for($i = 0 ; $i < count($tableau) ; $i++){
      if($tableau[$i]["COMPLETE"] != NULL){
        $corresBool = $this->corresBoolean($tableau[$i]["COMPLETE"]);
        $tableau[$i]["COMPLETE"] = $corresBool;
      }
      if($tableau[$i]["MOSS_IDENT"] != NULL){
        $corresBool = $this->corresBoolean($tableau[$i]["MOSS_IDENT"]);
        $tableau[$i]["MOSS_IDENT"] = $corresBool;
      }
      if($tableau[$i]["LICH_IDENT"] != NULL){
        $corresBool = $this->corresBoolean($tableau[$i]["LICH_IDENT"]);
        $tableau[$i]["LICH_IDENT"] = $corresBool;
      }

    }
    return $tableau;
  }


	//Cette fonction permet de lancer toutes les fonctions de vérification des données
	
  public function verificationSurvey ($tableau,$bdd){
    $intParam = new int_parameters();
    $varcharParam = new varchar_parameters();
    $listParam = new list_parameters();
    $error = true;
    $errorObligatoire = $this->verifSurveyObligatoire($tableau);
    $errorInt = $intParam->verifInt($tableau, $bdd);
    $errorVarchar = $varcharParam->verifVarchar($tableau, $bdd);
    $errorList = $listParam->verifList($tableau, $bdd);
    $errorUnique = $this->verifUnique($tableau, $bdd);
    $errorVerifType = $this->verifTypeSurvey($tableau);
    if($errorInt == false || $errorVarchar == false || $errorObligatoire == false ||
    $errorUnique == false ||  $errorVerifType == false || $errorList == false)
    {
      $error = false;
    }
    return $error;

  }
  
  
    // Cette fonction permet de vérifier que toutes les données sont bien du type que l'on attend
	
  public function verifTypeSurvey($tableau){
    $log = new log_error();
    $error = true;
	$nbrRow = count($tableau);
    for($row = 1 ; $row < $nbrRow ; $row++){
		
      if(!(is_string($tableau[$row]["NAME_RELEVE"]))){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN STRING , LIGNE : ".$row." / COLONNE : NAME_RELEVE";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_string($tableau[$row]["DATE_S"]))){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UNE DATE , LIGNE : ".$row." / COLONNE : DATE_S";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_bool($tableau[$row]["COMPLETE"]))){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN BOOLEEN , LIGNE : ".$row." / COLONNE : COMPLETE";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_string($tableau[$row]["AUTHOR"]))){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN STRING , LIGNE : ".$row." / COLONNE : AUTHOR";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_numeric($tableau[$row]["DEG_LAT"])) && !($tableau[$row]["DEG_LAT"] == NULL)){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN REEL , LIGNE : ".$row." / COLONNE : DEG_LAT";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_numeric($tableau[$row]["DEG_LONG"])) && !($tableau[$row]["DEG_LONG"] == NULL)){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN REEL , LIGNE : ".$row." / COLONNE : DEG_LONG";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_numeric($tableau[$row]["ALTITUDE"])) && !($tableau[$row]["ALTITUDE"] == NULL)){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN INT , LIGNE : ".$row." / COLONNE : ALTITUDE";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_string($tableau[$row]["TABLE_NR"]))){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN STRING , LIGNE : ".$row." / COLONNE : TABLE_NB";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_string($tableau[$row]["NR_IN_TAB"]))){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN STRING , LIGNE : ".$row." / COLONNE : NR_IN_TAB";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_string($tableau[$row]["REF_GEO"]))){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN STRING , LIGNE : ".$row." / COLONNE : REF_GEO";
        $log->writeLog($logError);
        $error = false;
      }
      if(!(is_string($tableau[$row]["REPETITION"]))){
        $logError = "ERREUR, LE TYPE DE DONNEE N'EST PAS UN STRING , LIGNE : ".$row." / COLONNE : REPETITION";
        $log->writeLog($logError);
        $error = false;
      }
    }
    return $error;
  }


  //Initialise des données integer à NULL si elles le sont

  public function intNull($tableau, $bdd){

    $sql = $bdd->query("SELECT label FROM list_int");
    $champsInt = $sql->fetchAll(PDO::FETCH_COLUMN);

    for($row = 0; $row < count($tableau) ; $row++){
      if($tableau[$row]["ALTITUDE"] == NULL){
        $tableau[$row]["ALTITUDE"] = NULL;
      }
      if($tableau[$row]["DEG_LONG"] == NULL){
        $tableau[$row]["DEG_LONG"] = NULL;
      }
      if($tableau[$row]["DEG_LAT"] == NULL){
        $tableau[$row]["DEG_LAT"] = NULL;
      }
      if($tableau[$row]["DEG_LAT"] == NULL){
        $tableau[$row]["DEG_LAT"] = NULL;
      }

    }

    for($champs = 0 ; $champs < count($champsInt) ; $champs++){
      for($row = 0; $row < count($tableau) ; $row++){
        if($tableau[$row][$champsInt[$champs]] == NULL){
          $tableau[$row][$champsInt[$champs]] = NULL;
        }
      }
    }
    return $tableau;
  }


//Vérifie si les données uniques ne sont pas présent dans la BDD
  
  public function verifUnique($tableau, $bdd){
    $log = new log_error();
    $error = true;
    $sql = $bdd->query("SELECT name_releve FROM survey");
    $survey = $sql->fetchAll(PDO::FETCH_COLUMN);

    for($row = 0 ; $row < count($survey) ; $row++ ){
      for($i = 0 ; $i < count($tableau) ; $i++){
        if($tableau[$i]["NAME_RELEVE"] == $survey[$row]){
          $error = false;
          $log->writeLog("ERREUR , L'IDENTIFIANT NAME_RELEVE: ".$tableau[$i]["NAME_RELEVE"]." EXISTE DEJA DANS LA BASE DE DONNEES");
        }
      }
    }

    return $error;
  }

	//Vérifie que les données obligatoire ne sont pas nul
  
  public function verifSurveyObligatoire($tableau){
    $log = new log_error();
    $error = true;
    for($row = 1 ; $row < count($tableau) ; $row++){
      if($tableau[$row]["PROJECT"] == NULL){
        $log->writeLog("ERREUR , Nom du Dataset manquant, LIGNE : ".$row." / COLONNE : PROJECT");
        $error = false;
      }
      if($tableau[$row]["NAME_RELEVE"] == NULL){
        $log->writeLog("ERREUR , Nom du relevé manquant, LIGNE : ".$row." / COLONNE : NAME_RELEVE");
        $error = false;
      }
      if($tableau[$row]["COMPLETE"] == NULL){
        $log->writeLog("ERREUR , Completude du relevé manquant, LIGNE : ".$row." / COLONNE : COMPLETE");
        $error = false;
      }
      if($tableau[$row]["PROTOCOL"] == NULL){
        $log->writeLog("ERREUR , protocole du relevé manquant, LIGNE : ".$row." / COLONNE : PROTOCOL");
        $error = false;
      }
      if($tableau[$row]["COVERSCALE"] == NULL){
        $log->writeLog("ERREUR , Echelle d'abondance du relevé manquant, LIGNE : ".$row." / COLONNE : COVERSCALE");
        $error = false;
      }

    }
    return $error;
  }

  
  //Insertion des données dans la table survey
  
  public function insertionSurvey ($tableau, $bdd){

    $sql = "INSERT INTO survey (id_dataset, name_releve, complete, date_s, 	id_protocol,
      id_coverscale, author, deg_lon,deg_lat, altitude, table_nb, nb_in_table, ref_geo, repetition)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

      for($row = 0 ; $row < count($tableau) ; $row++){
        $query=$bdd->prepare($sql);
        $query->execute(array(
          $tableau[$row]["PROJECT"],	
          $tableau[$row]["NAME_RELEVE"],	
          $tableau[$row]["COMPLETE"],	
          $tableau[$row]["DATE_S"],	
          $tableau[$row]["PROTOCOL"],	
          $tableau[$row]["COVERSCALE"],	
          $tableau[$row]["AUTHOR"],
          $tableau[$row]["DEG_LONG"],	
          $tableau[$row]["DEG_LAT"],	
          $tableau[$row]["ALTITUDE"],	
          $tableau[$row]["TABLE_NR"],	
          $tableau[$row]["NR_IN_TAB"],	
          $tableau[$row]["REF_GEO"],	
          $tableau[$row]["REPETITION"],	

        ));

      }
	  
	  //Appelle de la fonction d'insertion dans la table local_admin
      $this->insertionLocaAdmin($tableau, $bdd);
    }

	//Insetion des données dans la table local_admin
	
    public function insertionLocaAdmin ($tableau, $bdd){


      $sql = "INSERT INTO local_admin (id_vegfr, country, department, county_name, locality)
        VALUES (?,?,?,?,?)";


        for($row = 0 ; $row < count($tableau) ; $row++){

          $sql2 = $bdd->query("SELECT id_vegfr FROM survey WHERE name_releve = '".$tableau[$row]["NAME_RELEVE"]."'");
          $idVegfr = $sql2->fetchAll(PDO::FETCH_COLUMN);

          $query=$bdd->prepare($sql);
          $query->execute(array(
            $idVegfr[0],
            "FRANCE",
            $tableau[$row]["DEPARTMENT"],
            $tableau[$row]["COUNTY"],
            $tableau[$row]["LOCALITY"],

          ));

        }
      }
	  
	  //Suppression des données de la table survey en fonction de l'ID

      public function deleteSurvey($bdd, $surveyName){


        $sql = $bdd->query("SELECT id_vegfr FROM survey WHERE name_releve='".$surveyName."'");
        $idVegFr = $sql->fetchAll(PDO::FETCH_COLUMN);


        $bdd->query("DELETE FROM vegetation WHERE id_vegfr='".$idVegFr[0]."'");
        $bdd->query("DELETE FROM int_type WHERE id_vegfr='".$idVegFr[0]."'");
        $bdd->query("DELETE FROM varchar_type WHERE id_vegfr='".$idVegFr[0]."'");
        $bdd->query("DELETE FROM list_parameters WHERE id_vegfr='".$idVegFr[0]."'");
        $bdd->query("DELETE FROM survey WHERE name_releve='".$surveyName."'");

      }

	  //Supprime TOUTE la table survey et les données associées
	  
      public function suppressionSurvey(){
        $sql = $bdd->query("TRUNCATE survey CASCADE;");
      }




    //Initialise le champs GEOM de la BDD qui permet de placer un relevé sur le webmapping
	
	public function initGeom($bdd){
		$query = "UPDATE survey SET geom = ST_SetSRID(ST_Point( deg_lon, deg_lat),4326)";
		$bdd->query($query);
	}
	
}


    ?>
