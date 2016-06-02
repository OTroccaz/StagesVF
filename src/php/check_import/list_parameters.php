<?php

class list_parameters{

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

  public function verifList($tableau, $bdd){
    $log = new log_error();
    $error = true;
    $sql = $bdd->query("SELECT label FROM list_lists");
    $champsList = $sql->fetchAll(PDO::FETCH_COLUMN);

    for($champs = 0 ; $champs < count($champsList) ; $champs++){
      for($row = 0; $row < count($tableau) ; $row++){
        if(is_numeric($tableau[$row][$champsList[$champs]]) || $tableau[$row][$champsList[$champs]] == NULL){
        }
        else{
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
						//'species',
						'stratum',
						'temperatur',
						//'type',
						'typus',
						'unit' );

	for($row = 0 ; $row < count($listArray) ; $row++){
		$this->updateList($listArray[$row], $bdd);
	}



}
public function updateList($listName, $bdd){
  $listKeys = array();
  $listInsert = array(array());
  $ColumnName = array();
  $list = array(array());
  $row = 0;
  $nbr_lignes = 0;

  if (($handle = fopen("../../../List_CSV/list_".$listName.".csv", "r")) !== FALSE) {
    $nbr_lignes = count(file("../../../List_CSV/list_".$listName.".csv"));
    $ColumnName = fgetcsv($handle, 1000, ";");
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
      $nbr_champs = count($data);
        for($i = 0 ; $i < $nbr_champs ; $i++){
          $data[$i] = str_replace("'", "&apos;", $data[$i]);
          $list[$ColumnName[$i]][$row] = $data[$i];
        }

      $row++;

    }
    fclose($handle);
  }
  $sql = $bdd->query("SELECT label FROM list_".$listName);
  $listBdd = $sql->fetchAll(PDO::FETCH_COLUMN);

  $listDiff = array_diff_assoc($list["label"], $listBdd);
  var_dump($listDiff);
  $listKeys = array_keys($listDiff);

  for($t = 0 ; $t < count($listDiff) ; $t++){

    $sqlInsert = "INSERT INTO list_".$listName." (";
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
      $bdd->exec($sqlInsert);



    }

}



}

?>
