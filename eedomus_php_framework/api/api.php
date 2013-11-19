<?php
//to be removed
require '../vendor/autoload.php';
use Swagger\Annotations as SWG;
use Swagger\Swagger;

/**************************
 * 
 *   START INITIALISATION
 *      
 ***************************/

// Autoloading api classes
function FREedom_autoloader($class) {
	include './Library/' . strtolower($class) . '.class.php';
}
spl_autoload_register('FREedom_autoloader');

// config File read
$configFile=new ReadConfigFile;

// parameters Init
 $params=new Params($configFile);

//eedomus Init
//TODO gérer les problèmes de connexion à l'api eedomus
$eedomus = new eeDomus;
$eedomus_apiuser  =$params->showParam('eedomus_apiuser');
$eedomus_apisecret=$params->showParam('eedomus_apisecret');
$eedomus->setLoginInfo($eedomus_apiuser['eedomus_apiuser'],$eedomus_apisecret['eedomus_apisecret']);

//Slim Init
 //http://stackoverflow.com/questions/6807404/slim-json-outputs
class mySlim extends Slim\Slim {
	function JsonOutput($data) {
		switch($this->request->headers->get('Accept')) {
			case 'application/json':
			default:
				$this->response->headers->set('Content-Type', 'application/json');
				$this->response->status('200');
				$this->response->body(json_encode($data));

		}
	}
	function XmlOutput($data){
		$this->response->status('200');
		$this->response->headers->set('Content-Type', 'application/xml');
		$xml = new SimpleXMLElement('<root/>');
		$result=array_flip($data);
		array_walk_recursive($result, array ($xml, 'addChild'));
		
		$this->response->body($xml->asXML());
		
	}
}
$app = new mySlim(
		array(
				'debug' => TRUE,
				'templates.path' => './Library/CustomErrors'
		));

// notFound page Init
$app->notFound(function () use ($app) {
	//TODO améliorer la présentation de cette page
	$app->render('Custom404.html');
});

/******************************** 
 * 
 *       FIN INITIALISATION
 *       
 *********************************/

/**************************************
 * 
 * START Default webpage and help
 * 
 ***************************************/
$app->get('/', function () use ($app){
		//$app->render ('freedom/Help.php');
	    $app->redirect ('/api/help/');
	});

/**************************************
 *
 * EEDOMUS math
 *
***************************************/
/**  @SWG\Resource(
 *   apiVersion="0.0.11",
 *   swaggerVersion="1.2",
 *   basePath="http://localhost:8080/api",
 *   resourcePath="math",
 *   description="Math for eedomus",
 *   produces="['application/json','application/xml','text/plain','text/html']"
* )
*/
// Eedomus Math functions

$app->get('/math/:operator/:p1/:p2/:pr', function ($operator,$p1,$p2,$pr) use ($app,$eedomus){
	//TODO gerer correctement les retours
	    
	/**
	 *
	 * @url GET custom
	 *
	 * @SWG\Api(
	 *   path="/math/{operator}/{p1}/{p2}/{pr}",
	 *   @SWG\Operation(
	 *     method="GET",
	 *     summary="Math Operation",
	 *     notes="Execute a math operation, {operator} can be one of (egal,plus,moins,div,multi,echange)",
	 *     type="Param",
	 *     nickname="EedomusMath",
     *     @SWG\Parameter(
	 *       name="STORED_eedomus_apiuser",
	 *       description="userid for eedomus api",
	 *       required=false,
	 *       type="integer",
	 *       format="int64",
	 *       paramType="form",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\Parameter(
	 *       name="STORED_eedomus_apisecret",
	 *       description="userid for eedomus api",
	 *       required=false,
	 *       type="integer",
	 *       format="int64",
	 *       paramType="form",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\Parameter(
	 *       name="operator",
	 *       description="math operation to be done",
	 *       required=true,
	 *       type="string",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\Parameter(
	 *       name="p1",
	 *       description="API_ID of first device for operation",
	 *       required=true,
	 *       type="float",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
   	 *     @SWG\Parameter(
	 *       name="p2",
	 *       description="API_ID of second device for operation",
	 *       required=true,
	 *       type="float",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),	
     *     @SWG\Parameter(
	 *       name="pr",
	 *       description="API_ID of result device for operation",
	 *       required=true,
	 *       type="float",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\ResponseMessage(code=400, message=""),
	 *     @SWG\ResponseMessage(code=404, message="")
	 *   )
	 * )
	 */
		$eedomus->setPeriphMath($p1, $p2, $pr, $operator,0);
		$app->JsonOutput(array(
				'error' => FALSE,
				'msg' => 'operation done',
				'status' => 200,
		));
	});

