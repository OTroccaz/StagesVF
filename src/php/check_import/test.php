<?php

		include ('PDO.php');
		include('survey.php');
		include('dataset.php');
		include('vegetation.php');
		$bdd = connexion();
		$survey = new survey();
		$dataset = new dataset();
		$vegetation = new vegetation();
		$param = new list_parameters();


//INIT VERIF INSERT VEGETATION---------------------------------------

		// $tableau = $vegetation->initialisationVegetationAll($bdd, "../vegetation_test.csv");
		// $verif = $vegetation->verificationVegetation($tableau, $bdd);
		// if($verif){
		// 		echo "Vérification OK";
		// 		$vegetation->insertionVegetation($tableau, $bdd);
		// }
		//
		//
		// var_dump($tableau);

//-----------------------------------------------------------------------



//INIT VERIF INSERT SURVEY---------------------------------------

//
// $tableau = $survey->initialisationSurveyAll($bdd, "../survey_test.csv");
// $tableau = $survey->initialisationSurveyAll($bdd, "../BD_Orsay_survey.csv");
// $verif = $survey->verificationSurvey($tableau, $bdd);
// if($verif){
// 		$survey->insertionSurveyAll($tableau, $bdd);
// }
//

//--------------------------------------------------------------------


//INIT VERIF INSER DATASET -------------------------------------------

//  $tableau = $dataset->initialisationDatasetAll("../dataset_test.csv");
// $tableau = $dataset->initialisationDatasetAll("../BD_Orsay_dataset.csv");
//  $verif = $dataset->verificationDataset($tableau);
//  if($verif == true){
//   $dataset->insertionDataset($tableau, $bdd);
//   echo "L'insertion a été effectuée";
//  }
//  else{
//   echo "L'insertion n'a pas été effectuée";
//  }

 // $dataset->initialisationDatasetAll("../BD_Orsay_dataset.csv", $bdd);
$survey->initialisationSurveyAll("../BD_Orsay_survey.csv", $bdd)

//----------------------------------------------------------------------

//$dataset->DeleteAll($bdd);
//$dataset->deleteDataset($bdd, "BD Orsay");
//$param->updateListAll($bdd);


	// public function dataset(){

		// $tableau = $initialisation->initDataset();
		// $verif = $verification->verificationDataset($tableau);

		// if($verif == true){
			// $insertion->insertionDataset($tableau, $bdd);
			// echo "L'insertion a été effectuée";
		// }
		// else{
			// echo "L'insertion n'a pas été effectuée";
		// }












	// $corres = new correspondance();

	// $data = "mdr";

	// $val = $corres->corresProtocol($data, $bdd);

	// $data = $val;
	// echo $data;



?>
