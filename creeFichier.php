<?php

//********************************************
//********* Récupération des données *********
//********************************************
$sxe = simplexml_load_string(mb_convert_encoding(file_get_contents('livret.xml'),"ISO-8859-15","ISO-8859-15"));
$sxe->entete->etablissement = getSettingValue("gepiSchoolRne");

$niveaux = niveauConcernees($anneeSolaire, $selectClasses);
$classes = classesConcernees($anneeSolaire, $selectClasses);
$eleves = elevesConcernees($anneeSolaire, $selectClasses);
// id , ine , nom , prenom , ddn , annee , anneelsl , id_classe , code_mef //

$eleves2 = $eleves;
while($eleve = $eleves2->fetch_object()){
	if (LSL_get_ele_id($eleve)) {
		$changeClasse = LSL_change_classe($eleve->ine,$anneeAPB);
		if ($changeClasse) {
			echo '<p>'.$eleve->ine.' cet élève a changé de classe ou de groupe en cours d\'année '.($anneeAPB-1).'-'.$anneeAPB.'</p>';
		}
		$changeClasse = LSL_change_classe($eleve->ine,$anneeLSL);
		if ($changeClasse) {
			echo '<p>'.$eleve->ine.' cet élève a changé de classe ou de groupe en cours d\'année '.($anneeLSL-1).'-'.$anneeLSL.'</p>';
		}
		$lastNiveau="";
		$newElv = $sxe->donnees->addChild('eleve');
		$newElv->addAttribute('id', LSL_get_ele_id($eleve));

		// récupérer les engagements
		$listeEngagements = engagementsEleve($eleve->ine);
		if(($listeEngagements->num_rows > 0)) {
			$engagements = $newElv->addChild('engagements');
			if(($listeEngagements->num_rows > 0)) {
				while ($engagement = $listeEngagements->fetch_object()){	
					$os=array(1,2,3,4,5);
					if (in_array($engagement->id_engagement, $os)){
						$newEngagement = $engagements->addChild('engagement');
						$newEngagement->addAttribute('code',$engagement->code );
					} else {
						$description=$engagement->description;
						$description = substr($description,0,300);	
						$newEngagement = $engagements->addChild('engagement-autre',$description);
					}
				}
			}
		}

		// récupérer l'avis d"examen
		/*
		$avisEleve=avisEleve($eleve->ine);
		if($avisEleve->num_rows > 0) {
			$newAvisElv=$newElv->addChild('avisExamen');
			while ($avis = $avisEleve->fetch_object()){
				$newAvisElv->addAttribute('code',$avis->avis );
			}
		}
		*/

		// récupérer les scolarités
		$scolarites = $newElv->addChild('scolarites');
		$annees = anneesEleve($eleve->ine);
		$derniereAnnee = NULL;
		// annee , id_classe , nom_court , nom_complet , login_pp , niveau // 
		while ($annee = $annees->fetch_object()) {
			if (!$derniereAnnee || $annee->niveau != $derniereAnnee) {
				$derniereAnnee = $annee->niveau;

				// On récupère le niveau
				$niveau = "";
				$getNiveau = getNiveau($annee->annee, $annee->id_classe);
				$niveau = $getNiveau->fetch_object();
				// Ne pas récupérer une année redoublée
				if ($niveau && $lastNiveau != $niveau->apb_niveau) {
					$newScolarite = $scolarites->addChild('scolarite');
					$newScolarite->addAttribute('annee-scolaire',$annee->annee);
					$codePeriode=codePeriode($eleve->ine, $annee->annee);
					$newScolarite->addAttribute('code-periode',$codePeriode);

					$lastNiveau = $niveau->apb_niveau;

					// récupérer les investissements
					/*
					$investissements = avisInvestissement($eleve->ine, $annee->annee);
					if ($investissements->num_rows){
						$investissement = $investissements->fetch_object();
						$newInvestissement = $newScolarite->addChild('avisInvestissement',$investissement->avis);
						$newInvestissement->addAttribute('date',$investissement->date);
						$newInvestissement->addAttribute('nom',$investissement->nom);
						$newInvestissement->addAttribute('prenom',$investissement->prenom);
					}
					 */

					$nbPeriode = 0;
					if ($newScolarite["code-periode"] == "T") {
						$nbPeriode = 3;
					} elseif ($newScolarite["code-periode"] == "S") {
						$nbPeriode = 2;
					}

					// TODO récupérer les avis Chef Etablissement
					// $newScolarite->addChild('avisChefEtab');

					// TODO récupérer les avis Engagement
					// $newScolarite->addChild('avisEngagement');

					// récupérer les évaluations

					//TODO → passer la formation, ne sélectionner que les matières de cette formation (changement de classe)
					//TODO → calculer la moyenne en ne tenant compte que des matières 

					$newEvaluations = evaluations($eleve->ine,$annee->annee);
					$lastMatiere = NULL;
					$lastService = NULL;
					$periodesNotes = NULL;
			
					while ($evaluation = $newEvaluations->fetch_object()) {
						// TODO on limite aux $evaluation de la série
						//NON-RECONNU
						if ('0' == $evaluation->code_sconet || 0 == intval($evaluation->code_sconet)) {
							//var_dump($evaluation);
							echo "<p class='red'>L'enseignement ".$evaluation->code_service." n'est pas reconnu";
							echo " pour l'année ".$annee->annee."-".($annee->annee+1).".";
							echo " Vous devez régler ce problème, les notes ne sont pas exportées.</p>";
						}
						
						if (LSL_matiereDeSerie($annee->code_mef, str_pad($evaluation->code_sconet, 6, '0', STR_PAD_LEFT))) {
							//echo 'La matière '.$annee->code_mef.'est bien enseignée';

							if ($lastService != $evaluation->code_service) {
								$lastService = $evaluation->code_service;
								if ($lastMatiere != str_pad($evaluation->code_sconet, 6, '0', STR_PAD_LEFT)) {
									// on change de matière
									if ($periodesNotes) {
										// On vérifie si le trimestre est renseigné pour des élèves, 
										// si oui, on met à -1 pour cet élève
										if (!in_array('1', $periodesNotes)) {
											if(trimestreNote('1',$annee->annee,$lastService)) {
												$trimestre = $Periodiques->addChild('periode');
												$trimestre->addAttribute('numero', 1);
												$trimestre->addAttribute('moyenne', -1);
											}									
										}
										if (!in_array('2', $periodesNotes)) {
											if(trimestreNote('2',$annee->annee,$lastService)) {
												$trimestre = $Periodiques->addChild('periode');
												$trimestre->addAttribute('numero', 2);
												$trimestre->addAttribute('moyenne', -1);
											}									
										}
										if ($newScolarite["code-periode"] == "T") {
											if (!in_array('3', $periodesNotes)) {
												if(trimestreNote('3',$annee->annee,$lastService)) {
													$trimestre = $Periodiques->addChild('periode');
													$trimestre->addAttribute('numero', 3);
													$trimestre->addAttribute('moyenne', -1);
												}									
											}
										}
										$periodesNotes = NULL;
									}
									
									
									$lastMatiere = str_pad($evaluation->code_sconet, 6, '0', STR_PAD_LEFT);
									$compteEleves = compteElvEval($annee->annee, $evaluation->code_service);
									$compteElv = $compteEleves->fetch_object();
									if ($compteElv->nombre){
										$newEval = $newScolarite->addChild('evaluation');
										$newEval->addAttribute('modalite-election',$evaluation->modalite);
										$newEval->addAttribute('code-matiere',str_pad($evaluation->code_sconet, 6, '0', STR_PAD_LEFT));
										$newStructure = $newEval->addChild('structure');
										$structureEvaluation = structureEval($annee->annee, $evaluation->code_service);
										$structureEval = $structureEvaluation->fetch_object();
										$moinsHuit = reparMoinsHuit($annee->annee, $evaluation->code_service);
										$huitDouze = reparMoinsHuit($annee->annee, $evaluation->code_service, 8, 12);
										$plusDouze = 100-($moinsHuit + $huitDouze);
										$newStructure->addAttribute('effectif',$compteElv->nombre);
										$newStructure->addAttribute('moyenne',round($structureEval->moyenne,2));				
										$newStructure->addAttribute('repar-moins-huit',$moinsHuit);				
										$newStructure->addAttribute('repar-huit-douze',$huitDouze);			
										$newStructure->addAttribute('repar-plus-douze',$plusDouze);
										$structureEvaluation ->close();
										$appAnnuelle=" ";
										$appAnnuelle=getAppreciationProf($eleve->ine, $evaluation->code_service, $annee->annee+1);
										if (!$appAnnuelle) {
											$appAnnuelle=" ";
											$newMessage = $eleve->nom." ".$eleve->prenom;
											$newMessage .= " n'a pas d'appréciation pour la matière ".getMatiere($evaluation->code_service, $annee->annee+1);
											$newMessage .= " pour l'année ".$annee->annee."-".($annee->annee+1) ;
											$messages[] = $newMessage;
										}
										$newEval->addChild('annuelle', $appAnnuelle);

										$Periodiques = $newEval->addChild('periodiques');
										$moyennes = moyenneTrimestre($annee->annee, $evaluation->code_service, $eleve->ine);
										while ($moyenne = $moyennes->fetch_object()) {
											$periodesNotes[] = $moyenne->trimestre;
											$trimestre = $Periodiques->addChild('periode');
											$trimestre->addAttribute('numero', $moyenne->trimestre);
											if ("S" == $moyenne->etat) {
												$trimestre->addAttribute('moyenne', $moyenne->moyenne);
											} else {
												$trimestre->addAttribute('moyenne', -1);
											}
										}
										$moyennes->close();

										// TODO récupérer les compétences
										// $newEval->addChild('competences');
										$Enseignants = $newEval->addChild('enseignants');
										$getEnseignants = enseignants($annee->annee, $evaluation->code_service);
										while ($getEnseignant = $getEnseignants->fetch_object()) {
											$Enseignant = $Enseignants->addChild('enseignant');

											$Enseignant->addAttribute('nom', substr($getEnseignant->nom, 0,65));
											$Enseignant->addAttribute('prenom', substr($getEnseignant->prenom, 0,50));
										}
										$getEnseignants->close();
									}
									$compteEleves->close();

								} else {
									$moyennes = moyenneTrimestre($annee->annee, $evaluation->code_service, $eleve->ine);
									while ($moyenne = $moyennes->fetch_object()) {
										$periodesNotes[] = $moyenne->trimestre;
										$trimestre = $Periodiques->addChild('periode');
										$trimestre->addAttribute('numero', $moyenne->trimestre);
										if ("S" == $moyenne->etat) {
											$trimestre->addAttribute('moyenne', $moyenne->moyenne);
										} else {
											$trimestre->addAttribute('moyenne', -1);
										}
									}
								}
							}
						} else {
							// on change de matière
							if ($periodesNotes) {
								// On vérifie si le trimestre est renseigné pour des élèves, 
								// si oui, on met à -1 pour cet élève
								if (!in_array('1', $periodesNotes)) {
									if(trimestreNote('1',$annee->annee,$lastService)) {
										$trimestre = $Periodiques->addChild('periode');
										$trimestre->addAttribute('numero', 1);
										$trimestre->addAttribute('moyenne', -1);
									}									
								}
								if (!in_array('2', $periodesNotes)) {
									if(trimestreNote('2',$annee->annee,$lastService)) {
										$trimestre = $Periodiques->addChild('periode');
										$trimestre->addAttribute('numero', 2);
										$trimestre->addAttribute('moyenne', -1);
									}									
								}
								if ($newScolarite["code-periode"] == "T") {
									if (!in_array('3', $periodesNotes)) {
										if(trimestreNote('3',$annee->annee,$lastService)) {
											$trimestre = $Periodiques->addChild('periode');
											$trimestre->addAttribute('numero', 3);
											$trimestre->addAttribute('moyenne', -1);
										}									
									}
								}
								$periodesNotes = NULL;
							}

						}
					}
					$newEvaluations->close();
					// on change de matière	
							if ($periodesNotes) {
								// On vérifie si le trimestre est renseigné pour des élèves, 
								// si oui, on met à -1 pour cet élève
								if (!in_array('1', $periodesNotes)) {
									if(trimestreNote('1',$annee->annee,$lastService)) {
										$trimestre = $Periodiques->addChild('periode');
										$trimestre->addAttribute('numero', 1);
										$trimestre->addAttribute('moyenne', -1);
									}									
								}
								if (!in_array('2', $periodesNotes)) {
									if(trimestreNote('2',$annee->annee,$lastService)) {
										$trimestre = $Periodiques->addChild('periode');
										$trimestre->addAttribute('numero', 2);
										$trimestre->addAttribute('moyenne', -1);
									}									
								}
								if ($newScolarite["code-periode"] == "T") {
									if (!in_array('3', $periodesNotes)) {
										if(trimestreNote('3',$annee->annee,$lastService)) {
											$trimestre = $Periodiques->addChild('periode');
											$trimestre->addAttribute('numero', 3);
											$trimestre->addAttribute('moyenne', -1);
										}									
									}
								}
								$periodesNotes = NULL;
							}	
				}
			}
		}
		$annees->close();
	} else {
		//l'élève n'existe pas dans la table élève, il faut le traiter manuellement
?>
<p class='rouge' 
   title="Vous devriez vérifier ses données, il peut s'agir d'une modification de l'INE de cet élève après un export APB.
Dans ce cas, un export devrait exister pour cet élève avec le bon INE et vous n'avez pas à tenir compte de cet avertissement."
   style="cursor:pointer"
>
	<?php echo $eleve->nom; ?> <?php echo $eleve->prenom; ?>
	ine <?php echo $eleve->ine; ?>
	né le <?php echo $eleve->ddn; ?>
	est inconnu dans la table eleves.
	Aucun export n'est généré pour cet élève.
</p>
<?php	
	}
}

