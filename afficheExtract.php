<p>
	<a href="" class="bold">Retour</a>
</p>
<p class="center rouge grand" >
	Vous devez avoir effectué les extractions pour APB avant d'utiliser ce plugin
</p>
<p class="center">
<?php
echo "Éditeur : ".$sxe->entete->editeur;
echo " - Application : ".$sxe->entete->application;
echo "<br /> Établissement : ".getSettingValue("gepiSchoolName")." - ".$sxe->entete->etablissement;
?>
</p>
<h2>Liste des niveaux concernées</h2>
<?php
if ($niveaux) {
	while($niveau = $niveaux->fetch_object()){
		echo $niveau->apb_niveau." ".$niveau->anneelsl."<br />"; 	
	}
}
?>
<h2>Liste des classes concernées</h2>
<?php
if ($classes) {
	$cpt=0;
	while($classe = $classes->fetch_object()){
		echo $classe->id." ".$classe->nom_court." ".$classe->nom_complet." ".$classe->login_pp." ";
		echo $classe->niveau." ".$classe->anneelsl." ".$classe->decoupage." ";
		echo $classe->id_structure_sconet." ".$classe->libelle_mef." ";
		echo $classe->traitee."<br />"; 
		$cpt++;
	}
	echo '<br />Nombre de classes : '.$cpt.'<br />';
}
?>
<h2>Liste des élèves concernés</h2>
<?php
//$classes = $_POST['classes'];
$eleves = elevesConcernees($anneeSolaire, $selectClasses);
if ($eleves) {
	$cpt=0;
	while($eleve = $eleves->fetch_object()){
		if (LSL_get_ele_id($eleve)) {
			echo $eleve->ine." - ".$eleve->nom." - ".$eleve->prenom." - ".$eleve->ddn." - ";
			echo $eleve->anneelsl." - ".$eleve->id_classe;
			echo "<br />"; 
			$cpt++;
		}
	}
	echo '<br />Nombre d\'élèves : '.$cpt.'<br />';
}


$eleves->close();