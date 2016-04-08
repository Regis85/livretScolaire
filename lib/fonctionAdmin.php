<?php

/*
*
* Copyright 2015 Régis Bouguin
*
* This file is part of GEPI.
*
* GEPI is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
*
* GEPI is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with GEPI; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/



function verifieTables() {

	/*===== Structures des tables =====*/

	VerifieDroits();
	VerifieCompetences();
	VerifieCorrespondances();
	VerifieAppreciation();
	VerifieOuvertes();
	VerifieAvis();
	VerifieProgrammes();
	VerifieFormations();
	VerifieRattache();
}

function VerifieDroits() {
	global $mysqli;
	$table = 'plugin_lsl_droit';
	//1------ plugin_lsl_droit ------
	$champDroit = array('droit'=>"`droit` varchar(25) NOT NULL DEFAULT '' COMMENT 'Définition du droit'",
	   'ouvert'=>"`ouvert` varchar(1) NOT NULL DEFAULT 'n' COMMENT 'Droit ouvert'");
	$sql_droit = "CREATE TABLE IF NOT EXISTS `plugin_lsl_droit` ("
	   . "`id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id unique', ";
	foreach ($champDroit as $value){
		$sql_droit .= $value.", ";
	}
	 $sql_droit .=  "UNIQUE KEY `droit` (`droit`), "
	   . "PRIMARY KEY (`id`) "
	   . "COMMENT 'liste des droits du plugin Livrer Scolaire'"
	   . ") ENGINE=MyISAM  ;";
	//echo $sql_droit."<br />";
	$result_droit = $mysqli->query($sql_droit);
	if (!$result_droit) {
		echo 'erreur lors de la création de `plugin_lsl_droit`';
	}
	VerifieChamps($champDroit, $table);
}

function VerifieCompetences() {
	global $mysqli;
	$table = 'plugin_lsl_competences';
	//2------ plugin_lsl_competences ------
	$champCompetences = array('code_competences'=>"`code_competences` varchar(10) NOT NULL DEFAULT '' COMMENT 'code MEF des compétences'",
		'texte_competences'=>"`texte_competences` varchar(250) NOT NULL DEFAULT '' COMMENT 'Descriptif des compétences'",
		'annee'=>"`annee` varchar(4) NOT NULL DEFAULT '' COMMENT 'Année de validité de la compétence'");
	$sql_competences = "CREATE TABLE IF NOT EXISTS `plugin_lsl_competences` (
				  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id unique', ";
	foreach ($champCompetences as $value){
		$sql_competences .= $value.", ";
	}				 
	 $sql_competences .=  "UNIQUE KEY `code_competences` (`code_competences`, `annee`), "
		. "PRIMARY KEY (`id`) "
		. "COMMENT 'liste des competences'"
		. ") ENGINE=MyISAM  ;";
	// echo $sql_competences."<br />";
	$result_competences = $mysqli->query($sql_competences);
	if (!$result_competences) {
		echo 'erreur lors de la création de `plugin_lsl_competences`';
	}
	VerifieChamps($champCompetences, $table);
}

function VerifieCorrespondances() {
	global $mysqli;
	$table = 'plugin_lsl_correspondances';	
	//3------ plugin_lsl_correspondances ------
	$champCorrespondances = array('MEF'=>"`MEF` varchar(10) NOT NULL DEFAULT '' COMMENT 'code MEF du niveau et de la serie'",
		'Code_competences'=>"`Code_competences` varchar(10) NOT NULL DEFAULT '' COMMENT 'code MEF des compétences'",
		'Modalite'=>"`Modalite` ENUM('S', 'F', 'O') NOT NULL  COMMENT 'Matière obligatoire ou pas'",
		'Matiere'=>"`Matiere` varchar(50) NOT NULL DEFAULT '' COMMENT 'Code Matière BCN'",
		'Note'=>"`Note` ENUM('y', 'n') NOT NULL  COMMENT 'note obligatoire ou pas'",
		'Appreciation'=>"`Appreciation` ENUM('y', 'n') NOT NULL  COMMENT 'appréciation obligatoire ou pas'",
		'annee'=>"`annee` varchar(4) COMMENT 'Année de validité de la compétence'");
	$sql_correspondances = "CREATE TABLE IF NOT EXISTS `plugin_lsl_correspondances` ("
	   . "`id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id unique', ";
	foreach ($champCorrespondances as $value){
		$sql_correspondances .= $value.", ";
	}				 
	$sql_correspondances .= " UNIQUE KEY `correspondance` (`MEF`,`code_competences`), "
	   . "PRIMARY KEY (`id`) "
	   . "COMMENT 'correspondances code_competences → série + niveau'"
	   . ") ENGINE=MyISAM ;";
	//echo $sql_correspondances."<br />";
	$result_correspondances = $mysqli->query($sql_correspondances);
	if (!$result_correspondances) {
		echo 'erreur lors de la création de `plugin_lsl_correspondances`';
	}
	VerifieChamps($champCorrespondances, $table);
}

