<?php	

		include('vegetation.php');
		include ('../../../config/connection.php');
		$bdd = connexionPgSQL();
		$vegetation = new vegetation();
		$row = $_GET["row"];
		$chemin = $_GET["chemin"];
		$verif = $vegetation->initialisationVegetationAll($chemin ,$bdd, $row);
?>