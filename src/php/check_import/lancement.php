<?php	
	//Fonction permettant de relancer la fonction d'insertion des relevés avec les bons paramètres
		include('survey.php');
		include ('../../../config/connection.php');
		$bdd = connexionPgSQL();
		$survey = new survey();
		$row = $_GET["row"];
		$chemin = $_GET["chemin"];
		$verif = $survey->initialisationSurveyAll($chemin ,$bdd, $row);
?>