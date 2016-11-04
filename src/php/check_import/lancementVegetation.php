<?php	
	//Fonction permettant de relancer la fonction d'insertion des données de végétation avec les bons paramètres
		include('vegetation.php');
		include ('../../../config/connection.php');
		$bdd = connexionPgSQL();
		$vegetation = new vegetation();
		$Nbr_row = $_GET["row"];
		$chemin = $_GET["chemin"];
		$verif = $vegetation->initialisationVegetationAll($chemin ,$bdd, $Nbr_row);
?>