
<?php 

$classes = extraitClasses($anneeSolaire);

while ($obj = $classes->fetch_object()) {
	$ouvert = "n";
	$index=$obj->id;
	if (isset($_POST['classe_prof']) && isset($_POST['classe_prof'][$index])) {
		//echo $index.'<br>';
		$ouvert = "y";
	}
	lsl_enregistre_ouvert_prof($index , $ouvert);
}
// on libère la mémoire
$classes->close();