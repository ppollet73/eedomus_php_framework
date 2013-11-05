<?php
/**
 *
 * Classe bdpv qui permet d'exporter sa production solaire vers le site BDPV
 * @author  Pierre Pollet, inspire par le code source de Cedric Locqueneux
 * @version 1.0
 *
 */
class bdpv {
    public $api_User;
    public $api_Secret;
    private $eedomus;
    var $error;
    
    //-------------------------------------------------------------------------
    function __construct(eedomus $eedomus) 
    { 
        $this->eedomus = $eedomus;
        $this->error = "";
    }
    
    //-------------------------------------------------------------------------
    function getError()
    {
        return $this->error;
    }
    
    /**
      * @brief Entree des parametres API/User de logon
      * 
      * Descriptif detaille
      * 
      *
      * @param $api_BdpvUser 	 - Identifiant API BDPV
      * @param $api_BdpvSecret   - Secret API BDPV
      * @param $UserLogin 		 - Logon site BDPV
      * @param $UserPassword 	 - Password site BDPV
      * 
      * @return la signature du message a envoyer a BDPV
     */ 
    function setBdpvLoginInfo($api_BdpvUser, $api_BdpvSecret, $UserLogin , $UserPassword)
    {
        $this->api_User = $api_BdpvUser . $UserLogin;
        $this->api_Secret = $api_BdpvSecret . $UserPassword;
        
    }
    
     /**
      * @brief Calcul de la cle du message a envoyer
      * 
      * Descriptif detaille
      * 
      *
      * @param $compteur 	   - Numero ADCO du compteur PV
      * @param $valeur_index   - Valeur de l'index du compteur PV
      * @param $timestamp 	   - Date
      
      * 
      * @return pas de retour
     */
    function GenSignature($compteur , $valeur_index, $timestamp)
    {
        $api_sig = $this->api_Secret . "ADCO" . $compteur . "DATE" . $timestamp . "INDEX" . $valeur_index . "api_demandeur" . $this->api_User;
        $api_sig_md5 = md5($api_sig);
        return  $api_sig_md5;
    }
    
     /**
      * @brief Fonction principale pour envoyer la production PV
      * 
      * Descriptif detaille
      * 
      *
      * @param $IdAdco 	 - Id du device contenant l'ADCO du compteur PV
      * @param $IdIndex  - Id du device contenant l'index du compteur PV
      * 
      * @return pas de retour
     */
    function SendProd($IdAdco,$IdIndex)
    {
        //creation du Timestamp
        $timestamp = date("dmYHi");
        
        //recup de l'index
        $valeur_index = $this->eedomus->getPeriphValue($IdIndex);
        
        
        //recup de l'id compteur
        $compteur = $this->eedomus->getPeriphValue($IdAdco);
        
        
        //Creation de la signature
        $signature=$this->GenSignature($compteur , $valeur_index, $timestamp);
        
        //Envoi de la prod vers BDPV  
        $adresse = "http://www.bdpv.fr/_service/z_teleinfo_gen.php?ADCO=" . $compteur ."&INDEX=" . $valeur_index . "&DATE=" . $timestamp . "&api_demandeur=" . $this->api_User . "&api_sig=" . $signature;
        
        $envoi = file_get_contents($adresse);

        echo $envoi;
    }

}
?>

