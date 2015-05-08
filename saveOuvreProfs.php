
<?php 

$classes = extraitClasses($anneeSolaire);

while ($obj = $classes->fetch_object()) {
	$ouvert = "n";
	$type = "g";
	$index=$obj->id;
	if (isset($_POST['classe_prof']) && isset($_POST['classe_prof'][$index])) {
		//echo $index.'<br>';
		$ouvert = "y";
	}
	if (isset($_POST['lycee']) && isset($_POST['lycee'][$index])) {
		$type = $_POST['lycee'][$index];
	}
	lsl_enregistre_ouvert_prof($index , $ouvert , $type);
}
// on libère la mémoire
$classes->close();