$app->get('/math/set/:p1/:value', function ($p1,$value) use ($app,$eedomus){
		//TODO gerer correctement les retours
		$eedomus->setPeriphValue($p1, $value);
		$app->JsonOutput(array(
				'error' => FALSE,
				'msg' => 'operation done',
				'status' => 200,
		));
	});

/**************************************
 *
 * Energy math
 *
***************************************/
/**  @SWG\Resource(
 *   apiVersion="0.0.11",
 *   swaggerVersion="1.2",
 *   basePath="http://localhost:8080/api",
 *   resourcePath="energy",
 *   description="Operations for energy ",
 *   produces="['application/json','application/xml','text/plain','text/html']"
* )
*/
$app->get('/energy/daily/:previousindex/:indexnow/:dailytotal', function ($previousindex,$indexnow,$dailytotal) use ($app,$eedomus){
	//TODO gerer correctement les retours
		
	/**
	 *
	 * @url GET custom
	 *
	 * @SWG\Api(
	 *   path="/energy/daily/{previousindex}/{indexnow}/{dailytotal}",
	 *   @SWG\Operation(
	 *     method="GET",
	 *     summary="Daily energy",
	 *     notes="show daily energy consumption based on previous midnight index and current index",
	 *     type="Param",
	 *     nickname="EnergyDaily",
	 *     @SWG\Parameter(
	 *       name="STORED_eedomus_apiuser",
	 *       description="userid for eedomus api",
	 *       required=false,
	 *       type="integer",
	 *       format="int64",
	 *       paramType="form",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\Parameter(
	 *       name="STORED_eedomus_apisecret",
	 *       description="userid for eedomus api",
	 *       required=false,
	 *       type="integer",
	 *       format="int64",
	 *       paramType="form",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\Parameter(
	 *       name="previousindex",
	 *       description="API_ID of previous index device",
	 *       required=true,
	 *       type="string",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\Parameter(
	 *       name="indexnow",
	 *       description="API_ID of index as of now device",
	 *       required=true,
	 *       type="float",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\Parameter(
	 *       name="dailytotal",
	 *       description="API_ID of result device",
	 *       required=true,
	 *       type="float",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\ResponseMessage(code=400, message=""),
	 *     @SWG\ResponseMessage(code=404, message="")
	 *   )
	 * )
	 */
		$eedomus->calculEnergie($previousindex,$indexnow,$dailytotal,"2");
		$app->JsonOutput(array(
				'error' => FALSE,
				'msg' => 'operation done',
				'status' => 200,
		));
	});

$app->get('/energy/bdpv', function ($previousindex,$indexnow,$dailytotal) use ($app,$eedomus){
		//TODO gerer correctement les retours
		$bdpv = new bdpv($eedomus);
		$bdpv->setBdpvLoginInfo($params->value('BdpvApiUser'),$params->value('BdpvApiSecret'), $params->value('BdpvUser'), $params->value('BdpvPassword'));
		$bdpv->SendProd($params->value('IdAdco'), $params->value('IdIndex'));
		$app->JsonOutput(array(
				'error' => FALSE,
				'msg' => 'operation done',
				'status' => 200,
		));
	});


/**************************************
 *
 * Meteo part
 *
***************************************/
/**  @SWG\Resource(
 *   apiVersion="0.0.11",
 *   swaggerVersion="1.2",
 *   basePath="http://localhost:8080/api",
 *   resourcePath="meteo",
 *   description="Meteo operations",
 *   produces="['application/json','application/xml','text/plain','text/html']"
* )
*/
$app->get('/meteo/tempressentie/:temp/:wind/:unit', function ($temp,$wind,$unit) use ($app,$eedomus){
 //TODO gerer correctement le retour xml
			
	/**
	 *
	 * @url GET custom
	 *
	 * @SWG\Api(
	 *   path="/meteo/tempressentie/{temp}/{vent}/{unit}",
	 *   @SWG\Operation(
	 *     method="GET",
	 *     summary="temperature with chill effect",
	 *     notes="temperature which take into account wind effect",
	 *     type="Param",
	 *     nickname="ChillEffectTemperature",
	 *     @SWG\Parameter(
	 *       name="STORED_eedomus_apiuser",
	 *       description="userid for eedomus api",
	 *       required=false,
	 *       type="integer",
	 *       format="int64",
	 *       paramType="form",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\Parameter(
	 *       name="STORED_eedomus_apisecret",
	 *       description="userid for eedomus api",
	 *       required=false,
	 *       type="integer",
	 *       format="int64",
	 *       paramType="form",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\Parameter(
	 *       name="temp",
	 *       description="API_ID of temperature device",
	 *       required=true,
	 *       type="string",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\Parameter(
	 *       name="wind",
	 *       description="API_ID of wind device",
	 *       required=true,
	 *       type="float",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\Parameter(
	 *       name="unit",
	 *       description="unit choosen",
	 *       required=true,
	 *       type="float",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\ResponseMessage(code=400, message=""),
	 *     @SWG\ResponseMessage(code=404, message="")
	 *   )
	 * )
	 */			
		header("Content-type: text/xml;");
		$eedomus->TempRessentie($temp, $wind, $unit);
	});
	
		
	
