<?php

class update{

//TODO ajouter des fonctions pour updater la base de données
	
	function run()
	{
		exec("git pull", $git_result);
		var_dump($git_result);
		
	}
	
}

?>