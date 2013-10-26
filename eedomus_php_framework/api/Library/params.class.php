<?php
class Params{
	
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
				array("ParamName" => $ParamName), // unique key
				array("ParamValue" => $ParamValue)//, // insert values if the row doesn't exist
		); 
	}
	
	function delete($ParamName)
	{
		//TODO gérer la possibilité de rollbacker une suppression
		$Parameter = $this->db->parameters("ParamName = ?", $ParamName)->fetch();
		$Parameter->delete();
		
	}
	
	function showParam($ParamName,$output)
	{
		if ($ParamName <> '')
		{
			$Parameter = $this->db->parameters("ParamName = ?", $ParamName)->fetch();
			$result=$Parameter[ParamValue];
		}
		else 
		{
			$result=array();
			foreach($this->db->parameters() as $parameters) { // get all applications
				//echo "$parameters[ParamName]:$parameters[ParamValue]\n"; // print application title
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