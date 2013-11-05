<?php
require '../vendor/autoload.php';
use Swagger\Annotations as SWG;
use Swagger\Swagger;

/**************************
 * 
 *   START INITIALISATION
 *      
 ***************************/

/********************************
 * 
 * START Autoloading api classes
 * 
 ********************************/
function FREedom_autoloader($class) {
	include './Library/' . strtolower($class) . '.class.php';
}
spl_autoload_register('FREedom_autoloader');

/******************************** 
 * 
 * START config File read
 * 
 ********************************/
$configFile=new ReadConfigFile;

/********************************
 * 
 * START parameters Init
 * 
 *******************************/
$params=new Params($configFile);

/********************************
 *
* START eedomus Init
*
*******************************/
$eedomus = new eeDomus;
$eedomus_apiuser  =$params->showParam('eedomus_apiuser');
$eedomus_apisecret=$params->showParam('eedomus_apisecret');
$eedomus->setLoginInfo($eedomus_apiuser['eedomus_apiuser'],$eedomus_apisecret['eedomus_apisecret']);

/********************************
 * 
 *  START Initialisation Slim
 *  
 *********************************/
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
 * START Stored Parameters Management
 * 
 ***************************************/
	/**  * @SWG\Resource(
	 *   apiVersion="0.0.11",
	 *   swaggerVersion="1.2",
	 *   basePath="http://localhost:8080/api",
	 *   resourcePath="param",
	 *   description="Operations sur les parametres",
	 *   produces="['application/json','application/xml','text/plain','text/html']"
	* )
	*/
    /**
     * 
     * @url GET custom
     * 
     * @SWG\Api(
     *   path="/param/{paramName}",
     *   @SWG\Operation(
     *     method="GET",
     *     summary="Find pet by ID",
     *     notes="Returns a pet based on ID",
     *     type="Pet",
     *     nickname="getPetById",
     *     @SWG\Parameter(
     *       name="paramName",
     *       description="ID of pet that needs to be fetched",
     *       required=true,
     *       type="integer",
     *       format="int64",
     *       paramType="path",
     *       minimum="1.0",
     *       maximum="100000.0"
     *     ),
     *     @SWG\ResponseMessage(code=400, message="Invalid ID supplied"),
     *     @SWG\ResponseMessage(code=404, message="Param not found")
     *   )
     * )
     */
	
	
// CREATE
$app->post('/param/:id/:value', function ($id,$value)use ($app,$params) {
			$app->JsonOutput(array(
			'error' => FALSE,
              'msg' => $params->add($id,$value),
			'status' => 200,
           ));
	});

// UPDATE
$app->put('/param/:id/inc', function ($id)use ($app,$params) {
	
		$app->JsonOutput(array(
				'error' => FALSE,
				'msg' => $params->inc($id),
				'status' => 200,
	
		));
	});
$app->put('/param/:id/dec', function ($id)use ($app,$params) {
		$app->JsonOutput(array(
				'error' => FALSE,
				'msg' => $params->dec($id),
				'status' => 200,
		));
	});
$app->put('/param/:id/:value', function ($id,$value)use ($app,$params) {
	$app->JsonOutput(array(
			'error' => FALSE,
			'msg' => $params->add($id,$value),
			'status' => 200,
	));
	});

// DELETE
$app->delete('/param/:id', function ($id)use ($app,$params) {
	$app->JsonOutput(array(
			'error' => FALSE,
			'msg' => $params->delete($id),
			'status' => 200,
	));
	
	});

// GET
$app->get('/param/:id', function($id) use ($app,$params) {
		//TODO ajouter la possibilité de choisir entre XML et JSON
		$app->XmlOutput($params->showParam($id));
	});
$app->get('/param/', function() use ($app,$params) {
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
	 *   description="Operations about karotz",
	 *   produces="['application/json','application/xml','text/plain','text/html']"
	* )
	*/
	/**
	 * 
	 * @url GET custom
	 * 
	 * @SWG\Api(
	 *   path="/karotz/{paramName}",
	 *   @SWG\Operation(
	 *     method="GET",
	 *     summary="Find pet by ID",
	 *     notes="Returns a pet based on ID",
	 *     type="Pet",
	 *     nickname="karotzColorTemp",
	 *     @SWG\Parameter(
	 *       name="paramName",
	 *       description="ID of pet that needs to be fetched",
	 *       required=true,
	 *       type="integer",
	 *       format="int64",
	 *       paramType="path",
	 *       minimum="1.0",
	 *       maximum="100000.0"
	 *     ),
	 *     @SWG\ResponseMessage(code=400, message="Invalid ID supplied"),
	 *     @SWG\ResponseMessage(code=404, message="Param not found")
	 *   )
	 * )
	 */
$app->get('/karotz/colortemp', function() use ($app,$params,$eedomus)
		{
			$karotz = new karotz($params,$eedomus);
			$karotz->ColorTemp();

		});

/********************************
 * 
 * START FREEBOX PART
 * 
 *******************************/
$app->get('/freebox/wifi/off', function() use ($app)
{
	
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
 *   basePath="http://localhost/api",
 *   resourcePath="saison",
 *   description="Operations sur les saisons",
 *   produces="['application/xml']"
* )
*/
/**
 *
 * @url GET custom
 *
 * @SWG\Api(
 *   path="/saison",
 *   @SWG\Operation(
 *     method="GET",
 *     summary="Saison actuelle",
 *     notes="Retourne é la saison actuelle",
 *     nickname="Saison",
 *     @SWG\ResponseMessage(code=200, message="Succesfull return")
  *   )
 * )
 */
$app->get('/saison', function()  use ($app)
{
	$saison = new saison();
	$app->XmlOutput($saison->getSaison());
	

});
/********************************
 *
* START DOC part
*
*******************************/
$app->get('/api-docs/:resource', function($resource) use ($app)
{
	$swagger = new Swagger('.');
	header("Content-Type: application/json");
	echo $swagger->getResource($resource, array('output' => 'json'));
});
$app->get('/api-docs/', function() use ($app)
{
	$app->redirect('swagger-docs/api-docs.json');
});
/*****************************************
 * 
 * START Launching the slim application
 * 
 *****************************************/
$app->run();

?>