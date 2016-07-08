<?php	

		include('survey.php');
		include ('../../../config/connection.php');
		$bdd = connexionPgSQL();
		$survey = new survey();
		$row = $_GET["row"];
		$chemin = $_GET["chemin"];
		$verif = $survey->initialisationSurveyAll($chemin ,$bdd, $row);
?>