function VerifieAppreciation() {
	global $mysqli;
	$table = 'plugin_lsl_eval_app';
	//4------ plugin_lsl_eval_app ------
	$champEval_app = array('annee'=>"`annee` smallint(6) NOT NULL DEFAULT 0 COMMENT 'année'",
		'prof'=>"`prof` varchar(50) NOT NULL DEFAULT '' COMMENT 'login du prof'",
		'eleve'=>"`eleve` VARCHAR( 50 )  NOT NULL DEFAULT '' COMMENT 'login de l\'élève'",
		'appreciation'=>"`appreciation` varchar(300) NOT NULL DEFAULT '' COMMENT 'appreciation du prof'",
		'id_APB'=>"`id_APB` int(11) NOT NULL COMMENT 'code matiere dans APB'");
	$sql_eval_app = "CREATE TABLE IF NOT EXISTS `plugin_lsl_eval_app` ("
	   . "`id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id unique', ";
	foreach ($champEval_app as $value){
		$sql_eval_app .= $value.", ";
	}				 
	$sql_eval_app .= "UNIQUE KEY `matiere` ( `annee` , `id_APB` , `eleve` ), "
	   . "PRIMARY KEY (`id`) "
	   . "COMMENT 'appréciation annuelle des professeurs'"
	   . ") ENGINE=MyISAM  ;";
	//echo $sql_eval_app."<br />";
	$result_eval_app = $mysqli->query($sql_eval_app);
	if (!$result_eval_app) {
		echo 'erreur lors de la création de `plugin_lsl_eval_app`';
	}
	VerifieChamps($champEval_app, $table);
}

function VerifieOuvertes() {
	global $mysqli;
	$table = 'plugin_lsl_classes_ouvertes';
	//5------ plugin_lsl_classes_ouvertes ------
	$champClasses = array('classe'=>"`classe` smallint(5) NOT NULL COMMENT 'id de la classe dans APB'",
		'ouvert'=>"`ouvert` ENUM('y', 'n')  NOT NULL  COMMENT 'ouvert ou pas à la saisie par les profs'");
	$sql_classe = "CREATE TABLE IF NOT EXISTS `plugin_lsl_classes_ouvertes` ("
	   . "`id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id unique', ";
	foreach ($champClasses as $value){
		$sql_classe .= $value.", ";
	}
	$sql_classe	.= "UNIQUE KEY `classe` ( `classe` ), "
	   . "PRIMARY KEY (`id`) COMMENT 'classes ouvertes aux saisies par les professeurs'"
	   . ") ENGINE=MyISAM  ;";
	//echo $sql_classe."<br />";
	$result_classe = $mysqli->query($sql_classe);
	if (!$result_classe) {
		echo 'erreur lors de la création de `plugin_lsl_classes_ouvertes`';
	}
	VerifieChamps($champClasses, $table);
}