/**************************************
 * 
 * START Stored Parameters Management
 * 
 ***************************************/
/**  @SWG\Resource(
 *   apiVersion="0.0.11",
 *   swaggerVersion="1.2",
 *   basePath="http://localhost:8080/api",
 *   resourcePath="param",
 *   description="Operations sur les parametres",
 *   produces="['application/json','application/xml','text/plain','text/html']"
* )
*/
   
$app->post('/param/:id/:value', function ($id,$value)use ($app,$params) {
	/**
	 *
	 * @url POST custom
	 *
	 * @SWG\Api(
	 *   path="/param/{paramName}/{paramValue}",
	 *   @SWG\Operation(
	 *     method="POST",
	 *     summary="Create a param",
	 *     notes="Create a new parameter stored in DB",
	 *     type="Param",
	 *     nickname="CreateParam",
	 *     @SWG\Parameter(
	 *       name="paramName",
	 *       description="Name of the parameter that need to be created",
	 *       required=true,
	 *       type="string",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\Parameter(
	 *       name="paramValue",
	 *       description="Value of the parameter to be set",
	 *       required=true,
	 *       type="float",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\ResponseMessage(code=400, message=""),
	 *     @SWG\ResponseMessage(code=404, message="")
	 *   )
	 * )
	 */
	$app->JsonOutput(array(
			'error' => FALSE,
              'msg' => $params->add($id,$value),
			'status' => 200,
           ));
	});

$app->put('/param/:id/inc', function ($id)use ($app,$params) {
	/**
	 *
	 * @url UPDATE custom
	 *
	 * @SWG\Api(
	 *   path="/param/{paramName}/inc",
	 *   @SWG\Operation(
	 *     method="PUT",
	 *     summary="increment one parameter ",
	 *     notes="This will increment {ParamName} by one",
	 *     type="Param",
	 *     nickname="IncParams",
	 *     @SWG\Parameter(
	 *       name="paramName",
	 *       description="Name of the parameter",
	 *       required=true,
	 *       type="string",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\ResponseMessage(code=400, message=""),
	 *     @SWG\ResponseMessage(code=404, message="")
	 *   )
	 * )
	 */
		$app->JsonOutput(array(
				'error' => FALSE,
				'msg' => $params->inc($id),
				'status' => 200,
	
		));
	});
	
$app->put('/param/:id/dec', function ($id)use ($app,$params) {
	/**
	 *
	 * @url UPDATE custom
	 *
	 * @SWG\Api(
	 *   path="/param/{paramName}/dec",
	 *   @SWG\Operation(
	 *     method="PUT",
	 *     summary="decrement one parameter ",
	 *     notes="This will decrement {ParamName} by one",
	 *     type="Param",
	 *     nickname="DecParam",
	 *     @SWG\Parameter(
	 *       name="paramName",
	 *       description="Name of the parameter",
	 *       required=true,
	 *       type="string",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\ResponseMessage(code=400, message=""),
	 *     @SWG\ResponseMessage(code=404, message="")
	 *   )
	 * )
	 */
	$app->JsonOutput(array(
				'error' => FALSE,
				'msg' => $params->dec($id),
				'status' => 200,
		));
	});
	
