<?php
$droitsPossibles = array('droitAppreciation','droitCompetences');

foreach ($droitsPossibles as $droit) {
	$enregistre = 'n';
	if (isset($_POST[$droit])) {
		$enregistre = 'y';	
	}	
	lsl_enregistreDroits($droit , $enregistre);
}


