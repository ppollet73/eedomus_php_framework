<?php
require '../vendor/autoload.php';

/**************************
    START INITIALISATION
 ***************************/

/********************************
 * START Autoloading api classes
********************************/
function FREedom_autoloader($class) {
	include './Library/' . strtolower($class) . '.class.php';
}
spl_autoload_register('FREedom_autoloader');

/********************************
 * START config File read
********************************/
$configFile=new ReadConfigFile;

/********************************
 * START parameters Init
********************************/
$params=new Params($configFile);

/********************************
 *  START Initialisation Slim
*********************************/
//http://stackoverflow.com/questions/6807404/slim-json-outputs
class mySlim extends Slim\Slim {
	function outputArray($data) {
		switch($this->request->headers->get('Accept')) {
			case 'application/json':
			default:
				$this->response->headers->set('Content-Type', 'application/json');
				$this->response->status('200');
				$this->response->body(json_encode($data));
				//$this->response->write(json_encode($data));
				//echo json_encode($data);
		}
	}
}
$app = new mySlim(
		array(
				'debug' => TRUE,
				'templates.path' => './Library'
		));

/********************************
 *       FIN INITIALISATION
 ********************************/



/********************************
*  START A TRIER
*********************************/
$app->get('/JSON', function () use ($app){
		$app->outputArray(array(
			'error' => FALSE,
              'msg' => 'Welcome to my json API!',
			'status' => 200,
           ));

});

$app->get('/GET', function () {
		echo "Hello World en GET";
	});
	

$app->post('/POST', function ()use ($app) {
		//echo "Hello World en POST";
	$app->outputArray(array(
			'error' => FALSE,
			'msg' => 'Welcome to my json POST API!',
			'status' => 200,
	));
	});
/*
$app->get('/hello/:name', function ($name) {
    //echo "Hello, $name";
});
 */

/**************************************
 * START Default webpage and help
***************************************/
$app->get('/', function () use ($app){
		$app->render ('FREedom/Help.php');
	
	});

/**************************************
 * START Stored Parameters Management
***************************************/

// CREATE
$app->post('/param/:id/:value', function ()use ($app) {
		
	});

// UPDATE
$app->put('/param/:id/:value', function ()use ($app) {
	
	});

// DELETE
$app->delete('/param/:id', function ()use ($app) {
	
	});

// GET
$app->get('/param/:id', function ()use ($app,$params) {
		$params->showParam();
		$xml = new SimpleXMLElement('<root/>');
		$result=array_flip($result);
		array_walk_recursive($result, array ($xml, 'addChild'));
		print $xml->asXML();
	});

$app->get('/param/', function ()use ($app,$params) {
		$params->showParam();
		$xml = new SimpleXMLElement('<root/>');
		$result=array_flip($result);
		array_walk_recursive($result, array ($xml, 'addChild'));
		print $xml->asXML();
	});	

/********************************
* START KAROTZ PART
********************************/
$app->get('/karotz/colortemp/:id', function($id)
		{
			$karotz = new karotz;
			$karotz->ColorTemp($id);

		});

/********************************
 * START FREEBOX PART
********************************/
$app->get('/freebox/wifi/off', function() use ($app)
{
	
	$app->render('api/vendor/DJMomo/ApiFreebox/freebox.php');
});

/*****************************************
* START Launching the slim application
*****************************************/
$app->run();

?>