<?php
$droitsPossibles = array('droitAppreciation','droitCompetences','avisBacScolarite','avisBacPP');

foreach ($droitsPossibles as $droit) {
	$enregistre = 'n';
	if (isset($_POST[$droit])) {
		$enregistre = 'y';	
	}	
	lsl_enregistreDroits($droit , $enregistre);
}


