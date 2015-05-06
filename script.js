

function bascule(elem)
{
   etat=document.getElementById(elem).style.display;
   if(etat=="none"){
	 document.getElementById(elem).style.display="block";
   }
   else{
	 document.getElementById(elem).style.display="none";
   }
}

function CocheColonneSelect($max) {
	for (var i=1;i<=$max;i++) {
		if(document.getElementById('classe_'+i)){
			document.getElementById('classe_'+i).checked = true;
		}
	}
}


function DecocheColonneSelect($max) {
	for (var k=1;k<=$max;k++) {
		if(document.getElementById('classe_'+k)){
			document.getElementById('classe_'+k).checked = false;
		}
	}
}

function CocheProfSelect($max) {
	for (var i=1;i<=$max;i++) {
		if(document.getElementById('classe_prof_'+i)){
			document.getElementById('classe_prof_'+i).checked = true;
		}
	}
}


function DecocheProfSelect($max) {
	for (var k=1;k<=$max;k++) {
		if(document.getElementById('classe_prof_'+k)){
			document.getElementById('classe_prof_'+k).checked = false;
		}
	}
}

function afficheCompetences($code) {
   alert("competences_"+$code);
   
   $competences = document.getElementById("competences_"+$code).value;
   alert($competences);
}