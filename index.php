<?php

/*
*
* Copyright 2014 Régis Bouguin
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


$niveau_arbo = "2";
// Initialisations files (Attention au chemin des fichiers en fonction de l'arborescence)
//include("../../lib/initialisationsPropel.inc.php");
include("../../lib/initialisations.inc.php");
include("../plugins.class.php");
require_once("../../lib/initialisationsPropel.inc.php");


// Resume session
$resultat_session = $session_gepi->security_check();
if ($resultat_session == 'c') {
    header("Location: ../../utilisateurs/mon_compte.php?change_mdp=yes");
    die();
} else if ($resultat_session == '0') {
    header("Location: ../../logout.php?auto=1");
    die();
}

// l'utilisateur est-il autorisé à exécuter ce script ?
include("verification_autorisations.inc.php");

// si l'appel se fait avec passage de paramètre alors test du token
if ((function_exists("check_token")) && ((count($_POST)<>0) || (count($_GET)<>0))) check_token();


//recherche de l'utilisateur avec propel
$utilisateur = UtilisateurProfessionnelPeer::getUtilisateursSessionEnCours();
if ($utilisateur == null) {
	header("Location: ../../logout.php?auto=1");
	die();
}



//debug_var();

//********************************************
//**************** Constantes *****************
//********************************************
$dirTemp = "../../temp/";
$dirTemp .= get_user_temp_directory()."/";


//********************************************
//**************** Fonctions *****************
//********************************************

include_once "fonctions.php";

//$anneeSolaire=  apb_annee(getSettingValue("gepiYear"));
//echo $anneeSolaire;
$anneeSolaire = $anneeLSL = lsl_annee(getSettingValue("gepiYear"));
$anneeAPB = $anneeAPB = $anneeLSL+1;

//********************************************
//******************* TODO *******************
//********************************************
// Saisie des séries concernées

//**************** EN-TETE *****************
$titre_page = "Livret scolaire";
$tbs_librairies[]= "script.js";

if (!suivi_ariane($_SERVER['PHP_SELF'],"Livret scolaire"))
   echo "erreur lors de la création du fil d'ariane";
   
require_once("../../lib/header.inc.php");

//**************** Vérifier la présence d'APB *************



//**************** en administrateur *************
if ($utilisateur->getStatut()=="professeur") {
	require_once("afficheProf.php");
	
} elseif ($utilisateur->getStatut()=="scolarite") {
	require_once("afficheScolarite.php");	
	
} elseif ($utilisateur->getStatut()=="cpe") {
	
} elseif ($utilisateur->getStatut()=="administrateur") {


	//**************** FIN EN-TETE *************
	$creeFichier = isset($_POST['creeFichier']) ? $_POST['creeFichier'] : NULL ;
	$uploadFichier = isset($_POST['uploadFichier']) ? $_POST['uploadFichier'] : NULL ;
	$saveDroits = isset($_POST['sauveDroits']) ? $_POST['sauveDroits'] : NULL ;
	$ouvreProfs = isset($_POST['ouvertsProfs']) ? $_POST['ouvertsProfs'] : NULL ;

	if ($creeFichier) {
		//**************************************************
		//********* Création du fichier de données *********
		//**************************************************

		if (!isset($_POST['classes']) or !count($_POST['classes'])){ 
?>
<p class='center rouge grand bold'>
	Vous devez choisir au moins une classe
</p>
<?php
			include_once "afficheAccueil.php";
		} else {
			$selectClasses = $_POST['classes'];
			include_once "creeFichier.php";
			//**************** extraire les données **************** 
			include_once "afficheExtract.php";
		}

	} else if ($uploadFichier) {
		//********************************************************
		//********* Téléchargement du fichier de données *********
		//********************************************************
		include_once "upload.php";
		//**************** extraire les données **************** 
		include_once "afficheAccueil.php";

	} else if ($saveDroits) {
		include_once "saveDroits.php";
		//**************** extraire les données **************** 
		include_once "afficheAccueil.php";
	} else if ($ouvreProfs) {
		include_once "saveOuvreProfs.php";
		//**************** extraire les données **************** 
		include_once "afficheAccueil.php";
	} else {
		//**************** extraire les données **************** 
		include_once "afficheAccueil.php";
	}



}

debug_var();
//**************** Pied de page *****************
require_once("../../lib/footer.inc.php");
//**************** Fin de pied de page *****************