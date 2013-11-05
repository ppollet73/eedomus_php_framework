<?php
class readconfigfile
/**
 *
* Classe ReadConfigFile qui permet de lire le fichier de configuration
* @author  Pierre Pollet
* @version 1.0
*
*/
{
	public $ConfigParams;

	function __construct()
	{
		
		if (strtolower(substr(php_uname('s'),0,7))=='windows')
			$filename=dirname (__FILE__). "\..\config.ini";
		else
			$filename=dirname (__FILE__). "/../config.ini";
		
		// 	Analyse avec les sections
		$this->ConfigParams = parse_ini_file($filename, TRUE);
	}

	function showParam($section,$name)
	{
		return $this->ConfigParams[$section][$name];
	}

}

