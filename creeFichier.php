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
            $affiche = 'Dans APB '.$eleve->ine.' cet élève a changé de classe ou de groupe en cours d\'année '.($anneeAPB-1).'-'.$anneeAPB;
            echo '<p>'.$affiche.'</p>';
            ecrit($affiche."\n");
        }
        $changeClasseLSL = LSL_change_classe($eleve->ine,$anneeLSL);
        if ($changeClasseLSL) {
            $affiche = 'Dans LSL '.$eleve->ine.' cet élève a changé de classe ou de groupe en cours d\'année '.($anneeAPB-1).'-'.$anneeAPB;
            echo '<p>'.$affiche.'</p>';
            ecrit($affiche."\n");
        }
        $lastNiveau="";
        $newElv = $sxe->donnees->addChild('eleve');
        $newElv->addAttribute('id', LSL_get_ele_id($eleve));
        ecrit("\nid → ".LSL_get_ele_id($eleve)."\n");

        // récupérer les engagements
        $listeEngagements = engagementsEleve($eleve->ine);
        if(($listeEngagements->num_rows > 0)) {
            ecrit("Engagements :\n");
            $engagements = $newElv->addChild('engagements');
            if(($listeEngagements->num_rows > 0)) {
                while ($engagement = $listeEngagements->fetch_object()){	
                    $os=array(1,2,3,4,5);
                    if (in_array($engagement->id_engagement, $os)){
                        $newEngagement = $engagements->addChild('engagement');
                        $newEngagement->addAttribute('code',$engagement->code );
                        ecrit('Code engagement → '.$engagement->code."\n");
                    } else {
                        $description=substr($engagement->description,0,300);
                        $newEngagement = $engagements->addChild('engagement-autre',$description);
                        ecrit('Engagement autre → '.$description."\n");
                    }
                }
            }
        }

        // récupérer les scolarités
        $scolarites = $newElv->addChild('scolarites');
        $annees = anneesEleve($eleve->ine);
        $derniereAnnee = NULL;
        // annee , id_classe , nom_court , nom_complet , login_pp , niveau // 
        while ($annee = $annees->fetch_object()) {
            //var_dump($annee);
            //echo '<br />';
            ecrit("\n-----------------".$annee->annee."-----------------\n");
            
            if (!$derniereAnnee || $annee->niveau != $derniereAnnee) {
                // on change d'année
                $derniereAnnee = $annee->niveau;
                ecrit('année → '.$annee->annee.' nom court → '.$annee->nom_court." code MEF → ".$annee->code_mef."\n");

                // On récupère le niveau
                $getNiveau = getNiveau($annee->annee, $annee->id_classe);
                $niveau = $getNiveau->fetch_object();
                // Ne pas récupérer une année redoublée
                if ($niveau && $lastNiveau != $niveau->apb_niveau) {
                    $newScolarite = $scolarites->addChild('scolarite');
                    $newScolarite->addAttribute('annee-scolaire',$annee->annee);
                    $codePeriode=codePeriode($eleve->ine, $annee->annee);
                    $newScolarite->addAttribute('code-periode',$codePeriode);
                    ecrit("Scolarité : année scolaire → ".$annee->annee." code période → ".$codePeriode."\n");

                    $lastNiveau = $niveau->apb_niveau;
                    ecrit("Dernier niveau → ".$lastNiveau."\n");

                    $nbPeriode = 0;
                    if ($newScolarite["code-periode"] == "T") {
                            $nbPeriode = 3;
                    } elseif ($newScolarite["code-periode"] == "S") {
                            $nbPeriode = 2;
                    }

                    $newEvaluations = evaluations($eleve->ine,$annee->annee);
                    $lastMatiere = NULL;
                    $lastService = NULL;
                    $periodesNotes = NULL;
                    ecrit("nombre d'évaluation : ".$newEvaluations->num_rows."\n");

                    while ($evaluation = $newEvaluations->fetch_object()) {

                        // TODO on limite aux $evaluation de la série
                        if ('0' == $evaluation->code_sconet || 0 == intval($evaluation->code_sconet)) {
                            //var_dump($evaluation);
                            $getMatiere = getMatiere($evaluation->code_service,$annee->annee+1,'nom_complet');
                            echo "<p class='red'>L'enseignement ".$evaluation->code_service." → ";
                            echo $getMatiere.", n'est pas reconnu";
                            echo " pour l'année ".$annee->annee."-".($annee->annee+1).".";
                            echo " Vous devez régler ce problème, les notes ne sont pas exportées.</p>";
                            echo "<p class='red'>Ce problème peut rendre invalide votre fichier d'export</p>";
                            continue;
                        }

                        if (LSL_matiereDeSerie($annee->code_mef, str_pad($evaluation->code_sconet, 6, '0', STR_PAD_LEFT))) {
                            //echo 'La matière '.$annee->code_mef.'est bien enseignée';
                            ecrit("La matière ".str_pad($evaluation->code_sconet, 6, '0', STR_PAD_LEFT)." "
                                    . "est bien enseignée en ".$annee->code_mef." en ".$annee->annee."-".($annee->annee+1)."\n");
                            ecrit(count($periodesNotes)."\n");

                            if ($lastService != $evaluation->code_service) {
                                $lastService = $evaluation->code_service;
                                if ($lastMatiere != str_pad($evaluation->code_sconet, 6, '0', STR_PAD_LEFT)) {
                                    // on change de matière
                                    $periodesNotes;
                                    if ($periodesNotes) {
                                        // On vérifie si le trimestre est renseigné pour des élèves, 
                                        // si oui, on met à -1 pour cet élève
                                        PeriodeNExistePas($Periodiques, $periodesNotes, $annee, $lastService, $newScolarite);
                                    }
                                    $periodesNotes = NULL;

                                    $lastMatiere = str_pad($evaluation->code_sconet, 6, '0', STR_PAD_LEFT);
                                    $compteEleves = compteElvEval($annee->annee, $evaluation->code_service);
                                    $compteElv = $compteEleves->fetch_object();
                                    if ($compteElv->nombre){
                                        $newEval = $newScolarite->addChild('evaluation');
                                        //TODO : rechercher la modalité dans les tables LSL
                                        $code_matiere = str_pad($evaluation->code_sconet, 6, '0', STR_PAD_LEFT);
                                        $modalite = LSL_modalite($code_matiere, $annee->code_mef);
                                        if (!$modalite) {
                                                $modalite = $evaluation->modalite;
                                        }
                                        $newEval->addAttribute('modalite-election',$modalite);
                                        $newEval->addAttribute('code-matiere',$code_matiere);
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
                                        //$appAnnuelle=" ";
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
                                        $newEnseignants = $newEval->addChild('enseignants');
                                        $enseignantsPassees = getEnseignantsPassees($annee->annee, $evaluation->code_service, $newEnseignants);
                                        if (!$enseignantsPassees) {
                                            $getEnseignants = Enseignants($annee->annee, $evaluation->code_service);
                                            while ($getEnseignant = $getEnseignants->fetch_object()) {
                                                CreeNoeudProf ($getEnseignant->nom, $getEnseignant->prenom);
                                            }
                                            $getEnseignants->close();
                                        }
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
                                PeriodeNExistePas($Periodiques, $periodesNotes, $annee, $lastService, $newScolarite);
                                $periodesNotes = NULL;
                            }

                        }
                    }
                    $newEvaluations->close();
                    // on change de matière	
                    if ($periodesNotes) {
                        // On vérifie si le trimestre est renseigné pour des élèves, 
                        // si oui, on met à -1 pour cet élève
                        PeriodeNExistePas($Periodiques, $periodesNotes, $annee, $lastService, $newScolarite);
                        $periodesNotes = NULL;
		    }	
		}
                else {
                    ecrit("redoublant");
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
<p class='center grand rouge'>Validation du schema d'export → Votre fichier <?php echo $nomFichier; ?> n'est pas valide</p>
<?php	    //libxml_display_errors();

	$errors = libxml_get_errors();
	
    foreach ($errors as $error) {
        echo display_xml_error($error);
    }


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
	<?php echo count($messages); ?> appréciation<?php if(count($messages) > 1) {echo "s";} ?> manquante<?php if(count($messages) > 1) {echo "s";} ?>
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


