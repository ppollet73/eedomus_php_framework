<?php 
/*************************************************************************************/
/*                        ### Meteo script enregistrement ###                        */
/*                                                                                   */
/*                     Developpement par Aurel@www.domo-blog.fr                      */
/*                                                                                   */
/*************************************************************************************/
class meteo{

	 public $GetData;
	 public $FileOpen;
	 public $updateFile;

function __construct($params,$eedomus){
	 	$this->parametersClass=$params;
	 	$this->eedomusClass=$eedomus;
	 }
	 
function update(){
	//ouverture de l'url
	
	$MeteoUrl=$this->parametersClass->showParam('MeteoUrl');
	$MeteoUrl=$MeteoUrl['MeteoUrl'];
	
	if($sourcexml = fopen($MeteoUrl,"r")) $this->GetData= "Ouverture source OK";
	else $this->GetData= "Ouverture source : Echec";

	//ouverture du fichier de destination
	if($destxml = fopen("xmlFiles/previsions.xml","w")) $this->FileOpen= "Ouverture destination OK";
	else $this->FileOpen ="Ouverture destnation : Echec";
	$page = "";	
	while (!feof($sourcexml)) { //on parcourt toutes les lignes
		$page .= fgets($sourcexml, 4096); // lecture du contenu de la ligne
	}
	
	//mise a jour du fichier de destination	
	if(fputs($destxml,$page)) $this->updateFile= "Ecriture destination OK" ;
	//fermeture des connexions
	fclose($destxml);
	fclose($sourcexml);
	
	return array("UrlGet"=>$this->GetData, "FileOpening"=>$this->FileOpen, "Update"=>$this->updateFile);
	
	}
}
?>
