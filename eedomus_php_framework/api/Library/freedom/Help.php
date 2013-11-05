<?php
class Help{
	
	Public $HtmlCode;
	
	function __construct(){
		$this->HtmlCode="<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">"
					//."<!DOCTYPE html PUBLIC \"\" \"\"><HTML lang=\"fr\" xmlns=\"http://www.w3.org/1999/xhtml\"><HEAD>"
					//."<META content=\"IE=10.000\" http-equiv=\"X-UA-Compatible\">"
					//."<META charset=\"iso-8859-1\">"   
					//."<META name=\"viewport\" content=\"width=device-width\">"   
					//."<META name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">"   
					."<TITLE>Aide FREEDOM - Framework Rest EEDOMus </TITLE>"  
  					."<LINK href=\"/api/Library/freedom/css/theme-base.css\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\">" 
 					."<LINK href=\"/api/Library/freedom/css/theme-medium.css\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\">" 
 					//."<META name=\"GENERATOR\" content=\"MSHTML 10.00.9200.16721\"></HEAD>" 
					."<BODY class=\"docs \">"
					."<DIV class=\"row-fluid\"><SECTION class=\"docs span9\" id=\"layout-content\">"
					."<CENTER><H1 class=\"refname\">Aide FREEDOM - Framework Rest EEDOMus</H1></CENTER><BR><BR>";
	}

	function RawAdd($Text){
		$this->HtmlCode = $this->HtmlCode
		.$Text;
	}
	
	function SectionTitle($Text){
		$this->HtmlCode = $this->HtmlCode 
		."<BR><BR><H1 class=\"refname\">".htmlentities($Text, ENT_QUOTES,'iso-8859-1')."</H1>";
	}
	
	function SectionDesc($Text){
		$this->HtmlCode = $this->HtmlCode
		."<h3 class=\"title\">".htmlentities($Text, ENT_QUOTES,'iso-8859-1')."</h3>";
	}
	
	function MethodHTTPMethod($Text){
		$this->HtmlCode = $this->HtmlCode
		."<DIV class=\"refsect1 description\">"
		."<DIV class=\"methodsynopsis dc-description\">"
		."<SPAN class=\"type\">".$Text." &nbsp;&nbsp;&nbsp;&nbsp;</SPAN>";
	}

	function MethodBody($Text){
		$this->HtmlCode = $this->HtmlCode
		."<SPAN class=\"methodname\"><STRONG>".htmlentities($Text, ENT_QUOTES,'iso-8859-1')."</STRONG></SPAN>";
	}
	
	function MethodParam($Text){
		$this->HtmlCode = $this->HtmlCode
		."<SPAN class=\"type\">".htmlentities($Text, ENT_QUOTES,'iso-8859-1')."</SPAN>";
	}
	
	function MethodDesc($Text){
		$this->HtmlCode = $this->HtmlCode
		."</DIV><P class=\"para rdfs-comment\">".htmlentities($Text, ENT_QUOTES,'iso-8859-1')."</P></DIV>";
	}


	function render(){
		echo $this->HtmlCode;
	}
}


$Aide=new Help;
/*****************************************************
 * 
 *            AIDE SUR LES PARAMETRES
 *            
*******************************************************/
$Aide->SectionTitle("Gestion des paramètres");
$Aide->SectionDesc("Les paramètres sont des variables stockées en base, ils sont donc persistents et permettent d'effectuer des opérations que l'eedomus ne connait pas encore,"
					."comme l'incrément ou le décrement (utile pour faire des comptages).");
//--------------------------------------
$Aide->MethodHTTPMethod("HTTP/POST");
$Aide->MethodBody("http://");
$Aide->MethodParam("<@FreedomIp>");
$Aide->MethodBody("/api/param/");
$Aide->MethodParam("<ParamName>/<ParamValue>");
$Aide->MethodDesc("Crée le paramètre dans la base.");
//--------------------------------------
$Aide->MethodHTTPMethod("HTTP/GET");
$Aide->MethodBody("http://");
$Aide->MethodParam("<@FreedomIp>");
$Aide->MethodBody("/api/param/");
$Aide->MethodParam("<ParamName>");
$Aide->MethodDesc("Renvoie la valeur du paramètre sous forme XML <ParaName>ParamValue</ParamName>.");
//--------------------------------------
$Aide->MethodHTTPMethod("HTTP/GET");
$Aide->MethodBody("http://");
$Aide->MethodParam("<@FreedomIp>");
$Aide->MethodBody("/api/param/");
//$Aide->MethodParam("<ParamName>");
$Aide->MethodDesc("Renvoie tous les paramètres sous forme XML <ParaName>ParamValue</ParamName>.");
//--------------------------------------
$Aide->MethodHTTPMethod("HTTP/UPDATE");
$Aide->MethodBody("http://");
$Aide->MethodParam("<@FreedomIp>");
$Aide->MethodBody("/api/param/");
$Aide->MethodParam("<ParamName>/<ParamValue>");
$Aide->MethodDesc("Update le paramètre <ParamName> avec la valeur <ParamValue>");
//--------------------------------------
$Aide->MethodHTTPMethod("HTTP/UPDATE");
$Aide->MethodBody("http://");
$Aide->MethodParam("<@FreedomIp>");
$Aide->MethodBody("/api/param/");
$Aide->MethodParam("<ParamName>");
$Aide->MethodBody("/inc");
$Aide->MethodDesc("Incrémente le paramètre <ParamName> de 1");
//--------------------------------------
$Aide->MethodHTTPMethod("HTTP/UPDATE");
$Aide->MethodBody("http://");
$Aide->MethodParam("<@FreedomIp>");
$Aide->MethodBody("/api/param/");
$Aide->MethodParam("<ParamName>");
$Aide->MethodBody("/dec");
$Aide->MethodDesc("Decrémente le paramètre <ParamName> de 1");
//--------------------------------------
$Aide->MethodHTTPMethod("HTTP/DELETE");
$Aide->MethodBody("http://");
$Aide->MethodParam("<@FreedomIp>");
$Aide->MethodBody("/api/param/");
$Aide->MethodParam("<ParamName>");
$Aide->MethodDesc("Supprime le paramètre <ParamName>");

/*****************************************************
 * 
 *            AIDE SUR KAROTZ
 *            
*******************************************************/
$Aide->SectionTitle("Karotz via OpenKarotz");
$Aide->SectionDesc("Utilisation du Lapin grâce à l'excellente api fournie par Massalia (http://openkarotz.filippi.org/)");
$Aide->MethodHTTPMethod("HTTP/GET");
$Aide->MethodBody("http://");
$Aide->MethodParam("<@FreedomIp>");
$Aide->MethodBody("/api/kartoz/colortemp");
$Aide->MethodDesc("change la couleur de la led de Karotz en fonction de la température, inspiré du post TLD de domus (http://www.touteladomotique.com/forum/viewtopic.php?f=48&t=11661&p=96542"
		."cette fonction requiert d'avoir trois paramètres en base: KarotzColorTemp -> Id du device de température, eedomus_apiuser et eedomus_apisecret ");
$Aide->RawAdd('<p class="para rdfs-comment">!!!!température!!!!');
/*****************************************************
 *
*            FINALISATION ET AFFICHAGE
*
*******************************************************/
$Aide->RawAdd("<br><a href='http://localhost:8080/api/Library/phpMyAdmin/index.php'>phpMyAdmin</a>");
$Aide->render();









?>