function VerifieAvis() {
	global $mysqli;
	$table = 'plugin_lsl_avis_annuels';
	//6------ plugin_lsl_avis_annuels ------
	$champAvis = array('code_ine'=>"`code_ine` varchar(50) NOT NULL DEFAULT '' COMMENT 'code INE des élèves'",
		'avis'=>"`avis` ENUM ('T', 'F', 'A', 'D') DEFAULT NULL COMMENT 'avis pour le BAC'",
		'avisChefEtab'=>"`avisChefEtab` varchar(300) COMMENT 'appréciation annuelle des élèves par le chef d\'établissement'",
		'login'=>"`login` varchar(50) COMMENT 'Login de l\'appréciateur'",
		'date'=>"`date` date COMMENT 'date de  l\'appréciation'",
		'annee'=>"`annee` varchar(4) NOT NULL DEFAULT '' COMMENT 'annee LSL de l\'appréciation'",
		'avisEngagement'=>"`avisEngagement` varchar(300) COMMENT 'observation du CPE sur les engagements et responsabilités de l’élève.'",
		'loginCPE'=>"`loginCPE` varchar(50) NOT NULL COMMENT 'login du CPE ayant enregistré l\'avis'",
		'dateCPE'=>"`dateCPE` date COMMENT 'date de l\'appréciation du CPE'",
		'avisInvestissement'=>"`avisInvestissement` varchar(300) COMMENT 'avis sur l\’investissement de l\’élève et sa participation à la vie du lycée'",
		'loginPP'=>"`loginPP` varchar(50) NOT NULL COMMENT 'login du PP ayant enregistré l\'avis'",
		'datePP'=>"`datePP` date COMMENT 'date de l\'appréciation du PP'");
	$sql_Avis = "CREATE TABLE IF NOT EXISTS `plugin_lsl_avis_annuels` ("
	   . "`id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id unique', ";
	foreach ($champAvis as $value){
		$sql_Avis .= $value.", ";
	}
	$sql_Avis .= "UNIQUE KEY `eleve` (`code_ine`, `annee`), "
	   . "PRIMARY KEY (`id`)"
	   . ") ENGINE=MyISAM  ;";
	//echo $sql_Avis."<br />";
	$result_Avis = $mysqli->query($sql_Avis);
	if (!$result_Avis) {
		echo 'erreur lors de la création de `plugin_lsl_avis_annuels`';
	}
	VerifieChamps($champAvis, $table);	
}

function VerifieProgrammes() {
	global $mysqli;
	$table = 'plugin_lsl_programmes';
	//7------ plugin_lsl_programmes ------
	$champProgrammes = array('formation'=>"`formation` NOT NULL DEFAULT '' varchar(11) COMMENT 'code SIECLE de la formation'",
		'matiere'=>"`matiere` varchar(6) NOT NULL DEFAULT '' COMMENT 'code SIECLE de la matière'",
		'Modalite'=>"`Modalite` ENUM ('S', 'F', 'O') NOT NULL DEFAULT 'S' COMMENT 'code SIECLE de la modalité (S,F,O)'",
		'note'=>"`note` ENUM ('y', 'n') NOT NULL DEFAULT 'y' COMMENT 'Les notes sont obligatoires ou pas (y - n)'",
		'appreciation'=>"`appreciation` ENUM ('y', 'n') NOT NULL DEFAULT 'y' COMMENT 'Les appréciations sont obligatoires ou pas (y - n)'",
		'option'=>"`option` varchar(250) COMMENT 'Commentaire sur la formation'");

	$sql_Programmes = "CREATE TABLE IF NOT EXISTS `plugin_lsl_programmes` (
				  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id unique', ";
	foreach ($champProgrammes as $value){
		$sql_Programmes .= $value.", ";
	}
	$sql_Programmes .= "UNIQUE KEY `couple` (`formation`, `matiere`, `Modalite`), "
	   . "PRIMARY KEY (`id`)"
	   . ") ENGINE=MyISAM "
	   . "COMMENT 'Liste des enseignements par formation';";
	//echo $sql_Programmes."<br />";
	$result_Programmes = $mysqli->query($sql_Programmes);
	if (!$result_Programmes) {
		echo 'erreur lors de la création de `plugin_lsl_formations`';
	}
	VerifieChamps($champProgrammes, $table);
}

