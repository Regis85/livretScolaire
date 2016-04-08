<?php

/**
 * Renvoie la première partie de l'année au format complet (2009 pour 2009/2010 ou 2009/10 ou 2009-2010 …)
 * 
 * @param String $_annee L'année scolaire
 * @return String
 */
function lsl_annee($_annee) {
	//$expl = preg_split("/[^0-9]/", $_annee);
	$expl = preg_split("/[^0-9]/", $_annee);
	return $expl[0];
}

/**
 * Recherche dans APB les niveaux concernés par le livret
 * 
 * @global object $mysqli
 * @param String $annee L'année dans LSL
 * @param array() $classes les classes concernées
 * @return mysqli_result le résultat de la requête
 */
function niveauConcernees($annee, $classes) {
    global $mysqli;
    $annee1 = $annee+1;
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
       . "WHERE annee = ".$annee1
       .$whereClasse;
    ecrit("Niveau concerné ".$sql."\n");
    $resultchargeDB = $mysqli->query($sql);
    return $resultchargeDB;
}

/**
 * Recherche dans APB le niveau d'une classe
 * 
 * @global object $mysqli
 * @param String $annee L'année dans LSL
 * @param String $classe la classes concernée
 * @return mysqli_result le résultat de la requête
 */
function getNiveau($annee, $classe) {
    global $mysqli;
    $annee = $annee+1;
    $sql= "SELECT * FROM `plugin_archAPB_apb_niveau` WHERE `id` =".$classe." AND `annee` = ".$annee." " ;
    $resultchargeDB = $mysqli->query($sql);		
    //echo $sql;	
    ecrit("Niveau de la classe ".$classe." en ".$annee." dans APB \n".$sql."\n");
    return $resultchargeDB;	
}

/**
 * Recherche dans APB les données des classes concernées par le livret
 * 
 * @global object $mysqli
 * @param String $annee l'année LSL concernée
 * @param array() $classes les classes concernées
 * @return mysqli_result le résultat de la requête
 */
function classesConcernees($annee, $classes) {
	global $mysqli;
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
	//echo "<br />".$sql;
	$resultchargeDB = $mysqli->query($sql);
	return $resultchargeDB;
}

/**
 * 
 * @global object $mysqli
 * @param String $annee l'année LSL concernée
 * @param array() $classes les classes concernées
 * @return mysqli_result le résultat de la requête
 */
function elevesConcernees($annee, $classes) {
	global $mysqli;
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
	$sql= "SELECT el.id,el.ine,el.nom,el.prenom,el.ddn,el.annee,el.annee-1 AS anneelsl,el.id_classe,m.code_mef "
	   . "FROM `plugin_archAPB_eleves` AS el "
	   . "INNER JOIN plugin_archAPB_eleves_mef AS m ON (el.ine = m.no_gep AND el.annee = m.annee )"
	   . "WHERE el.annee= "
	   .$annee
	   .$whereClasse
	   . " ORDER BY el.nom ASC , el.prenom ASC ,el.id ASC ,el.id_classe ASC , annee ASC ";
	//echo $sql."<br />";
	$resultchargeDB = $mysqli->query($sql);	
	return $resultchargeDB;
}

/**
 * renvoie les données de plugin_archAPB_eleves
 * 
 * @global object $mysqli
 * @param string $ine INE de l'élève
 * @return mysqli_result le résultat de la requête
 */
function anneesEleve($ine) {
	global $mysqli;
	$sql= "SELECT el.annee -1 AS annee, el.id_classe, c.nom_court , c.nom_complet , c.login_pp , c.niveau , m.code_mef "
	   . "FROM `plugin_archAPB_eleves` AS el "
	   . "INNER JOIN `plugin_archAPB_classes` AS c "
	   . "ON (c.annee = el.annee AND c.id = el.id_classe) "
	   . "INNER JOIN `plugin_archAPB_eleves_mef` AS m "
	   . "ON (m.annee = el.annee AND m.no_gep = el.ine) "
	   . "WHERE el.ine = '".$ine."'  "
	   . "ORDER BY annee DESC ";
	//echo $sql."<br />";
	$resultchargeDB = $mysqli->query($sql);
	return $resultchargeDB;	
}

/**
 * Retourne les engagements d'un élève
 * 
 * @global object $mysqli connexion à la base
 * @param text $ine INE de l'élève
 * @return mysqli_result les engagements trouvés pour l'élève
 */
function engagementsEleve($ine) {
	global $mysqli;
	$sql= "SELECT en.* , eg.code , eg.description ,  ev.`no_gep` FROM `engagements_user` AS en "
	   . "INNER JOIN `engagements` AS eg ON eg.`id` = en.`id_engagement` "
	   . "INNER JOIN `eleves` AS ev ON ev.`login` = en.`login` "
	   . " WHERE ev.`no_gep` LIKE '".$ine."' ";		
	//echo "<br />".$sql." ";
	$resultchargeDB = $mysqli->query($sql);	
	return $resultchargeDB;	
}

/**
 * Retourne les avis pour l'examen
 * 
 * @global object $mysqli
 * @param String $ine INE de l'élàve
 * @return mysqli_result Les avis pour l'examen
 */
function avisEleve($ine) {
	global $mysqli;
	$sql= "SELECT * FROM `plugin_lsl_examen` WHERE `code_ine` LIKE '".$ine."'";
	$resultchargeDB = $mysqli->query($sql);	
	return $resultchargeDB;	
}

