<?php

class karotz {
	
	function ColorTemp($temp)
	{
			if ($temp <0) {
				$color='0000FF'; //Bleu
			} elseif ($temp < 5) {
				$color='00FFFF'; //Cyan
			} elseif ($temp < 10) {
				$color='00FF00'; //Vert
			} elseif ($temp < 20) {
				$color='FF00FF'; //Rose
			} elseif ($temp < 30) {
				$color='FFFF00'; //Jaune
			}else {
				$color='FF0000'; //Rouge
			}
			$file = file_get_contents("http://192.168.0.45/cgi-bin/leds?pulse=1&color=" . $color);
			echo $file;
	}
}

?>