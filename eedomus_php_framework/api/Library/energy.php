<?php
class energy{
	function EJP(){
		// URL des pages à parser
		$URL_obs = "http://particuliers.edf.com/gestion-de-mon-contrat/options-tempo-et-ejp/option-ejp/l-observatoire-2584.html";
		$URL_histo = "http://edf-ejp-tempo.sfr-sh.fr/index.php?m=eh";
		$URL_tempo = "http://particuliers.edf.com/gestion-de-mon-contrat/options-tempo-et-ejp/option-tempo/la-couleur-du-jour-2585.html";
		
		// Ordre des zones sur la page
		$zones = array("nord","paca","ouest","sud");
		$zones_tempo = array("bleu","blanc","rouge");
		
		// Extraction des données
		// Etat EJP
		$page = file_get_contents($URL_obs);
		preg_match_all("/.*FRONT\/NetExpress\/img\/ejp_(.*).png.*/", $page, $matches);
		$ejp = $matches[1];
		
		// Nombre de jours restants EJP
		$page = file_get_contents($URL_histo);
		preg_match_all("/.*<td.*>(\d+)<\/td>.*/", $page, $matches);
		$ejp_jours = $matches[1];
		
		// Etat TEMPO
		$page = file_get_contents($URL_tempo);
		preg_match_all("/.*<span class=\"period\">(.*)<\/span>.*/", $page, $matches);
		$tempo_aujourdhui = str_replace(utf8_decode("non déterminé"),"nd",strtolower(trim(utf8_decode($matches[1][1]))));
		$tempo_demain = str_replace(utf8_decode("non déterminé"),"nd",strtolower(trim(utf8_decode($matches[1][4]))));
		
		// Nombre de jours restants TEMPO
		$page = file_get_contents($URL_tempo);
		preg_match_all("/.*<strong>(\d+)<\/strong>.*<strong>(\d+)<\/strong>.*/", $page, $matches);
		$tempo_jours_restant = $matches[1];
		$tempo_jours_total = $matches[2];
		
		// Création données XML
		// Instance de la class DomDocument
		$doc = new DOMDocument();
		
		// Definition de la version et de l'encodage
		$doc->version = '1.0';
		$doc->encoding = 'UTF-8';
		$doc->formatOutput = true;
		
		// Ajout de commentaires a la racine
		$comment_elt = $doc->createComment(utf8_encode('Etat des zones EJP pour aujourdhui, demain et nombre de jours restants.'));
		$doc->appendChild($comment_elt);
		$comment_elt = $doc->createComment(utf8_encode('Etat des jours TEMPO pour aujourdhui et demain et nombre restant pour chaque couleur.'));
		$doc->appendChild($comment_elt);
		$comment_elt = $doc->createComment(utf8_encode('https://github.com/DjMomo/EJP_to_XML'));
		$doc->appendChild($comment_elt);
		
		// Création noeud principal
		$racine = $doc->createElement('ejp_tempo');
		
		// Ajout la balise 'update' a la racine
		$version_elt = $doc->createElement('update',date("Y-m-d H:i"));
		$racine->appendChild($version_elt);
		$ejp_XML = $doc->createElement('ejp');
		
		// Données EJP
		for($i = 0;$i<sizeof($zones); $i++)
		{
			$j = $i+7;
		
			// Zones
			$zone = $doc->createElement($zones[$i]);
			$aujourdhui = $doc->createElement('aujourdhui', $ejp[$i]);
			($ejp[$i] === "oui") ? $bool = 1 : $bool = 0;
			$aujourdhui_bool = $doc->createElement('aujourdhui_bool', $bool);
			$demain = $doc->createElement('demain', $ejp[$j]);
			($ejp[$j] === "oui") ? $bool = 1 : $bool = 0;
			$demain_bool = $doc->createElement('demain_bool', $bool);
			$jours_restants = $doc->createElement('jours_restants', $ejp_jours[$i]);
			$zone->appendChild($aujourdhui);
			$zone->appendChild($aujourdhui_bool);
			$zone->appendChild($demain);
			$zone->appendChild($demain_bool);
			$zone->appendChild($jours_restants);
			$ejp_XML->appendChild($zone);
		}
		$racine->appendChild($ejp_XML);
		
		// Données TEMPO
		$tempo = $doc->createElement('tempo');
		$aujourdhui = $doc->createElement('aujourdhui', $tempo_aujourdhui);
		$demain = $doc->createElement('demain', $tempo_demain);
		$tempo->appendChild($aujourdhui);
		$tempo->appendChild($demain);
		
		for ($i = 0; $i<sizeof($zones_tempo);$i++)
		{
			$jours_restant = $doc->createElement($zones_tempo[$i].'_restant', $tempo_jours_restant[$i]);
			$jours_total = $doc->createElement($zones_tempo[$i].'_total', $tempo_jours_total[$i]);
			$tempo->appendChild($jours_restant);
			$tempo->appendChild($jours_total);
		}
		$racine->appendChild($tempo);
		
		// Fermeture noeud principal
		$doc->appendChild($racine);
		
		// Affichage XML
		echo $doc->saveXML();
	}
}

?>