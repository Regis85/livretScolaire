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
$nbInputs = count(filter_input_array(INPUT_POST)) +  count(filter_input_array(INPUT_GET));
if ((function_exists("check_token")) && ($nbInputs)) {
	check_token();
}

//recherche de l'utilisateur avec propel
$utilisateur = UtilisateurProfessionnelPeer::getUtilisateursSessionEnCours();
if ($utilisateur == null) {
	header("Location: ../../logout.php?auto=1");
	die();
}

include_once 'lib/fonctionAdmin.php';

verifieTables();

//debug_var();


//********************************************
//**************** Constantes *****************
//********************************************
$dirTemp = "../../temp/";
$dirTemp .= get_user_temp_directory()."/";


//********************************************
//**************** Fonctions *****************
//********************************************

include_once "lib/fonctions.php";

$anneeLSL = $anneeSolaire= lsl_annee(getSettingValue("gepiYear"));
$anneeAPB = $anneeLSL+1;

$titre_page = "Livret scolaire";

$serveur = filter_input(INPUT_SERVER, 'PHP_SELF') ;

if (!suivi_ariane($serveur,"Livret scolaire → tables")) {
//if (!suivi_ariane($_SERVER['PHP_SELF'],"Livret scolaire → tables")) {
	echo "erreur lors de la création du fil d'ariane";
}


   
require_once("../../lib/header.inc.php");

?>
<form method="post" action="verifieTables.php" id="form_verifieTables">
	<p>
		<button type="submit" name="action" value="verifieTables">Vérifier les tables</button>
		<?php if (function_exists("add_token_field")) {echo add_token_field(); } ?>
	</p>
</form>

<form method="post" action="verifieTables.php" id="form_verifieTables">
	<p>
		<button type="submit" name="action" value="chargeCompetences">Importer les compétences</button>
		<?php if (function_exists("add_token_field")) {echo add_token_field(); } ?>
	</p>
</form>
<?php
// debug_var();
//**************** Pied de page *****************
require_once("../../lib/footer.inc.php");
//**************** Fin de pied de page *****************
