# FREEDOM - Framework Rest EEDOMus

21/02/2014 : Version 0.3
- Ajout: 
 - Previsions meteo basée sur le travail d'Aurel (http://www.domo-blog.fr/les-previsions-meteo-avec-eedomus/)
 - Vigilance météo, basé sur le travail de Djmomo (http://www.planete-domotique.com/blog/2014/01/03/la-vigilance-meteo-dans-votre-box-domotique-evolue)

- Modifications:  
 - Update de swagger-ui (Interface de l'aide)

## Fonctionalités

- Opérations mathématiques (addition/soustraction/multiplication/division + la possibilité d'incrémenter/décrémenter des paramètres)
- capteur Internet (vitese download et ping)
- gestion de paramètres stockés en base pour s'affranchir de certains états virtuels
- export BDPV
- périphérique saison (pour affficher la saison en cours)
- Prévisions météo
- Vigilance météo
             
## Installation

- configuration mysql
 - créer une base, configurer les paramètres dans le fichier config.ini
 - initialiser la base (elle ne contient pour l'instant qu'une seule table) 
   ```
    CREATE TABLE IF NOT EXISTS `parameters` (
    `ParamName` varchar(40) NOT NULL,
    `ParamValue` varchar(200) NOT NULL,
    `Hidden` tinyint(1) NOT NULL DEFAULT '0',
    `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ParamName`)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
  ```
- configuration apache
 - changer le documentRoot pour pointer vers l'endroit ou vous avez downloader le framework, il faut que le document root pointe sur le deuxième folder eedomus_php_framework

- installation dépendances (gérées par php composer)
 - php composer.phar install

- droits d'ecriture pour le compte sous lequel est lancé le serveur web
 - sur eedomus_php_framework/eedomus_php_framework/xmlFiles
 - sur eedomus_php_framework/eedomus_php_framework/api/Library/speedtest
 
 profitez du framework en allant sur l'url http://<@ip>/api, vous serez redirigé sur la page d'aide