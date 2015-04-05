<?php


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




$export_filename = $_GET['Fichier_a_telecharger']; 
$dirTemp = "../../temp/";
$dirTemp .= get_user_temp_directory()."/";
$filename = $dirTemp.$export_filename;
   
header("Content-Type: text/xml");
header("Content-Disposition: attachment; filename=$filename"); 
//echo $doc->saveXML($doc->documentElement);