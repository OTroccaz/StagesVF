<?php

//classe contenant les fonctions de vérification et d'insertion pour les champs facultatif de type list

class list_parameters{



	// Cette fonction permet d'inserer les données ayant passé la vérification dans la BDD

  public function insertionListParam ($tableau, $bdd){

    $sql = $bdd->query("SELECT label FROM list_lists");
    $champsList = $sql->fetchAll(PDO::FETCH_COLUMN);

    $sql = $bdd->query("SELECT id_name FROM list_lists");
    $champsListCorres = $sql->fetchAll(PDO::FETCH_COLUMN);

    for($champs = 0 ; $champs < count($champsList) ; $champs++){


      $sqlInsert = "INSERT INTO list_parameters (id_vegfr, id_liste_name, id_value) VALUES (?,?,?)";


        for($row = 0 ; $row < count($tableau) ; $row++){

          $sql = $bdd->query("SELECT id_vegfr FROM survey WHERE name_releve = '".$tableau[$row]["NAME_RELEVE"]."'");
          $idVegfr = $sql->fetchAll(PDO::FETCH_COLUMN);

          if($tableau[$row][$champsList[$champs]] != NULL){

            $query=$bdd->prepare($sqlInsert);
            $query->execute(array(
              $idVegfr[0],
              $champsListCorres[$champs],
              $tableau[$row][$champsList[$champs]],
            ));
          }

      }

  }

  }

    // Cette fonction permet de vérifier que toutes les données sont bien des integer
  
  public function verifList($tableau, $bdd){
    $log = new log_error();
    $error = true;
    $sql = $bdd->query("SELECT label FROM list_lists");
    $champsList = $sql->fetchAll(PDO::FETCH_COLUMN);

    for($champs = 0 ; $champs < count($champsList) ; $champs++){
      for($row = 0; $row < count($tableau) ; $row++){
        if(is_numeric($tableau[$row][$champsList[$champs]]) || ($tableau[$row][$champsList[$champs]] == NULL)){
        }
        else{
			echo $tableau[$row][$champsList[$champs]];
			echo $row;
			echo "ERRUR";
            $log->writeLog("ERREUR, LA DONNEE N'EST PAS UN INT , LIGNE : ".$row." / COLONNE : ".$champsList[$champs]);
          $error = false;
        }
      }
    }
    return $error;
  }

public function updateListAll($bdd){
	$listArray = array('cah_hab',
						'catminat',
						'clc',
						'corine',
						'coverscale',
						'eunis',
						'exposure',
						'geol_subst',
						'hydro',
						'lighting',
						'loca_metho',
						'management',
						'n2k',
						'nat_region',
						'nutrient',
						'pedology',
						'protocol',
						'pvf1',
						'pvf2',
						'regime',
						'salinity',
						'sampling',
						'soil_ph',
						'stratum',
						'temperatur',
						'typus',
						'unit' );
	echo count($listArray);
	for($row = 0 ; $row < count($listArray) ; $row++){
		$this->updateList($listArray[$row], $bdd);
		echo $listArray[$row];
	}



}

	// Permet de comparer les listes présentes dans la BDD avec celle d'un fichier CSV
	// Insère les différences constaté dans la BDD

public function updateList($listName, $bdd){
  $listKeys = array();
  $listInsert = array(array());
  $ColumnName = array();
  $list = array(array());
  $line = 0;
  $nbr_lignes = 0;

  if (($handle = fopen("../../List_CSV/".$listName, "r")) !== FALSE) {
    $nbr_lignes = count(file("../../List_CSV/".$listName));
    $ColumnName = fgetcsv($handle, 1000, ";");
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
      $nbr_champs = count($data);
        for($i = 0 ; $i < $nbr_champs ; $i++){
          $data[$i] = str_replace("'", "&apos;", $data[$i]);
          $list[$ColumnName[$i]][$line] = $data[$i];
        }

      $line++;

    }
    fclose($handle);
  }
  $name = substr($listName, 0, -4);
  
  $sql = $bdd->query("SELECT label FROM ".$name);
  $listBdd = $sql->fetchAll(PDO::FETCH_COLUMN);
  $listDiff = array_diff_assoc($list["label"], $listBdd);
  $listKeys = array_keys($listDiff);

	$nbrDiff = count($listDiff);
  for($t = 0 ; $t < $nbrDiff ; $t++){
	echo "T : ".$t;
    $sqlInsert = "INSERT INTO ".$name." (";
      for($b = 0 ; $b < count($ColumnName) ; $b++ ){
        $sqlInsert .= $ColumnName[$b];
        if($b != count($ColumnName)-1)$sqlInsert .= " , ";
      }
      $sqlInsert .= ") VALUES (";
      for($c = 0 ; $c < count($ColumnName) ; $c++ ){
        $sqlInsert .= "'".$list[$ColumnName[$c]][$listKeys[$t]]."'";
        if($c != count($ColumnName)-1)$sqlInsert .= " , ";
      }
      $sqlInsert .= ")";
	  echo $sqlInsert;
      $bdd->exec($sqlInsert);
	  
			
	
    }

}



	//Permet de mettre à jour la liste TaxRef à partir d'un fichier CSV
	
public function updateListTaxRef($bdd){
	  set_time_limit(0);
	ini_set('memory_limit', '256M');
  $listKeys = array();
  $listInsert = array(array());
  $ColumnName = array();
  $list = array(array());
  $line = 0;
  $nbr_lignes = 0;
  
  for($nombreFichier = 1 ; $nombreFichier < 4 ; $nombreFichier++){
	  
		  if (($handle = fopen("C:/wamp/www/Test/List_CSV/taxref9_".$nombreFichier.".csv", "r")) !== FALSE) {
			$nbr_lignes = count(file("C:/wamp/www/Test/List_CSV/taxref9_".$nombreFichier.".csv"));
			$ColumnName = fgetcsv($handle, 1000, ";");
			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
			  $nbr_champs = count($data);
				for($i = 0 ; $i < $nbr_champs ; $i++){
				  $data[$i] = str_replace("'", "&apos;", $data[$i]);

				  $list[$line][$ColumnName[$i]] = $data[$i];
				}

			  $line++;

			}
			fclose($handle);
		  }

		for($row = 0 ; $row < count($list) ; $row++){
			for($i = 0 ; $i < count($ColumnName) ; $i++){
				if( $list[$row][$ColumnName[$i]] == NULL){
					$list[$row][$ColumnName[$i]] = NULL;
			}
		}
  
  
		  for($t = 0 ; $t < count($list) ; $t++){

			$sqlInsert = "INSERT INTO taxref_9 (";
			  for($b = 0 ; $b < count($ColumnName) ; $b++ ){
				$sqlInsert .= $ColumnName[$b];
				if($b != count($ColumnName)-1)$sqlInsert .= " , ";
			  }
			  $sqlInsert .= ") VALUES (";
			  for($c = 0 ; $c < count($ColumnName) ; $c++ ){
				  if($list[$t][$ColumnName[$c]] == NULL){
					  $sqlInsert .= "null";
					  if($c != count($ColumnName)-1)$sqlInsert .= ",";
				  }
				  else{
					$sqlInsert .= "'".$list[$t][$ColumnName[$c]]."'";
					if($c != count($ColumnName)-1)$sqlInsert .= " , ";
				  }

			  }
			  $sqlInsert .= ")";
			  $bdd->exec($sqlInsert);
			  
					
			
			}

		}

	  
  }



}
}
?>
