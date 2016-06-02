<?php

class survey{


  public function survey(){
    include('log_error.php');
    include('int_parameters.php');
    include('varchar_parameters.php');
    include('list_parameters.php');
    ini_set('memory_limit', '-1');
    set_time_limit(0);

  }

  public function initialisationSurveyAll($chemin, $bdd){
    $reponse = false;
    $tabName = $this->getTabNameSurvey($chemin);
    $tableau = $this->initSurvey($chemin, $tabName);
    $tableau = $this->correspondanceSurvey($tableau, $bdd);
    $tableau = $this->intNull($tableau, $bdd);
    $verif = $this->verificationSurvey($tableau, $bdd);
    if($verif){
      $reponse = $this->insertionSurveyAll($tableau, $bdd);
    }
    return $reponse;
  }

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
      $bdd->commit();
      $verif = true;
    }
    catch ( Exception $e )
    {
      $bdd -> rollBack ();
      $log->writeLog($e->getMessage());
      $log->writeLog("INSERTION NON EFFECTUE");
    }
    return $verif;
  }

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
            // echo $tabName[$offset];
            $offset++;

          }
        }
        $row++;

      }
      fclose($handle);
    }
    return $tabName;
  }

  public function initSurvey($nameSurvey, $tabName){

    $log = new log_error();
    $log->resetLog();
    $row = 0;
    $nbr_champs = 0;
    $tableau = array(array());


    if (($handle = fopen($nameSurvey, "r")) !== FALSE) {
      $nbr_lignes = count(file($nameSurvey));
      $nbr_champs = count($tabName);

      while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {



        if($row > 1){
          for ($c=0; $c < $nbr_champs ; $c++) {
            $line = $row - 2;
            $data[$c] = str_replace("'", chr(39), $data[$c]);
            $tableau[$line][$tabName[$c]] = $data[$c];

            //echo $tableau[$line][$tabName[$c]];

          }

        //  echo "<p> $nbr_champs champs à la ligne $line: <br /></p>\n";
        }
        $row++;

      }
      fclose($handle);
    }



    return $tableau;
  }

  public function correspondanceSurvey($tableau, $bdd){

      $sql = $bdd->query("SELECT project FROM dataset");
      $tableauDataset = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_dataset FROM dataset");
      $tableauDatasetId = $sql->fetchAll(PDO::FETCH_COLUMN);

      $sql = $bdd->query("SELECT label FROM protocol");
      $tableauProtocol = $sql->fetchAll(PDO::FETCH_COLUMN);
      $sql = $bdd->query("SELECT id_protocol FROM protocol");
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
  public function verifTypeSurvey($tableau){
    $log = new log_error();
    $error = true;
    for($row = 0 ; $row < count($tableau) ; $row++){
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


  public function verifSurveyObligatoire($tableau){
    $log = new log_error();
    $error = true;
    for($i = 2 ; $i < count($tableau) ; $i++){
      if($tableau[$i]["PROJECT"] == NULL){
        $log->writeLog("ERREUR , Nom du Dataset manquant");
        $error = false;
      }
      if($tableau[$i]["NAME_RELEVE"] == NULL){
        $log->writeLog("ERREUR , Nom du relevé manquant");
        $error = false;
      }
      if($tableau[$i]["COMPLETE"] == NULL){
        $log->writeLog("ERREUR , Completude du relevé manquant");
        $error = false;
      }
      if($tableau[$i]["PROTOCOL"] == NULL){
        $log->writeLog("ERREUR , protocole du relevé manquant");
        $error = false;
      }
      if($tableau[$i]["COVERSCALE"] == NULL){
        $log->writeLog("ERREUR , Echelle d'abondance du relevé manquant");
        $error = false;
      }

    }
    return $error;
  }

  public function insertionSurvey ($tableau, $bdd){

    //Insertion des données dans survey

    $sql = "INSERT INTO survey (id_dataset, name_releve, complete, date_s, 	id_protocol,
      id_coverscale, author, deg_lon,deg_lat, altitude, table_nb, nb_in_table, ref_geo, repetition)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";


      for($row = 0 ; $row < count($tableau) ; $row++){

        $query=$bdd->prepare($sql);
        $query->execute(array(
          $tableau[$row]["PROJECT"],	//id_dataset
          $tableau[$row]["NAME_RELEVE"],	//name_survey
          $tableau[$row]["COMPLETE"],	//complete
          $tableau[$row]["DATE_S"],	//date_s
          $tableau[$row]["PROTOCOL"],	//id_method
          $tableau[$row]["COVERSCALE"],	//id_coverscale
          $tableau[$row]["AUTHOR"],
          $tableau[$row]["DEG_LONG"],	//deg_long
          $tableau[$row]["DEG_LAT"],	//deg_lat
          $tableau[$row]["ALTITUDE"],	//altitude
          $tableau[$row]["TABLE_NR"],	//projection
          $tableau[$row]["NR_IN_TAB"],	//projection
          $tableau[$row]["REF_GEO"],	//projection
          $tableau[$row]["REPETITION"],	//projection

        ));

      }
      $this->insertionLocaAdmin($tableau, $bdd);
    }

    public function insertionLocaAdmin ($tableau, $bdd){

      //Insertion des données dans survey

      $sql = "INSERT INTO local_admin (id_vegfr, country, department, county_name, locality)
        VALUES (?,?,?,?,?)";


        for($row = 0 ; $row < count($tableau) ; $row++){

          $sql2 = $bdd->query("SELECT id_vegfr FROM survey WHERE name_releve = '".$tableau[$row]["NAME_RELEVE"]."'");
          $idVegfr = $sql2->fetchAll(PDO::FETCH_COLUMN);

          $query=$bdd->prepare($sql);
          $query->execute(array(
            $idVegfr[0], //id veg_fr
            "FRANCE", //country
            $tableau[$row]["DEPARTMENT"],
            $tableau[$row]["COUNTY"],
            $tableau[$row]["LOCALITY"],

          ));

        }
      }


      public function deleteSurvey($bdd, $surveyName){


        $sql = $bdd->query("SELECT id_vegfr FROM survey WHERE name_releve='".$surveyName."'");
        $idVegFr = $sql->fetchAll(PDO::FETCH_COLUMN);


        $bdd->query("DELETE FROM vegetation WHERE id_vegfr='".$idVegFr[0]."'");
        $bdd->query("DELETE FROM int_type WHERE id_vegfr='".$idVegFr[0]."'");
        $bdd->query("DELETE FROM varchar_type WHERE id_vegfr='".$idVegFr[0]."'");
        $bdd->query("DELETE FROM list_parameters WHERE id_vegfr='".$idVegFr[0]."'");


        $bdd->query("DELETE FROM survey WHERE name_releve='".$surveyName."'");

      }

      public function suppressionSurvey(){
        $sql = $bdd->query("TRUNCATE survey CASCADE;");
      }




    }

    ?>