function VerifieFormations() {
	global $mysqli;
	$table = 'plugin_lsl_formations';
	//8------ `plugin_lsl_formations` ------
	$champFormation = array('MEF'=>"`MEF` varchar(11) NOT NULL DEFAULT '' COMMENT 'code SIECLE de la formation'",
		'edition'=>"`edition` varchar(50) COMMENT 'libelle pour l\'édition niveau + codeSerie + specialite (à supprimer quand plus utiliser)'",
		'libelle'=>"`libelle` varchar(50) COMMENT 'libelle long niveau + serie + specialite en majuscule (à supprimer quand plus utiliser)'",
		'MEF_rattachement'=>"`MEF_rattachement` varchar(11) NOT NULL DEFAULT '' COMMENT 'code SIECLE de la formation de rattachement'",
		'annee'=>"`annee` varchar(4) NOT NULL DEFAULT '' COMMENT 'annee LSL'",
		'codeSerie'=>"`codeSerie` varchar(11) COMMENT 'Code de la série'",
		'niveau'=>"`niveau` varchar(20) COMMENT 'niveau du MEF'",
		'serie'=>"`serie` varchar(100) COMMENT 'libellé de la série'",
		'specialite'=>"`specialite` varchar(100) COMMENT 'spécialité dans la série'");
	$sql_formations = "CREATE TABLE IF NOT EXISTS `plugin_lsl_formations` ("
	   . "`id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id unique', ";
	foreach ($champFormation as $value){
		$sql_formations .= $value.", ";
	}
	$sql_formations .= "UNIQUE KEY `formation`  (`MEF` , `annee`), "
	   . "PRIMARY KEY (`id`)"
	   . ") ENGINE=MyISAM "
	   . "COMMENT 'Liste des MEF avec les MEF de rattachement';";
	//echo $sql_formations."<br />";
	$result_formations = $mysqli->query($sql_formations);
	if (!$result_formations) {
		echo 'erreur lors de la création de `plugin_lsl_formations`';
	}
	VerifieChamps($champFormation, $table);
}

function VerifieRattache() {
	global $mysqli;
	$table = 'plugin_lsl_rattache';
	//------ plugin_lsl_rattache ------
	$champRattache = array('MEF'=>"`MEF` varchar(11) NOT NULL DEFAULT '' COMMENT 'code SIECLE de la formation'",
		'annee'=>"`annee` varchar(4) NOT NULL DEFAULT '' COMMENT 'annee LSL de la formation'",
		'MEF_rattachement'=>"`MEF_rattachement` varchar(11) COMMENT 'code SIECLE de la formation de rattachement'"
	   );
	$sql_rattache = "CREATE TABLE IF NOT EXISTS `plugin_lsl_rattache` (
				  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id unique', ";
	foreach ($champRattache as $value){
		$sql_rattache .= $value.", ";
	}
	$sql_rattache .= "UNIQUE KEY `formation`  (`MEF`,`annee`), "
	   . "PRIMARY KEY (`id`)"
	   . ") ENGINE=MyISAM "
	   . "COMMENT 'jointures MEF → MEF de rattachement';";
	//echo $sql_rattache."<br />";
	$result_rattache = $mysqli->query($sql_rattache);
	if (!$result_rattache) {
		echo 'erreur lors de la création de `plugin_lsl_rattache`';
	}
	VerifieChamps($champRattache, $table);
}

function VerifieChamps($champs, $table) {
	global $mysqli;
	foreach ($champs as $key => $value) {
		$sqlChamp = "SHOW COLUMNS FROM `$table` LIKE '$key' ";
		$result_Champ = $mysqli->query($sqlChamp);
		if (!$result_Champ || !$result_Champ->num_rows) {
			CreeChamp($value, $table);
		}
	}
}

function CreeChamp($champ, $table) {
	global $mysqli;
	$sqlAjouteChamp = "ALTER TABLE `$table` ADD $champ";
	$result_AjouteChamp = $mysqli->query($sqlAjouteChamp);
	if (!$result_AjouteChamp) {
		echo $sqlAjouteChamp;
		echo "<br />erreur lors de la création du champ $champ dans `$table`<br />";
		}
}

function VerifieCleUnique($nom, $champs) {
	
}



