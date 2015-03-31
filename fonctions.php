<?php




// Renvoie la première partie de l'année au format complet (2009/2010 ou 2009/10)
function apb_annee($_annee) {
	//$expl = preg_split("/[^0-9]/", $_annee);
	$expl = preg_split("/[^0-9]/", $_annee);
	return $expl[0];
}

function niveauConcernees($annee, $classes) {
	global $mysqli;
	//APB enregistre la fin d'année lsl travaille avec début
	$annee = $annee+1;
	$whereClasse = " AND (";
	$cpt=0;
	foreach ($classes as $key => $classe) {
		if ($cpt!=0) {
			$whereClasse .= " OR ";
		}
		$cpt=1;
		$whereClasse .= " id ='".$key."' ";
	}
	$whereClasse .= ")";
	$sql= "SELECT DISTINCT apb_niveau,annee,annee-1 AS anneelsl FROM `plugin_archAPB_apb_niveau` "
	   . "WHERE annee = ".$annee
	   .$whereClasse;
	$resultchargeDB = $mysqli->query($sql);
	return $resultchargeDB;
}

function classesConcernees($annee, $classes) {
	global $mysqli;
	//APB enregistre la fin d'année lsl travaille avec début
	$annee = $annee+1;
	$whereClasse = " AND (";
	$cpt=0;
	foreach ($classes as $key => $classe) {
		if ($cpt!=0) {
			$whereClasse .= " OR ";
		}
		$cpt=1;
		$whereClasse .= " id ='".$key."' ";
	}
	$whereClasse .= ")";
		
	$sql= "SELECT id,nom_court,nom_complet,login_pp,niveau,annee,annee-1 AS anneelsl,decoupage,id_structure_sconet,libelle_mef,traitee "
	   . "FROM `plugin_archAPB_classes` "
	   . "WHERE annee = ".$annee
	   .$whereClasse;	
	$resultchargeDB = $mysqli->query($sql);
	return $resultchargeDB;
}

function elevesConcernees($annee, $classes) {
	global $mysqli;
	//APB enregistre la fin d'année lsl travaille avec début
	$annee = $annee+1;
	$whereClasse = " AND (";
	$cpt=0;
	foreach ($classes as $key => $classe) {
		if ($cpt!=0) {
			$whereClasse .= " OR ";
		}
		$cpt=1;
		$whereClasse .= " id_classe ='".$key."' ";
	}
	$whereClasse .= ")";
	   
	// on ne selectionne que les élèves qui sont en 1ère ou term cette année
	$sql= "SELECT el.id,el.ine,el.nom,el.prenom,el.ddn,el.annee,el.annee-1 AS anneelsl,el.id_classe "
	   . "FROM `plugin_archAPB_eleves` AS el "
	   . "WHERE el.annee= "
	   .$annee
	   .$whereClasse
	   . " ORDER BY el.nom ASC , el.prenom ASC ,el.id ASC ,el.id_classe ASC , annee ASC ";
	//echo $sql."<br />";
	$resultchargeDB = $mysqli->query($sql);	
	return $resultchargeDB;
}

function anneesEleve($ine) {
	global $mysqli;
	//APB enregistre la fin d'année
	$sql= "SELECT el.annee-1 AS annee , el.id_classe FROM `plugin_archAPB_eleves` AS el "
	   . "WHERE el.ine = '".$ine."' "
	   . "ORDER BY annee DESC ";
	$resultchargeDB = $mysqli->query($sql);
	return $resultchargeDB;	
}

function engagementsEleve($ine) {
	global $mysqli;
	$sql= "SELECT * FROM `plugin_lsl_engagements` WHERE `code_ine` LIKE '".$ine."'";
	$resultchargeDB = $mysqli->query($sql);	
	return $resultchargeDB;	
}

function engagementAutreEleve($ine) {
	global $mysqli;
	$sql= "SELECT * FROM `plugin_lsl_engage_autre` WHERE `code_ine` LIKE '".$ine."'";
	$resultchargeDB = $mysqli->query($sql);	
	return $resultchargeDB;	
}

function avisEleve($ine) {
	global $mysqli;
	$sql= "SELECT * FROM `plugin_lsl_examen` WHERE `code_ine` LIKE '".$ine."'";
	$resultchargeDB = $mysqli->query($sql);	
	return $resultchargeDB;	
}

