<?php
/**
 *
 * Classe eedomus qui permet d'utiliser les fonctions de l'API
 * @author  Mickael Vialat/Pierre Pollet
 * @version 1.0
 *
 */
class eedomus {
     var $api_user;
     var $api_secret;
     var $url_server;
     var $error;
 
     function __construct() 
     { 
          $this->url_server = "http://api.eedomus.com";
          $this->error = "";
     } 
 
     function setEeDomusServer($url_server)
     {
          $this->url_server = $url_server;
     }
 
     function autoLoginInfo()
     {
        if (isset($_GET['api_user'])) 
          $this->api_user = $_GET['api_user'];
        else
          if (isset($_POST['api_user'])) 
            $this->api_user = $_POST['api_user'];
          else
            $this->api_user = "";
 
        if (isset($_GET['api_secret'])) 
          $this->api_secret = $_GET['api_secret'];
        else
          if (isset($_POST['api_secret'])) 
            $this->api_secret = $_POST['api_secret'];
          else
            $this->api_secret = "";
 
     }
 
     function setLoginInfo($api_user,  $api_secret)
     {
          $this->api_user = $api_user;
          $this->api_secret =  $api_secret;
     }
 
     function getError()
     {
        return $this->error;
     }
 
     function getPeriphValue($periph_id)
     {
          $url =  $this->url_server."/get?action=periph.caract&periph_id=".$periph_id."&api_user=".$this->api_user."&api_secret=".$this->api_secret;
          $arr = json_decode(utf8_encode(file_get_contents($url))); 
 
          //print_r($arr);
          if ($arr->success==1)
            return $arr->body->last_value;
          else
          {
            $this->error = "Impossible de récupérer la valeur du périphérique (".$periph_id.")";
 
            return 0;
          }
     }
 
     function setPeriphValue($periph_id, $value)
     {
         $url =  $this->url_server."/set?action=periph.value&periph_id=".$periph_id."&value=".$value."&api_user=".$this->api_user."&api_secret=".$this->api_secret;
 
         return file_get_contents($url); 
     }
 
     function setPeriphMath($p1, $p2, $pr, $operator,$val)
     {
          $p1_val = $this->getPeriphValue($p1); 
          if ($val==1)
            $p2_val = $p2;
          else
            $p2_val = $this->getPeriphValue($p2); 
 
          $pr_val = 0;
 
          switch ($operator)
          {
          case 'egal' :
            $pr_val = $p1_val;
            break;
          case 'plus' :
            $pr_val = $p1_val + $p2_val;
            break;
          case 'moins' :
            $pr_val =  $p1_val - $p2_val;
            break;
          case 'div' :
            if ($p2_val!=0)
              $pr_val =  $p1_val / $p2_val;
            else
              $pr_val = $p1_val;
            break;
          case 'multi' :
            $pr_val = $p1_val * $p2_val;
            break;
          case 'echange' :
            // copie de P1 dans P2
            $this->copyPeriphValue($p1, $p2);
            
            //copie de la valeur de P2 dans p1
            $this->setPeriphValue($p1,$p2_val);
            
            break;
          }
 
          $this->setPeriphValue($pr, $pr_val);
     }
      
     function copyPeriphValue($p1, $p2)
     {
          $p1_val = $this->getPeriphValue($p1);           
          $this->setPeriphValue($p2, $p1_val);
     }
     
     
     /**
      * @brief Fonction de calcul des productions/consommations
      * 
      * Descriptif detaille
      * 
      *
      * @param IdIndex - Identifiant de l'index stocke a minuit
      * @param IdNow   - Identifiant de l'index du compteur
      * @param EnergieJour - Identifiant du device qui stocke la valeur de la journee
      * @param NbDec - Nombre de decimales a afficher
      * 
      * @return pas de retour
     */ 
     function calculEnergie($IdIndex,$IdNow,$EnergieJour,$NbDec)
     {
          $IndexMinuit= $this->GetPeriphValue($IdIndex);
          $IndexNow= $this->GetPeriphValue($IdNow);
          $ProdJour=round(($IndexNow-$IndexMinuit)/1000,$NbDec);
  
          //sauvegarde prod
          $this->setPeriphValue($EnergieJour, $ProdJour);
  
          //sauvegarde Nouvel IndexMinuit
          $this->setPeriphValue($IdIndex,$IndexNow);
     }
 
     
     /**
      * @brief Fonction de la moyenne d'un ensemble de devices
      *
      * Descriptif detaille
      *
      *
      * @param ListeDevices - Liste des devices pour lesquels il faut calculer la moyenne
      * @param DeviceResultat   - Identifiant du device résultat
      * @param NbDec - Nombre de decimales a afficher
      *
      * @return pas de retour
      */
     function Moyenne($ListeDevices,$DeviceResultat,$NbDec)
     {
     	$asArr= array();
     	$somme=0;
     	$nbdevices=0;
     	$Moyenne=0;
     	$asArr = explode( '|', $ListeDevices );
     	
       	foreach( $asArr as $DeviceId )
       	{
       		$val=0;
       		$val=$this->getPeriphValue($DeviceId);
     		$somme=$somme + $val;
     		$nbdevices=$nbdevices+1;
     	}
     	// calcul de la moyenne
     	$Moyenne=round($somme/$nbdevices,$NbDec);
     	
     	// stockage dans le périph résultat
     	$this->setPeriphValue($DeviceResultat, $Moyenne);
     }
     /**
      * @brief Fonction de calcul de la température ressentie
      *
      * Descriptif detaille
      *
      *
      * @param IdTemp - Identifiant du device de température extérieure
      * @param IdVent - Identifiant du device de vent
      * @param Unite  - Unite du vent (ms ou kh)
      *
      * @return pas de retour
      */
     function TempRessentie($IdTemp,$IdVent,$Unite)
     {
     	$Temp= $this->GetPeriphValue($IdTemp);
     	if ($unite=="ms")
     	{
     		$Vent= $this->GetPeriphValue($IdVent)*3.6;
     	}
     	else 
     	{
     		$Vent= $this->GetPeriphValue($IdVent);
     	}	
     	$TempRessentie=$Temp;
     	
     	$TempRessentie=13.12+0.6215*$Temp+(0.3965*$Temp-11.37)*pow($Vent,0.16);
     	
     	if ($TempRessentie>$Temp)
     	{
     		echo "<TEMP><TRE>".$Temp."</TRE></TEMP>";
     	}
     	else
     	{
     		echo "<TEMP><TRE>".$TempRessentie."</TRE></TEMP>";
     	}
     	
     }
}


?>
