<?php
class Params{
	//TODO faire la gestion d'erreur
	private $db;
	private $connection;
	
	function __construct($configFile)
	{
		$structure = new NotORM_Structure_Convention(
				$primary = "ParamName" // id_$table
		);
	
		$this->connection = new PDO('mysql:host='.$configFile->showParam('Database','Host')
									   .';port='.$configFile->showParam('Database','Port')
							           .';dbname='.$configFile->showParam('Database','DBSchema'),
									    $configFile->showParam('Database','Login'), 
									    $configFile->showParam('Database','Password'));
		$this->db = new NotORM($this->connection,$structure);
	}
	
	function add($ParamName,$ParamValue)
	{
		$this->db->parameters()->insert_update(
				array("ParamName" => htmlentities(strtolower($ParamName), ENT_QUOTES,'iso-8859-1')), // unique key
				array("ParamValue" => htmlentities(strtolower($ParamValue), ENT_QUOTES,'iso-8859-1'))//, // insert values if the row doesn't exist
		); 
		return "Parameter created or updated";
	}
	
	function delete($ParamName)
	{
		//TODO gérer la possibilité de rollbacker une suppression
		$Parameter = $this->db->parameters("ParamName = ?", strtolower($ParamName))->fetch();
		$Parameter->delete();
		return "Parameter deleted";
	}
	
	function inc($ParamName){
		$dbResult=$this->db->parameters("ParamName = ?", strtolower($ParamName))->fetch();
		$ParamValue=intval($dbResult['ParamValue']);
		$ParamValue=$ParamValue + 1;
		$result=$this->add($ParamName,$ParamValue);

		return "Parameter increased by one";
	}
	
	function dec($ParamName){
		$dbResult=$this->db->parameters("ParamName = ?", strtolower($ParamName))->fetch();
		$ParamValue=intval($dbResult['ParamValue']);
		$ParamValue=$ParamValue - 1;
		$result=$this->add($ParamName,$ParamValue);
		
		return "Parameter increased by one";
	}
	
	function showParam($ParamName)
	{
		$result=array();
		if ($ParamName <> '')
		{
			$Parameter = $this->db->parameters("ParamName = ?", strtolower($ParamName))->fetch();
			if ($Parameter){
				$result=array($Parameter['ParamName'] => $Parameter['ParamValue']);
			}
			else{
				//TODO modifier le retour d'erreur pour inclure, le code http 404/410 suivant les cas
				$result=array($ParamName => "Parametre inexistant");
			}
				
		}
		else 
		{
			foreach($this->db->parameters() as $parameters) { // get all applications
				$result = $result + array($parameters['ParamName'] => $parameters['ParamValue']);
			}
		
		}
		return $result;

	}
	
	function Purge()
	{
	//TODO Gestion de la purge des paramètres en fct de la date de LastUpdated 
	}	

}



?>