function avisInvestissement($ine, $annee) {
	global $mysqli;
	$sql= "SELECT * FROM `plugin_lsl_investissement` "
	   . "WHERE `code_ine` LIKE '".$ine."' AND `annee` LIKE '".$annee."'";
	$resultchargeDB = $mysqli->query($sql);	
	return $resultchargeDB;	
}

function codePeriode($ine, $annee) {
	global $mysqli;
	//APB enregistre la fin d'année
	$annee = $annee+1;
	$sql= "SELECT cl.decoupage FROM `plugin_archAPB_classes` AS cl , `plugin_archAPB_eleves` AS elv "
	   . "WHERE elv.ine LIKE '".$ine."' AND cl.annee LIKE '".$annee."' AND elv.id_classe LIKE cl.id";
	$resultchargeDB = $mysqli->query($sql);	
	if ($resultchargeDB->num_rows) {
		$result = $resultchargeDB->fetch_object();
		$resultchargeDB->close();
		if ($result->decoupage == 2) {
		return "S";
		} elseif ($result->decoupage == 3) {
			return "T";
		}		
	}	
	return "";	
	
}
function evaluations($ine, $annee) {
	// il faut récupérer par matière et pas par période
	global $mysqli;
	//APB enregistre la fin d'année
	$annee = $annee+1;
	$sql= "SELECT DISTINCT `n`.code_service , `n`.annee , m.code_sconet , m.modalite , m.libelle_sconet "
	   . " FROM `plugin_archAPB_notes` n , `plugin_archAPB_matieres` m "
	   . "WHERE `n`.`ine` LIKE '".$ine."' AND `n`.`annee` =".$annee." AND m.id_gepi=`n`.code_service";
	$resultchargeDB = $mysqli->query($sql);		
	return $resultchargeDB;	
}

function getNiveau($annee, $classe) {
	global $mysqli;
	//APB enregistre la fin d'année
	$annee = $annee+1;
	$sql= "SELECT * FROM `plugin_archAPB_apb_niveau` WHERE `id` =".$classe." AND `annee` = ".$annee." " ;
	$resultchargeDB = $mysqli->query($sql);		
	//echo $sql;
	$chargeDB = '';	
	
	return $resultchargeDB;	
}

function structureEval($annee, $code_service) {
	global $mysqli;
	//APB enregistre la fin d'année
	$annee = $annee+1;
	$sql= "SELECT AVG(moyenne) AS moyenne FROM `plugin_archAPB_notes` "
	   . "WHERE code_service != 'MOYGEN' AND code_service = '".$code_service."' AND annee = '".$annee."' AND etat = 'S' ";
	$resultchargeDB = $mysqli->query($sql);		
	//echo $sql;
	return $resultchargeDB;	
}

function compteElvEval($annee, $code_service) {
	global $mysqli;
	//APB enregistre la fin d'année
	$annee = $annee+1;
	$sql= "SELECT COUNT(DISTINCT ine) AS nombre FROM `plugin_archAPB_notes` "
	   . "WHERE code_service = '".$code_service."' AND annee = '".$annee."' AND etat = 'S' ";
	$resultchargeDB = $mysqli->query($sql);		
	//echo "<br />".$sql;
	return $resultchargeDB;	
}

function reparMoinsHuit($annee, $code_service, $totalEleve , $min = 0 , $max = 8) {
	global $mysqli;
	//APB enregistre la fin d'année
	$annee = $annee+1;
	$sql= "SELECT COUNT(DISTINCT ine) AS nombre FROM `plugin_archAPB_notes` "
	   . "WHERE code_service = '".$code_service."' AND annee = '".$annee."' AND etat = 'S' "
	   . " AND moyenne >= ".$min." AND moyenne < ".$max;
	if ($totalEleve) {
		$resultchargeDB = $mysqli->query($sql);
		$result = round($resultchargeDB->fetch_object()->nombre / $totalEleve * 100, 2);
	} else {
		$result = 0;
	}
	$resultchargeDB->close();	
	//echo "<br />".$sql;
	return $result;	
}
	
