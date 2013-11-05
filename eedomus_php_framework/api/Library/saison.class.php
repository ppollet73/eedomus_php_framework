<?php
// based on http://www.phpsources.org/scripts312-PHP.htm
class saison {

	public $limits= array('/12/21'=>'Hiver',
			'/09/21'=>'Automne',
			'/06/21'=>'Eté',
			'/03/21'=>'Printemps',
			'/01/01'=>'Hiver');
	public $adate;

	function getSaison(){
	$adate = date('Y/m/d');
	foreach ($this->limits AS $key => $value) 
		{
			$limit=date("Y").$key;
			if (strtotime($adate)>=strtotime($limit)) 
				{
					return array("Saison"=>$value);
				}
		}
	}

}