/**
 * Les investissements de l'élève au sein de l'établissement
 * 
 * @global object $mysqli
 * @param String $ine INE de l'élàve
 * @param String $annee LSL de l'investissements
 * @return mysqli_result Les investissements de l'élève
 */
function avisInvestissement($ine, $annee) {
	global $mysqli;
	$sql= "SELECT * FROM `plugin_lsl_investissement` "
	   . "WHERE `code_ine` LIKE '".$ine."' AND `annee` LIKE '".$annee."'";
	$resultchargeDB = $mysqli->query($sql);	
	return $resultchargeDB;	
}

/**
 * Retourne le code du découpage de l'année
 * 
 * S → semestre
 * T → trimestre
 * @global object $mysqli
 * @param String $ine INE de l'élève
 * @param String $annee année LSL de la période
 * @return string
 */
function codePeriode($ine, $annee) {
	global $mysqli;
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

/**
 * Retourne les évaluations d'un élève pour une année
 * 
 * @global object $mysqli
 * @param String $ine INE de l'élève
 * @param String $annee année LSL de la période
 * @return mysqli_result Les évaluations de l'élève
 */
function evaluations($ine, $annee) {
	global $mysqli;
	$annee = $annee+1;
	$sql= "SELECT DISTINCT n.code_service , n.ine , n.etat , n.moyenne , n.trimestre , n.annee , n.appreciation , "
	   . "m.code_sconet , m.libelle_sconet , m.modalite , login_prof , e.code_mef "
	   . "FROM `plugin_archAPB_notes` AS n "
	   . "INNER JOIN `plugin_archAPB_eleves_mef` AS e "
	   . "ON (e.no_gep = n.ine AND e.annee = n.annee) "
	   . "INNER JOIN `plugin_archAPB_matieres` AS m "
	   . "ON (m.id_gepi = n.code_service AND m.annee = n.annee) "
	   . "WHERE n.ine = '".$ine."' AND n.annee = '".$annee."' "
	   . "ORDER BY m.code_sconet ASC , n.trimestre ASC";
	//echo "<br />"."<br />".$sql;
	$resultchargeDB = $mysqli->query($sql);	
	return $resultchargeDB;		
}

/**
 * Moyenne de l'enseignement dans plugin_APB
 * 
 * @global object $mysqli
 * @param string $anneeLSL année LSL sur 4 Chiffres
 * @param string $code_service code du service dans le plugin APB
 * @return mysql_query
 */
function structureEval($anneeLSL, $code_service) {
	global $mysqli;
	//APB enregistre la fin d'année
	$annee = $anneeLSL+1;
	$sql= "SELECT AVG(moyenne) AS moyenne FROM `plugin_archAPB_notes` "
	   . "WHERE code_service != 'MOYGEN' AND code_service = '".$code_service."' AND annee = '".$annee."' AND etat = 'S' ";
	$resultchargeDB = $mysqli->query($sql);		
	//echo $sql;
	return $resultchargeDB;	
}

/**
 * Compte le nombre d'élèves notés dans l'enseignement
 * 
 * @global object $mysqli
 * @param type $anneeLSL année LSL sur 4 Chiffres
 * @param type $code_service code du service dans le plugin APB
 * @return  mysql_query
 */
function compteElvEval($anneeLSL, $code_service) {
	global $mysqli;
	//APB enregistre la fin d'année
	$annee = $anneeLSL+1;
	$sql= "SELECT COUNT(DISTINCT ine) AS nombre FROM `plugin_archAPB_notes` "
	   . "WHERE code_service = '".$code_service."' AND annee = '".$annee."' AND etat = 'S' ";
	$resultchargeDB = $mysqli->query($sql);		
	//echo "<br />".$sql;
	return $resultchargeDB;	
}

/**
 * On ne tient pas compte des élèves non notés qui ont 0. 
 * 
 * Pour tenir compte des dispensés qui ont 0, il faut ajouter un filtre etat = 'D' 
 * Pour tenir compte des absents… qui ont 0, il faut ajouter un filtre etat = 'N' 
 * 
 * @global object $mysqli
 * @param string $anneeLSL année LSL sur 4 Chiffres
 * @param string $code_service code du service dans le plugin APB
 * @param int $min
 * @param int $max
 * @return int
 */
function reparMoinsHuit($anneeLSL, $code_service, $min = 0 , $max = 8) {
	global $mysqli;
	$annee = $anneeLSL+1;
	
	$sql1= "SELECT COUNT(DISTINCT ine) AS nombre FROM ( SELECT AVG(n.`moyenne`) AS moyennes , ine "
	   . "FROM `plugin_archAPB_notes` n "
	   . "WHERE n.`annee` = '".$annee."' AND n.`code_service` = '".$code_service."' AND etat = 'S'  "
	   . "GROUP BY n.`ine` "
	   . ") as P ";
	$resultchargeDB1 = $mysqli->query($sql1);
	$totalEleve = $resultchargeDB1->fetch_object()->nombre;
	$resultchargeDB1->close();	
	
	$sql= "SELECT COUNT(DISTINCT ine) AS nombre FROM ( SELECT AVG(n.`moyenne`) AS moyennes , ine "
	   . "FROM `plugin_archAPB_notes` n "
	   . "WHERE n.`annee` = '".$annee."' AND n.`code_service` = '".$code_service."' AND etat = 'S'  "
	   . "GROUP BY n.`ine` "
	   . ") as P "
	   . "WHERE P.moyennes >= ".$min." AND P.moyennes < ".$max;
	if ($totalEleve) {
		$resultchargeDB = $mysqli->query($sql);
		$result = round($resultchargeDB->fetch_object()->nombre / $totalEleve * 100, 2);
	} else {
		$result = 0;
	}
	$resultchargeDB->close();	
	// echo "<br />".$sql;
	return $result;	
}

/**
 * 
 * @global object $mysqli
 * @param string $anneeLSL année LSL sur 4 Chiffres
 * @param string $code_service code du service dans le plugin APB
 * @param string $ine INE de l'élève
 * @return type
 */
function moyenneTrimestre($anneeLSL, $code_service, $ine) {
	global $mysqli;
	//APB enregistre la fin d'année
	$annee = $anneeLSL+1;
	$sql= "SELECT * FROM `plugin_archAPB_notes` "
	   . "WHERE code_service = '".$code_service."' AND annee = '".$annee."' AND ine = '".$ine."'";	
	$resultchargeDB = $mysqli->query($sql);		
	//echo "<br />".$sql." → ".$resultchargeDB->num_rows;	
	return $resultchargeDB;		
}

/**
 * Renvoie les enseignants d'un groupe
 * 
 * on recherche les profs dans GEPI pour l'année actuelle (plusieurs possibles) 
 * et dans le plugin_APB pour les années antérieures (un seul enseignant)
 * $code service = archivage discipline.id_groupe
 * 
 * @global object $mysqli
 * @param string $anneeLSL année LSL sur 4 Chiffres
 * @param string $code_service code du service dans le plugin APB
 * @return mysql_query
 */
function Enseignants($anneeLSL, $code_service) {
    global $mysqli;
    //APB enregistre la fin d'année
    $annee = $anneeLSL+1;
    // on recherche les profs dans GEPI pour l'année actuelle (plusieurs possibles)
    //  et dans le plugin_APB pour les années antérieures (un seul enseignant)
    $sql= "SELECT DISTINCT gp.login as login , pf.nom , pf.prenom "
            . "FROM j_groupes_professeurs AS gp "
            . "INNER JOIN plugin_archAPB_profs AS pf "
            . "ON pf.login = gp.login "
            . "WHERE gp.id_groupe = '".$code_service."' "
            . "UNION "
            . "SELECT DISTINCT mat.login_prof as login, pf.nom , pf.prenom "
            . "FROM `plugin_archAPB_matieres` AS mat "
            . "INNER JOIN plugin_archAPB_profs AS pf "
            . "ON pf.login = mat.login_prof "
            . "WHERE mat.id_gepi= '".$code_service."' ";

    $resultchargeDB = $mysqli->query($sql);		
    //echo "<br />".$sql;

    return $resultchargeDB;	
}

function getEnseignantsPassees($anneeLSL, $code_service, $enseignants) {
    global $mysqli;
    global $newEnseignants;
    $anneeArchive = $anneeLSL."/".($anneeLSL+1);
    $sql= "SELECT DISTINCT nom_prof , prenom_prof FROM `archivage_disciplines` "
            . "WHERE `id_groupe` = '".$code_service."' AND `annee` = '".$anneeArchive."' ";	
    //echo "<br />".$sql;
    $resultchargeDB = $mysqli->query($sql);
    $retour = FALSE;
    while ($profs = $resultchargeDB->fetch_object()){
        ecrit("Enseignants → ".$profs->nom_prof." ".$profs->prenom_prof."\n");
        $nomEnseignants = explode("|", $profs->nom_prof);
        $prenomEnseignants = explode("|", $profs->prenom_prof);
        $nbProfs=  count($nomEnseignants);
        for ($i = 0; $i < $nbProfs ; $i++) {
            /*
            $retour[$i]['nom'] = $nomEnseignants[$i];
            $retour[$i]['prenom'] = $prenomEnseignants[$i];
             * 
             */                  
            /*
            CreeNoeudProf ($nomEnseignants[$i],$prenomEnseignants[$i]);
             * 
             */
            /* */
            $enseignant = $newEnseignants->addChild('enseignant');
            $enseignant->addAttribute('nom', substr($nomEnseignants[$i], 0,65));
            $enseignant->addAttribute('prenom', substr($prenomEnseignants[$i], 0,50));
            /* 
            */
        }
        $retour = TRUE;
    }
    return $retour;
}
function CreeNoeudProf ($nom, $prenom) {
    global $newEnseignants;
    $enseignant = $newEnseignants->addChild('enseignant');
    $enseignant->addAttribute('nom', substr($nom, 0,65));
    $enseignant->addAttribute('prenom', substr($prenom, 0,50));
    
}

/**
 * Vérifie si le plugin APB est installé
 * 
 * @global object $mysqli
 * @return mysql_query
 */
function APBinstalle() {
	global $mysqli;
	//$sql = "SELECT * FROM `plugins` WHERE nom = 'archivageAPB'";
	$sql = "SHOW TABLES LIKE 'plugin_archAPB_classes'";
	$resultchargeDB = $mysqli->query($sql);		
	//echo "<br />".$sql;
	return $resultchargeDB;	
}

/**
 * 
 * @global object $mysqli
 * @return mysql_query
 */
function extraitCompetences() {
	global $mysqli;
	$sql = "SELECT * FROM `plugin_lsl_competences` ";
	$resultchargeDB = $mysqli->query($sql);		
	//echo "<br />".$sql;
	return $resultchargeDB;		
}

/**
 * Extrait les classes présentes dans APB à partir d'une année
 * 
 * @global object $mysqli connexion à la base
 * @param text $anneeSolaire année sur 4 caractères
 * @return object enregistrements de plugin_archAPB_classes
 */
function extraitClasses($anneeSolaire) {
	global $mysqli;
	$anneeAPB = $anneeSolaire+1;
	$sql = "SELECT * FROM `plugin_archAPB_classes` WHERE annee='".$anneeAPB."'";
	$resultchargeDB = $mysqli->query($sql);		
	//echo "<br />".$sql;
	return $resultchargeDB;		
}

/**
 * Extrait les classes présentes dans APB à partir d'une année, pour un prof
 * 
 * @global object $mysqli connexion à la base
 * @param text $login login de l'enseignant, % pour tous
 * @param text $annee année sur 4 caractères
 * @return object enregistrements de plugin_archAPB_classes
 */
function chercheClassesProf($login, $annee = NULL) {
	//$login="fcoutaud";
	//$login="%";
	global $mysqli;
	$sql = "SELECT DISTINCT c.* "
	   . "FROM `plugin_archAPB_classes` AS c ";
	$sql .= "INNER JOIN `plugin_archAPB_eleves` AS e ON (c.id = e.id_classe AND c.annee = e.annee) ";
	$sql .= "INNER JOIN `plugin_archAPB_notes` AS n ON (n.ine = e.ine AND n.annee  = e.annee) ";
	$sql .= "INNER JOIN `plugin_archAPB_matieres` AS m ON (n.code_service = m.id_gepi AND m.annee  = n.annee) ";
	$sql .= "WHERE m.login_prof LIKE '".$login."' ";
	if ($annee) {
		$sql .= "AND c.annee LIKE '".$annee."' ";
	}
	//echo "<br />".$sql."<br />";
	$resultchargeDB = $mysqli->query($sql);	
	return $resultchargeDB;		
}

/**
 * 
 * @global object $mysqli connexion à la base
 * @param array $classesChoisies
 * @param text $login
 * @return object
 */
function chercheElevesProf($classesChoisies, $login, $annee) {	
	global $mysqli;
	$sql = "SELECT DISTINCT e.* "
	   . "FROM `plugin_archAPB_eleves` AS e ";
	$sql .= "INNER JOIN `plugin_archAPB_classes` AS c ON (c.id = e.id_classe AND c.annee = e.annee) ";
	$sql .= "INNER JOIN `plugin_archAPB_notes` AS n ON (n.ine = e.ine AND n.annee  = e.annee) ";
	$sql .= "INNER JOIN `plugin_archAPB_matieres` AS m ON (n.code_service = m.id_gepi AND m.annee  = n.annee) ";
	$sql .= "WHERE m.login_prof LIKE '".$login."' ";
	$sql .= "AND n.annee = '".$annee."' ";
	$sql .= "AND (";
	$join= "";
	//while ($classeActive = $classesChoisies->fetch_object()) {	
	foreach ($classesChoisies as $key=>$classeActive) {
		$sql .= $join."e.id_classe  = ".$key." ";
		$join= "OR ";
	}
	$sql .= ") ";
	$sql .= "ORDER BY e.`id_classe` ASC , e.`nom` ASC , e.`prenom` ASC  ";
	
	//echo "<br />".$sql."<br />";
	$resultchargeDB = $mysqli->query($sql);	
	return $resultchargeDB;		
}

/**
 * 
 * @global object $mysqli
 * @param string $eleve INE de l'élève
 * @param string $prof Login de l'enseignant
 * @param string $anneeAPB année APB sur 4 chiffre
 * @return mysql_query
 */
function chercheNotes($eleve,$prof,$anneeAPB) {
	global $mysqli;
	$sql = "SELECT DISTINCT n.* "
	   . "FROM `plugin_archAPB_notes` AS n ";
	$sql .= "INNER JOIN  `plugin_archAPB_matieres` AS m ON (n.code_service = m.id_gepi AND m.annee  = n.annee) ";
	$sql .= "WHERE m.login_prof LIKE '".$prof."' ";
	$sql .= "AND n.ine = '".$eleve."' ";
	$sql .= "AND n.annee = '".$anneeAPB."' ";
	
	$sql .= "ORDER BY n.code_service ASC , n.trimestre ASC  ";
	//echo "<br />".$sql."<br />";
	$resultchargeDB = $mysqli->query($sql);	
	return $resultchargeDB;		
}

/**
 * Recherche le nombre maximum de périodes de notes 
 * 
 * @global object $mysqli connexion à la base
 * @param text $eleve ine de l'élève, rien si on veut tous les élèves
 * @return int nombre maxi de périodes de notes
 */
function maxTrimNotes($eleve=NULL) {
	global $mysqli;
	$sql = "SELECT MAX(trimestre) as trimestre FROM `plugin_archAPB_notes` ";
	if ($eleve) {
		$sql = "WHERE ine = '".$eleve."' ";	
	}
	//echo "<br />".$sql."<br />";
	$resultchargeDB = $mysqli->query($sql);
	return $resultchargeDB->fetch_object()->trimestre;		
}

/**
 * renvoie une information sur la matière
 * 
 * @global object $mysqli
 * @param string $code Code GEPI de la matière
 * @param string $annee année APB sur 4 chiffres
 * @param string $champ libellé du champ cherché
 * @return string
 */
function getMatiere($code, $annee, $champ = 'libelle') {
	global $mysqli;
	$sql = "SELECT * FROM `plugin_archAPB_matieres`  WHERE id_gepi = '".$code."' AND  annee  = '".$annee."' ";
	//echo "<br />".$sql."<br />";
	$resultchargeDB = $mysqli->query($sql);	
	switch ($champ) {
		case 'nom_complet' :
			$retour = $resultchargeDB->fetch_object()->nom_complet;
			break;
		case 'modalite' :
			$retour = $resultchargeDB->fetch_object()->modalite;
			break;
		case 'login_prof' :
			$retour = $resultchargeDB->fetch_object()->login_prof;
			break;
		case 'libelle' :
			$retour = $resultchargeDB->fetch_object()->libelle_sconet;
			break;
		default :
			$retour = $resultchargeDB->fetch_object();
	}
	return 	$retour;
}

/**
 * Renvoie l'appréciation saisie par un enseignant dans le plugin LSL
 * 
 * @global object $mysqli
 * @param string $eleve login de l'élève
 * @param string $code id de la matière dans APB
 * @param string $annee année LSL sur 4 chiffres
 * @return string
 */
function getAppreciationProf($eleve, $code, $annee) {
	global $mysqli;
	$sql = "SELECT * FROM `plugin_lsl_eval_app`  ";
	$sql .= "WHERE id_APB = '".$code."' ";
	$sql .= "AND annee = '".$annee."' ";
	$sql .= "AND eleve = '".$eleve."' ";
	
	//echo "<br />".$sql."<br />";
	$resultchargeDB = $mysqli->query($sql);	
	$retour = "";
	if ($resultchargeDB->num_rows) {
		$result = $resultchargeDB->fetch_object();
		$retour = $result->appreciation;
	}
	return $retour;
}

/**
 * Enregistre dans les tables LSL l'appréciation d'un enseignant
 * 
 * @global object $mysqli
 * @param string $eleve login de l'élève
 * @param string $code id de la matière dans APB
 * @param string $annee année LSL sur 4 chiffres
 * @param string $appreciation 300 caractères maximum
 * @param string $prof login du prof
 * @return boolean
 */
function setAppreciationProf($eleve, $code, $annee, $appreciation, $prof) {
	global $mysqli;
	$sql = "INSERT INTO `plugin_lsl_eval_app` (`id` ,`annee` ,`prof` ,`appreciation` ,`id_APB` ,`eleve`) "
	   . "VALUES (NULL , '".$annee."', '".$prof."', '".trim($appreciation)."', '".$code."', '".$eleve."') "
	   . "ON DUPLICATE KEY UPDATE `appreciation` = '".trim($appreciation)."' ";
	//echo "<br />".$sql."<br />";
	$resultchargeDB = $mysqli->query($sql);
        return $resultchargeDB;
}

function getLoginProfAppreciation($eleve, $code, $annee) {
	global $mysqli;
	$sql = "SELECT * FROM `plugin_lsl_eval_app`  ";
	$sql .= "WHERE id_APB = '".$code."' ";
	$sql .= "AND annee = '".$annee."' ";
	$sql .= "AND eleve = '".$eleve."' ";
	//echo "<br />".$sql."<br />";
	$resultchargeDB = $mysqli->query($sql);	
	$retour = "";
	if ($resultchargeDB->num_rows) {
		$result = $resultchargeDB->fetch_object();
		$retour = $result->prof;
	}
	return $retour;
}

/**
 * retourne un utilisateur à partir de son login
 * 
 * @global object $mysqli
 * @param string $login login de l'utilisateur recherché
 * @return object
 */
function getUtilisateur($login) {
	global $mysqli;
	$sql = "SELECT * FROM `utilisateurs` WHERE login = '".$login."' ";
	//echo "<br />".$sql."<br />";
	$resultchargeDB = $mysqli->query($sql);	
	return $resultchargeDB->fetch_object();	
}

/**
 * 
 * @global object $mysqli
 * @param sting $droit
 * @return boolean
 */
function lsl_getDroit($droit) {
	global $mysqli;
	$retour = FALSE;
	$sql = "SELECT * FROM `plugin_lsl_droit` WHERE droit = '".$droit."' ";
	//echo "<br />".$sql."<br />";
	$resultchargeDB = $mysqli->query($sql);
	if ($resultchargeDB->num_rows) {
		$droitRetour = $resultchargeDB->fetch_object();
		if ($droitRetour->ouvert == "y") {
			$retour = TRUE;
		}
	}	
	return $retour;	
}

function lsl_enregistreDroits($droit , $valeur) {
	global $mysqli;
	$sql = "INSERT INTO `plugin_lsl_droit` (`id` ,`droit` ,`ouvert`) "
	   . "VALUES (NULL , '".$droit."', '".$valeur."') "
	   . "ON DUPLICATE KEY UPDATE `ouvert` = '".$valeur."' ";	
	//echo "<br />".$sql."<br />";
	$resultchargeDB = $mysqli->query($sql);
}

function lsl_enregistre_ouvert_prof($classe , $valeur) {
	global $mysqli;
	$sql = "INSERT INTO `plugin_lsl_classes_ouvertes` (`id` ,`classe` ,`ouvert`) "
	   . "VALUES (NULL , '".$classe."', '".$valeur."') "
	   . "ON DUPLICATE KEY UPDATE `ouvert` = '".$valeur."' ";	
	//echo "<br />".$sql."<br />";
	$resultchargeDB = $mysqli->query($sql);
	
}

function lsl_get_ouvert_prof($classe) {
	global $mysqli;
	$retour = FALSE;
	$sql = "SELECT * FROM `plugin_lsl_classes_ouvertes` WHERE `classe` = '".$classe."' ";
	//echo "<br />".$sql."<br />";
	$resultchargeDB = $mysqli->query($sql);
	if ($resultchargeDB->num_rows) {
		$droitRetour = $resultchargeDB->fetch_object();
		if ($droitRetour->ouvert == "y") {
			$retour = TRUE;
		}
	}
	return $retour;	
}

function cherche_classe_APB($id, $annee) {
    global $mysqli;
    $sql = "SELECT * FROM `plugin_archAPB_classes` WHERE `id` = '".$id."' AND `annee` = '".$annee."' ";
    $resultchargeDB = $mysqli->query($sql);	
    return $resultchargeDB->fetch_object();	
}

function LSL_get_ele_id($eleve) {
    global $mysqli;
    $retour = FALSE;

    $sql = "SELECT ele_id FROM eleves "
       . "WHERE no_gep = '".$eleve->ine."' "
       . "AND nom = \"".$eleve->nom."\"   "
       . "AND prenom = \"".$eleve->prenom."\"  "
       . "AND naissance = '".$eleve->ddn."'  ";
    $resultchargeDB = $mysqli->query($sql);
    //if ($eleve->ine == 'XXXXXXXXXX') echo $sql;
    if ($resultchargeDB->num_rows) {
        $retour = $resultchargeDB->fetch_object()->ele_id;	
    }
    return($retour);
}

function LSL_change_classe($ine, $annee) {
	global $mysqli;
	$retour = FALSE;
	$sql = "SELECT t3.* "
                . "FROM ( SELECT t2 . * , count( * ) AS COMPTEUR2 "
                     . "FROM ( SELECT t1 . * , count( * ) AS COMPTEUR "
                          . "FROM (SELECT n.code_service, n.ine, n.etat, m.modalite, n.annee, n.appreciation, m.code_sconet, m.libelle_sconet "
                               . "FROM `plugin_archAPB_notes` AS n "
                               . "INNER JOIN `plugin_archAPB_matieres` AS m "
                               . "ON n.code_service = m.id_gepi AND n.`annee` = m.`annee` "
                               . "WHERE n.`ine` = '".$ine."' AND n.`annee` = '".$annee."' "
                               . "ORDER BY m.libelle_sconet ASC , n.trimestre ASC , m.code_sconet ASC "
                          . ")t1 "
                          . "GROUP BY t1.code_service "
                          . "ORDER BY t1.code_sconet "
                     . ")t2 "
                     . "GROUP BY t2.code_sconet , t2.modalite "
                     . "ORDER BY t2.code_sconet "
                . ")t3 "
                . "WHERE t3.COMPTEUR2 >1";
	//echo "<br />".$sql."<br />";
	$resultchargeDB = $mysqli->query($sql);	
	if ($resultchargeDB->num_rows) {
		$retour = TRUE;
                //echo "<br />".$sql."<br />";
	}
	return $retour;
}

/**
 * 
 * @global object $mysqli
 * @param type $formation
 * @param type $matiere
 * @param type $Modalite
 * @param type $noteIn
 * @param type $appreciationIn
 * @param type $option
 */
function LSL_enregistre_programme($formation, $matiere, $Modalite, $noteIn=NULL, $appreciationIn=NULL, $option=NULL) {
    global $mysqli;
    if (mb_strtolower($noteIn)=='n')  {$note = 'n';} else {$note = 'y';}
    if (mb_strtolower($appreciationIn)=='n')  {$appreciation = 'n';} else {$appreciation = 'y';}
    $sql = "INSERT INTO `plugin_lsl_programmes` (`id`, `formation`, `matiere`, `Modalite` ,`note` ,`appreciation` ,`option`) "
       . "VALUE (NULL, '".$formation."', '".$matiere."', '".$Modalite."', '".$note."', '".$appreciation."', '".$option."') "
       . "ON DUPLICATE KEY UPDATE `formation`= '".$formation."' ";	
    if (mb_strtolower($noteIn)=='n') {
        $sql .= ", `note`='n' ";
    } elseif (mb_strtolower($noteIn)==='y') {
        $sql .= ", `note`='y' ";
    }
    if (mb_strtolower($appreciationIn)=='n') {
        $sql .= ", `appreciation`='n' ";
    } elseif (mb_strtolower($appreciationIn)=='y') {
        $sql .= ", `appreciation`='y' ";
    }
    if ($option && strlen($option)) {
        $sql .= ", `option`= '".$option."' ";
    }
    //echo "<br />".$sql."<br />";
    $resultchargeDB = $mysqli->query($sql);
    return $resultchargeDB;
}

function extraitProgrammes($formation = NULL) {
	global $mysqli;
	
	if ($formation) {
		$mef_rattachement = $formation;
		$sql1 = "SELECT * FROM `plugin_lsl_formations` WHERE `MEF` = '".$formation."' ";
		//echo "<br />".$sql1;
		$resultchargeDB1 = $mysqli->query($sql1);
		if ($resultchargeDB1->num_rows) {
			$mef_rattachement = $resultchargeDB1->fetch_object()->MEF_rattachement;
		}
	}
	
	
	$sql = "SELECT * FROM `plugin_lsl_programmes` ";
	if ($formation) {
		$sql .= "WHERE `formation` = '".$mef_rattachement."' ";
	}
	$sql .= "ORDER BY `formation` ASC, `matiere` ASC ";
	//echo "<br />".$sql;
	$resultchargeDB = $mysqli->query($sql);	
	return $resultchargeDB;	
	
}

function supprimeProgramme($idFormation) {
	global $mysqli;
	$sql = "DELETE FROM `plugin_lsl_programmes` WHERE `id` = '".$idFormation."' ";
	$resultchargeDB = $mysqli->query($sql);
}
 
function extraitFormations($anneeAPB, $id = NULL) {
	global $mysqli;	
	$sql = "SELECT DISTINCT t2.*, f.edition, f.libelle, f.MEF_rattachement "
	   . "FROM plugin_lsl_formations AS f "
	   . "INNER JOIN ("
	   . "SELECT t1.code_mef, t1.niveau "
	   . "FROM ("
	   . "SELECT c.* , m.code_mef "
	   . "FROM plugin_archAPB_classes AS c "
	   . "INNER JOIN plugin_archAPB_mefs_classes AS m  "
	   . "ON (c.id_structure_sconet = m.id_structure_sconet AND m.annee = c.annee) "
	   . "WHERE c.`annee` = '".$anneeAPB."' ";
	if ($id) {
		$sql .= " AND c.`id` = '".$id."' ";
	}
	$sql .= ") t1 "
	   . "GROUP BY t1.code_mef "
	   . ") t2 "
	   . "ON (t2.code_mef = f.MEF )  ";
	//echo "<br />".$sql;
	$resultchargeDB = $mysqli->query($sql);	
	return $resultchargeDB;		
}
 
function MaJFormations($formations) {
    global $mysqli;
    global $anneeLSL;

    foreach ($formations as $formation) {
        if ($formation ["code_mef"] == $formation["MEF_rattachement"]) {
            $sql = "SELECT DISTINCT * FROM `nomenclatures_valeurs` "
               . "WHERE `type` = 'mef' AND `nom` = 'mef_rattachement' "
               . "AND `valeur` = '".$formation ["code_mef"]."' "
               . "AND `code` != '".$formation ["code_mef"]."' ";
//echo "<br />".$sql;
            $resultChargeMEFs = $mysqli->query($sql);
            if ($resultChargeMEFs->num_rows) {
                $type = 'mef';
                $MEF_rattachement = $formation ["code_mef"] ;

                while ($mefCharge = $resultChargeMEFs->fetch_object() ) {
                    $type = 'mef';
                    $code = $MEF = $mefCharge->code;
                    $nom = 'libelle_edition';
                    $edition = getValeurNomenclature($type, $code, $nom);					

                    $nom = 'libelle_long';
                    $libelle = getValeurNomenclature($type, $code, $nom);					

                    $nom = 'formation';	
                    $libelle_long = getValeurNomenclature($type, $code, $nom);

                    LSL_enregistre_MEF($MEF, $edition, $libelle, $MEF_rattachement, $anneeLSL);

                }
            }
        }		
    }	
}

function getValeurNomenclature($type, $code, $nom) {
	global $mysqli;
	$filtre = "";
	$retour = "";
	$filtre .= " WHERE `type` = '".$type."' ";
	$filtre .= " AND `code` = '".$code."' ";
	$filtre .= " AND `nom` = '".$nom."' ";
	
	$sql = "SELECT DISTINCT * FROM `nomenclatures_valeurs` ".$filtre;
	//echo "<br />".$sql;
	$resultchargeDB = $mysqli->query($sql);	
	$retour = $resultchargeDB->fetch_object()->valeur;
	return $retour;		
}

function LSL_enregistre_MEF($MEF, $edition, $libelle, $MEF_rattachement, $annee) {
	global $mysqli;
	$sql = "INSERT INTO `plugin_lsl_formations` (`id` , `MEF` , `edition` , `libelle` , `MEF_rattachement`, `annee` )"
	   . "VALUE (NULL , '".$MEF."', '".$edition."', '".$libelle."', '".$MEF_rattachement."', '".$annee."') "
	   . "ON  DUPLICATE KEY UPDATE `edition` = '".$edition."' , `libelle` = '".$libelle."' , `MEF_rattachement` = '".$MEF_rattachement."' ";
	//echo "<br />".$sql;
	$resultchargeDB = $mysqli->query($sql);
}

function LSL_matiereDeSerie($MEF, $matiere) {
    global $mysqli;

        $MEF_rattachement = $MEF;
        $sql1 = "SELECT * FROM `plugin_lsl_formations` WHERE `MEF` = '".$MEF."' ";
        //echo "<br />".$sql1;
        $resultchargeDB1 = $mysqli->query($sql1);
        if ($resultchargeDB1->num_rows) {
                $MEF_rattachement = $resultchargeDB1->fetch_object()->MEF_rattachement;
        }

    if(substr($matiere,0,3) == "030") {
        // Si c'est une langue, le code commence par 030 et seul 030000 rst dans plugin_lsl_programmes
        $matiere = "030%";
    }
    $sql = "SELECT * FROM `plugin_lsl_programmes` WHERE `formation` = '".$MEF_rattachement."' AND `matiere` LIKE '".$matiere."' ";
    //echo "<br />".$sql;
    $resultchargeDB = $mysqli->query($sql);
    return $resultchargeDB->num_rows;	
}
 
function formationValide($id,$annee) {	
	global $mysqli;
	$sql = "SELECT * FROM `plugin_lsl_formations` AS f INNER JOIN ("
	   . "SELECT c.* , m.code_mef FROM `plugin_archAPB_classes` AS c "
	   . "INNER JOIN `plugin_archAPB_mefs_classes` AS m "
	   . "ON (m.annee = c.annee AND m.id_structure_sconet = c.id_structure_sconet) "
	   . "WHERE c.`id` = '".$id."' AND c.annee = '".$annee."'  "
	   . ") t1 "
	   . "ON t1.code_mef = f.MEF ";
	//echo "<br />".$sql;
	$resultchargeDB = $mysqli->query($sql);
	return $resultchargeDB->num_rows;
}

function trimestreNote($trimestre,$annee,$code_service) {
	global $mysqli;
	$annee=$annee+1;
	$retour = FALSE;
	$sql = "SELECT * FROM `plugin_archAPB_notes` "
	   . "WHERE  code_service = '".$code_service."' AND trimestre = '".$trimestre."' AND annee = '".$annee."' ";
	// echo "<br />".$sql;
	$resultchargeDB = $mysqli->query($sql);
	if ($resultchargeDB->num_rows) {
		$retour = TRUE;
	}
	return $retour;	
}

function LSL_modalite($matiere, $formation) {
	global $mysqli;
	$retour = FALSE;
	$sql = "SELECT 	Modalite FROM `plugin_lsl_programmes` "
	   . "WHERE  matiere = '".$matiere."' AND formation = '".$formation."' ";
	//echo "<br />".$sql;
	$resultchargeDB = $mysqli->query($sql);
	if ($resultchargeDB->num_rows) {
		$retour= $resultchargeDB->fetch_object()->Modalite;
	}
	return $retour;
}

function LSL_get_MEF_classe($id_structure_sconet,$annee) {
	global $mysqli;
	$retour=NULL;
	$sql = "SELECT * FROM `plugin_archAPB_mefs_classes` "
	   . "WHERE annee = '".$annee."' AND id_structure_sconet = '".$id_structure_sconet."' ";
	//echo "<br />".$sql;
	$resultchargeDB = $mysqli->query($sql);	
	if ($resultchargeDB->num_rows) {
		$retour = $resultchargeDB->fetch_object();	
	}
	return $retour;
	
}

function display_xml_error($error) {
	switch ($error->code) {
		case 1871:
			$return = "Erreur ".$error->code." → une classe n'est pas reconnue, la scolarité correspondante est vide";
			break;
		default :	
			switch ($error->level) {
				case LIBXML_ERR_WARNING:
					$return = "Attention ".$error->code." : ";
					break;
				 case LIBXML_ERR_ERROR:
					$return = "Erreur ".$error->code." : ";
					break;
				case LIBXML_ERR_FATAL:
					$return = "Erreur Fatale ".$error->code." : ";
					break;
			}
			$return .= trim($error->message);
	}
    return $return."<hr />";
}

function LSL_est_maitre($MEF) {
	global $mysqli;
	$sql = "SELECT * FROM `plugin_lsl_formations` WHERE MEF_rattachement = '".$MEF."'";
	//echo "<br />".$sql;
	$resultchargeDB = $mysqli->query($sql);	
	return $resultchargeDB->num_rows;	
}

function LSL_peut_supprimer($id, $modalite) {
	$retour = FALSE;
	if ((('03' == substr($id, 0, 2)) && ('1' != substr($id, -1, 1))) || ('F' == $modalite)){
		$retour = TRUE;
	}
	return $retour;
}

function PeriodeNExistePas($Periodiques, $periodesNotes, $annee, $lastService, $newScolarite) {
    // On vérifie si le trimestre est renseigné pour des élèves, 
    // si oui, on met à -1 pour cet élève
    PeriodeNonNotee(2, $periodesNotes, $annee, $lastService, $Periodiques);
    PeriodeNonNotee(2, $periodesNotes, $annee, $lastService, $Periodiques);
    if ($newScolarite["code-periode"] == "T") {
        PeriodeNonNotee(3, $periodesNotes, $annee, $lastService, $Periodiques);
    }
    return $Periodiques;
}

function PeriodeNonNotee($periode, $periodesNotes, $annee, $lastService, $Periodiques) {
    if (!in_array($periode, $periodesNotes)) {
        if(trimestreNote($periode,$annee->annee,$lastService)) {
            $trimestre = $Periodiques->addChild('periode');
            $trimestre->addAttribute('numero', $periode);
            $trimestre->addAttribute('moyenne', -1);
            ecrit("Période ".$periode." moyenne -1 (non renseignée)\n");
            
        }									
    }
    
}