function moyenneTrimestre($annee, $code_service, $ine) {
	global $mysqli;
	//APB enregistre la fin d'année
	$annee = $annee+1;
	$sql= "SELECT * FROM `plugin_archAPB_notes` "
	   . "WHERE code_service = '".$code_service."' AND annee = '".$annee."' AND ine = '".$ine."'";	
	$resultchargeDB = $mysqli->query($sql);		
	//echo "<br />".$sql;
	return $resultchargeDB;		
}
	
function enseignants($annee, $code_service) {
	global $mysqli;
	//APB enregistre la fin d'année
	$annee = $annee+1;
	$sql= "SELECT mat.id_gepi , mat.login_prof  , pf.nom , pf.prenom "
	   . " FROM `plugin_archAPB_matieres` AS mat , `plugin_archAPB_profs` AS pf "
	   . " WHERE mat.id_gepi = '".$code_service."' AND mat.annee = '".$annee."'"
	   . " AND pf.login = mat.login_prof ";
	$resultchargeDB = $mysqli->query($sql);		
	//echo "<br />".$sql;
	return $resultchargeDB;	
}

function APBinstalle() {
	global $mysqli;
	$sql = "SELECT * FROM `plugins` WHERE nom = 'archivageAPB'";
	$resultchargeDB = $mysqli->query($sql);		
	//echo "<br />".$sql;
	return $resultchargeDB;	
}

function extraitCompetences() {
	global $mysqli;
	$sql = "SELECT * FROM `plugin_lsl_competences` ";
	$resultchargeDB = $mysqli->query($sql);		
	//echo "<br />".$sql;
	return $resultchargeDB;		
}

function extraitClasses($anneeSolaire) {
	global $mysqli;
	$anneeAPB = $anneeSolaire+1;
	$sql = "SELECT * FROM `plugin_archAPB_classes` WHERE annee='".$anneeAPB."'";
	$resultchargeDB = $mysqli->query($sql);		
	//echo "<br />".$sql;
	return $resultchargeDB;		
}

function chercheClassesProf($login, $annee = NULL) {
	//$login="fcoutaud";
	global $mysqli;
	// `plugin_archAPB_profs` login →
	// login_prof → `plugin_archAPB_matieres` → id_gepi
	// code_service → `plugin_archAPB_notes` → ine
	// ine → `plugin_archAPB_eleves` → id_classe 
	// id → `plugin_archAPB_classes`
	
	
	$sql = "SELECT DISTINCT c.* "
	   . "FROM `plugin_archAPB_classes` AS c , plugin_archAPB_eleves AS e, `plugin_archAPB_notes` AS n, `plugin_archAPB_matieres` AS m, `plugin_archAPB_profs` AS p "
	   . "WHERE c.id = e.id_classe "
	   . "AND e.ine = n.ine "
	   . "AND n.code_service = m.id_gepi "
	   . "AND m.login_prof = '".$login."' ";
	if ($annee) {
		$sql .= "AND c.annee= '".$annee."' ";
	}
	
	$sql = "SELECT DISTINCT c.* "
	   . "FROM `plugin_archAPB_classes` AS c ";
	$sql .= "INNER JOIN `plugin_archAPB_eleves` AS e ON c.id = e.id_classe ";
	$sql .= "INNER JOIN `plugin_archAPB_notes` AS n ON n.ine = e.ine  ";
	$sql .= "INNER JOIN `plugin_archAPB_matieres` AS m ON n.code_service = m.id_gepi ";
	$sql .= "WHERE m.login_prof = '".$login."' ";
	if ($annee) {
		$sql .= "AND c.annee= '".$annee."' ";
	}
	
	
	$sql = "SELECT DISTINCT m.* "
	   . "FROM `plugin_archAPB_matieres` AS m ";
	$sql .= "INNER JOIN `plugin_archAPB_notes` AS n ON m.id_gepi = n.code_service  ";
	
	
	$sql .= "WHERE m.login_prof = '".$login."' ";
	if ($annee) {
		$sql .= "AND m.annee= '".$annee."' ";
	}
	
	
		
	echo "<br />".$sql."<br />";	
	
	$resultchargeDB = $mysqli->query($sql);	
	return $resultchargeDB;		
}