$app->put('/param/:id/:value', function ($id,$value)use ($app,$params) {
	/**
	 *
	 * @url UPDATE custom
	 *
	 * @SWG\Api(
	 *   path="/param/{paramName}/{paramValue}",
	 *   @SWG\Operation(
	 *     method="PUT",
	 *     summary="change a parameter value",
	 *     notes="This will affect {paramValue} to {paramName} ",
	 *     type="Param",
	 *     nickname="UpdateParam",
	 *     @SWG\Parameter(
	 *       name="paramName",
	 *       description="Name of the parameter",
	 *       required=true,
	 *       type="string",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\ResponseMessage(code=400, message=""),
	 *     @SWG\ResponseMessage(code=404, message="")
	 *   )
	 * )
	 */
	$app->JsonOutput(array(
			'error' => FALSE,
			'msg' => $params->add($id,$value),
			'status' => 200,
	));
	});
	
$app->delete('/param/:id', function ($id)use ($app,$params) {
	/**
	 *
	 * @url DELETE custom
	 *
	 * @SWG\Api(
	 *   path="/param/{paramName}",
	 *   @SWG\Operation(
	 *     method="DELETE",
	 *     summary="Delete a param",
	 *     notes="Delete a parameter stored in DB",
	 *     type="Param",
	 *     nickname="DeleteParam",
	 *     @SWG\Parameter(
	 *       name="paramName",
	 *       description="Name of the parameter that need to be deleted",
	 *       required=true,
	 *       type="string",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\ResponseMessage(code=400, message=""),
	 *     @SWG\ResponseMessage(code=404, message="")
	 *   )
	 * )
	 */
	$app->JsonOutput(array(
			'error' => FALSE,
			'msg' => $params->delete($id),
			'status' => 200,
	));
	
	});

$app->get('/param/:id', function($id) use ($app,$params) {
	/**
	 *
	 * @url GET custom
	 *
	 * @SWG\Api(
	 *   path="/param/{paramName}",
	 *   @SWG\Operation(
	 *     method="GET",
	 *     summary="Retrieve a param",
	 *     notes="Retrieve a parameter stored in DB",
	 *     type="Param",
	 *     nickname="RetrieveParam",
	 *     @SWG\Parameter(
	 *       name="paramName",
	 *       description="Name of the parameter that need to be retrieved",
	 *       required=true,
	 *       type="string",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\ResponseMessage(code=400, message=""),
	 *     @SWG\ResponseMessage(code=404, message="")
	 *   )
	 * )
	 */
		//TODO ajouter la possibilité de choisir entre XML et JSON
		$app->XmlOutput($params->showParam($id));
	});

$app->get('/param/', function() use ($app,$params) {

	/**
	 *
	 * @url GET custom
	 *
	 * @SWG\Api(
	 *   path="/param/",
	 *   @SWG\Operation(
	 *     method="GET",
	 *     summary="Retrieve all parameters",
	 *     notes="Retrieve all the parameters stored in DB",
	 *     type="Param",
	 *     nickname="RetrieveParams",
	 *     @SWG\ResponseMessage(code=400, message=""),
	 *     @SWG\ResponseMessage(code=404, message="")
	 *   )
	 * )
	 */
	$app->XmlOutput($params->showParam(''));
});


	
/********************************
 * 
 * START KAROTZ PART
 * 
 *******************************/
/**  * @SWG\Resource(
 *   apiVersion="1.0.0",
 *   swaggerVersion="1.2",
 *   basePath="http://localhost:8080/api",
 *   resourcePath="karotz",
 *   description="Operations on karotz",
 *   produces="['application/json','application/xml','text/plain','text/html']"
* )
*/
	
$app->get('/karotz/colortemp', function() use ($app,$params,$eedomus)
		{
			/**
			 *
			 * @url GET custom
			 *
			 * @SWG\Api(
			 *   path="/colortemp",
			 *   @SWG\Operation(
			 *     method="GET",
			 *     summary="Led depends on temperature",
			 *     notes="the led color will change depending on a temperature device",
			 *     type="ColorTemp",
			 *     nickname="karotzColorTemp",
			 *     @SWG\Parameter(
			 *       name="STORED_karotzcolortemp",
			 *       description="API ID of the temperature device",
			 *       required=false,
			 *       type="integer",
			 *       format="int64",
			 *       paramType="form",
			 *       minimum="1.0",
			 *       maximum="100000.0"
			 *     ),
			 *     @SWG\Parameter(
			 *       name="STORED_karotzip",
			 *       description="IP address of the karotz",
			 *       required=false,
			 *       type="integer",
			 *       format="int64",
			 *       paramType="path",
			 *       minimum="1.0",
			 *       maximum="100000.0"
			 *     ),
			 *     @SWG\ResponseMessage(code=400, message=""),
			 *     @SWG\ResponseMessage(code=404, message="")
			 *   )
			 * )
			 */
			$karotz = new karotz($params,$eedomus);
			$karotz->ColorTemp();

		});

