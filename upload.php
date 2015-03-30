<?php

function lire_csv($nom_fichier, $separateur =";"){
    $row = 0;
    $donnee = array();   
    $f = fopen ($nom_fichier,"r");
    $taille = filesize($nom_fichier)+1;
    while ($donnee = fgetcsv($f, $taille, $separateur)) {
        $result[$row] = $donnee;
        $row++;
    }
    fclose ($f);
    return $result;
}
function verifie_csv($donnees_csv){
	$retour = TRUE;
	list($key, $val) = @each($donnees_csv);
	if (count($val) == 2) {
		return TRUE;
	} else {
		return FALSE;
	}	
}
function requete_insert($donnees_csv, $table){
    $insert = array();
    $i = 0;     
    while (list($key, $val) = @each($donnees_csv)){
/*On ajoute une valeur vide ' ' en début pour le champs d'auto-incrémentation*/
		
		if ($i>0){
            $insert[$i] = "INSERT into ".$table." VALUES(' ','";  
            $insert[$i] .= implode("',\"", $val);
            $insert[$i] .= "\") ";   
            $insert[$i] .= "ON DUPLICATE KEY UPDATE ";   
            $insert[$i] .= " texte_competences = \"".$val[1]."\"";	
		}
		$i++;
    }      
    return $insert;
}


$target_dir = $dirTemp;
//echo $_FILES["fileToUpload"]["name"];
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
if ($_FILES["fileToUpload"]['type'] != "text/csv") {
?>
<p class="center rouge grand ">
	<?php echo basename($_FILES["fileToUpload"]["name"]); ?>
	<?php $uploadOk = 0; ?>
	→ Vous devez importer un fichier csv
</p>
<?php
	
} else {
	
?>
<p class="center vert grand ">
	<?php echo basename($_FILES["fileToUpload"]["name"]); ?>
	a été téléchargé
</p>
<?php
	
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
		$table = "plugin_lsl_competences";
		$donnees_csv = lire_csv($target_file, $separateur =";");
		if (verifie_csv($donnees_csv)) {
			global $mysqli;
			$requetes = requete_insert($donnees_csv, $table);
			foreach($requetes as $requete)
				{
				  $result = $mysqli->query($requete) or die('Erreur SQL !'. $requete.'<br />'.$mysqli->error);
	//$resultchargeDB = $mysqli->query($sql);
				}
		} else {
			$uploadOk = 0;
?>
<p class="center rouge grand ">
	Erreur de format du fichier
	<?php echo basename($_FILES["fileToUpload"]["name"]); ?>
</p>
<?php			
		}
    } else {
		$uploadOk = 0;
?>
<p class="center rouge grand ">
	Erreur lors du téléchargement du fichier
	<?php echo basename($_FILES["fileToUpload"]["name"]); ?>
</p>
<?php
    }

}
	/*
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
	 */
