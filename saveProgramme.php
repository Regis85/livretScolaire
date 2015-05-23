<?php
$target_dir = $dirTemp;
//echo $_FILES["fileToUpload"]["name"];
$target_file = $target_dir.'nomenclature.xml' ;
$uploadOk = 1;
if ($_FILES["fileToUpload"]['type'] == "application/zip") {
		$zip = new ZipArchive;
	if ($zip->open($_FILES["fileToUpload"]["tmp_name"]) === TRUE) {
        $filename = $zip->getNameIndex(0);
		
		$zip->extractTo($target_dir);
		$zip->close();
		rename ( $target_dir.$filename , $target_file );
		$charge = TRUE;
		//echo 'ok dans '.$target_dir."/".$filename;
	} else {
		$charge = FALSE;
	}
?>	
<?php
} elseif ($_FILES["fileToUpload"]['type'] == "text/xml") {
	
	if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
		//echo 'ok dans '.$target_dir;
		$charge = TRUE;
	} else {
		//echo 'échec';
		$charge = FALSE;
	}
} else {
?>
<p class="center rouge grand ">
	IMPORT DES PROGRAMMES <br />
	<?php echo basename($_FILES["fileToUpload"]["name"]); ?>
	<?php $uploadOk = 0; ?>
	→ Vous devez importer un fichier .xml, compressé au besoin en .zip
	<br />
	Votre fichier est de type <?php echo $_FILES["fileToUpload"]['type']; ?>
</p>
<?php
	$charge = FALSE;	
}

if (!$charge) {
?>
<p class="center rouge grand ">
	Échec du téléchargement de <?php echo $_FILES["fileToUpload"]["name"]; ?>
</p>
<?php
} else {
	//===== TODO vérifier le respect du schéma =====\\
	
	//===== On charge $target_file avec simpleXml =====\\
	
    $xml = simplexml_load_file($target_file);
	$mefs = $xml->DONNEES->MEFS;
	foreach ($mefs->MEF as $mef) {
		LSL_enregistre_MEF($mef['CODE_MEF'], rtrim($mef->LIBELLE_EDITION), $mef->LIBELLE_LONG);
	}
	
	$matieres = $xml->DONNEES->MATIERES;
	/*
	echo 'Matières :<br />';
	foreach ($matieres->MATIERE as $matiere) {
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$matiere['CODE_MATIERE'];
		echo ' → ';
		echo $matiere->LIBELLE_EDITION;
		echo '<br />';
	}
	echo '<hr />';
	 * 
	 */
	$programmes = $xml->DONNEES->PROGRAMMES	;
	//echo 'Programmes :<br />';
	foreach ($programmes->PROGRAMME as $programme) {
		foreach ($matieres->MATIERE as $matiere) {
			//echo $matiere['CODE_MATIERE'].' → '.$programme->CODE_MATIERE;
			if ((string)$matiere['CODE_MATIERE'] == (string)$programme->CODE_MATIERE) {
				//echo $matiere->LIBELLE_LONG;
				LSL_enregistre_programme($programme->CODE_MEF,$programme->CODE_MATIERE , $programme->CODE_MODALITE_ELECT, NULL, NULL, $matiere->LIBELLE_LONG);
			}
		}
	}
	
		
	
}