/********************************
 * 
 * START FREEBOX PART
 * 
 *******************************/
	/**  * @SWG\Resource(
	 *   apiVersion="1.0.0",
	 *   swaggerVersion="1.2",
	 *   basePath="http://localhost:8080/api",
	 *   resourcePath="freebox",
	 *   description="Operations on freeboxOS, on first execution you will have to authorize the application on freebox oled screen",
	 *   produces="['application/xml']"
	* )
	*/
$app->get('/freebox/wifi/:state', function($state) use ($app)
{
	/**
	 *
	 * @url GET custom
	 *
	 * @SWG\Api(
	 *   path="/wifi/{state}",
	 *   @SWG\Operation(
	 *     method="GET",
	 *     summary="Wifi ON/OFF",
	 *     notes="Operations on freeboxOS, on first execution you will have to authorize the application on freebox oled screen",
	 *     nickname="season",
	 *     @SWG\Parameter(
	 *       name="state",
	 *       description="the wanted state for the wifi either ON or OFF",
	 *       required=true,
	 *       type="integer",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\ResponseMessage(code=200, message="Succesfull return")
	 *   )
	 * )
	 */
	$app->render('api/vendor/DJMomo/ApiFreebox/freebox.php');
});

/********************************
 *
* START Saison PART
*
*******************************/
/**  * @SWG\Resource(
 *   apiVersion="1.0.0",
 *   swaggerVersion="1.2",
 *   basePath="http://localhost:8080/api",
 *   resourcePath="saison",
 *   description="Operations on seasons",
 *   produces="['application/xml']"
* )
*/

$app->get('/saison', function()  use ($app)
{
	/**
	 *
	 * @url GET custom
	 *
	 * @SWG\Api(
	 *   path="/saison",
	 *   @SWG\Operation(
	 *     method="GET",
	 *     summary="current season",
	 *     notes="Return the current season",
	 *     nickname="season",
	 *     @SWG\ResponseMessage(code=200, message="Succesfull return")
	 *   )
	 * )
	 */
	$saison = new saison();
	$app->XmlOutput($saison->getSaison());
	

});

/********************************
 *
* START Internet PART
*
*******************************/
/**  * @SWG\Resource(
 *   apiVersion="1.0.0",
 *   swaggerVersion="1.2",
 *   basePath="http://localhost:8080/api",
 *   resourcePath="internet",
 *   description="Internet performances",
 *   produces="['application/xml']"
* )
*/

$app->get('/internet', function()  use ($app,$params)
{
	/**
	 *
	 * @url GET custom
	 *
	 * @SWG\Api(
	 *   path="/internet",
	 *   @SWG\Operation(
	 *     method="GET",
	 *     summary="current internet performances",
	 *     notes="Return the current internet performances using speedtest, <br>two parameters are updated InternetDownloadSpeed and InternetLatency,<br>values must be divided by 100 to get the real value, ",
	 *     nickname="internet",
	 *     @SWG\ResponseMessage(code=200, message="Succesfull return")
	 *   )
	 * )
	 */
	$speedtest = new speedtest();
	$result=$speedtest->run();
	$params->add("InternetDownloadSpeed",$result['download']/100);
	$params->add("InternetLatency",$result['latency']/100);
	$app->JsonOutput(array(
			'error' => FALSE,
			'msg' => 'tests results saved in parameters \'InternetDownloadSpeed\' and \'InternetLatency\'',
			'status' => 200,
	));
	
});

/********************************
*
* START DOC part
*
*******************************/
$app->get('/api-docs/:resource', function($resource) use ($app)
{
	//TODO revoir la manière de créer cette page en fonction des exemples présents dans le github de swagger-ui
	$swagger = new Swagger('.');
	header("Content-Type: application/json");
	echo $swagger->getResource($resource, array('output' => 'json'));
});
$app->get('/api-docs/', function() use ($app)
{
	$app->redirect('swagger-docs/api-docs.json');
});

/********************************
 *
* update part
*
*******************************/
$app->put('/update', function() use ($app)
{
	$update=new update();
	$test2=$update->run();
});



/*****************************************
 * 
 * START Launching the slim application
 * 
 *****************************************/
$app->run();

?>