//$extension=".txt";
$extension="";
$nomFichier = "LSL_".date("d-m-Y_H:i").".xml1".$extension;
//echo $dirTemp.$nomFichier;
   
$sxe->asXML($dirTemp.$nomFichier);

//===== vérifier le schéma avec import-lsl.xsd =====

$file = $dirTemp.$nomFichier;
$schema = "xsd/import-lsl.xsd";


// active la gestion d'erreur personnalisée
libxml_use_internal_errors(true);

// Instanciation d’un DOMDocument
$dom = new DOMDocument("1.0");

// Charge du XML depuis un fichier
$dom->load($file);

// Validation du document XML
/*
$validate = $dom->schemaValidate($schema) ?
"<p class='center grand vert'>Le schéma XML paraît valide !</p>" :
"<p class='center grand rouge'>Schéma XML non valide !</p>";
 * 
 */
if (!$dom->schemaValidate($schema)) { ?>
<p class='center grand rouge'>DOMDocument::schemaValidate() Votre fichier ".$nomFichier." n'est pas valide</p>
<?php	    //libxml_display_errors();
}


unset($dom);
// Affichage du résultat
//echo $validate;




if (isset($messages)) {
?>
<p class="center grand bold rouge" 
	onclick="bascule('messages')" 
	style="cursor:pointer"
	title="Cliquez pour déplier/plier">
	<?php echo count($messages); ?> appréciation<?php if(count($messages) > 1) echo "s"; ?> manquante<?php if(count($messages) > 1) echo "s"; ?>
</p>
<p style="text-align: center">
	<button type="button" onclick="bascule('messages')">Afficher/Cacher les appréciations manquantes</button>
</p>

<block id="messages" style="display:none;">
<?php	
	foreach ($messages as $message) {
?>
<p class="center rouge ">
	<?php echo $message; ?>
</p>
<?php		
	} ?>
</block>
<?php
	unset ($message);
}

// On crée un lien pour télécharger le fichier
 ?>
<p>
	<a class="bold"  href='../temp/<?php echo $dirTemp ; ?><?php echo $nomFichier; ?>' target='_blank'>
		Récupérer le fichier XML
	</a>
	(<em>effectuer un clic-droit/enregistrer la cible [vous pouvez supprimer le 1 de l'extension]</em>)
</p>


