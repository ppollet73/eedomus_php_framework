<?php

//TODO tester sur plusiers serveurs pour lisser les résultats, il faut donc augmenter le temps de run de cette page
//TODO stocker le resultat dans des paramètres et scheduler la partie recup des données
//TODO Code cleaning



class speedtest
{

    private $maxrounds = 1;
    private $downloads = "./speedtest/temp_down/";
    private $uploads = "./speedtest/upload/";
    private $datadir = "./speedtest/data/";
    private $useragent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13';
    private $speedtestServersUrl = 'http://www.speedtest.net/speedtest-servers.php';
    private $speedtestServersFile = './speedtest/testservers.xml';
    private $countryCode = 'FR';
    private $do_size = array(1 => 500, 2 => 1000, 3 => 1500, 4 => 2000, 5 => 2500, 6 => 3000, 7 => 3500, 8 => 4000, 9 => 5000, 10 => 5500, 11 => 6500, 12 => 7000, 13 => 7500, 14 =>8000, 15 =>8500, 16 => 9000, 17 => 9500, 18 => 10000, 19 => 20000);
    private $randoms = null;
    private $time = null;
    private $day = null;
    private $do_server = array();

    private $globallatency = 0;
    private $globaldownloadspeed = 0;
    private $latencies = array();
    public  $result=array();

    private $ch = null;

    public function __construct()
    {
        $this->randoms = rand(100000000000, 9999999999999);
        $this->time = time();
        $this->day = date("d-m-Y");

    }
    
    public function run()
    {
    	$this->getServers();
    	$this->testLatency();
    	$this->testDownload();
    	//$this->testUpload();
    	return($this->result);
    	
    }

    private function getServers()
    {

        //print "Getting list of speedtest.net servers..." . PHP_EOL;
        $this->speedtestServersFile=dirname(__FILE__).$this->speedtestServersFile;
        $this->downloads = dirname(__FILE__).$this->downloads;
        $this->uploads = dirname(__FILE__).$this->uploads;
        $this->datadir = dirname(__FILE__).$this->datadir ;
        
        
        if (file_get_contents($this->speedtestServersFile,true) == '') {


            $fp = fopen($this->speedtestServersFile, 'w');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->speedtestServersUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
            $contents = curl_exec($ch);
            $info = curl_getinfo($ch);

            //print "Got list: " . round($info['size_download'] / pow(1024, 1), 2) . "KB" . PHP_EOL;

            curl_close($ch);
            fclose($fp);

            // code...
        }

        $xml = simplexml_load_string(file_get_contents($this->speedtestServersFile));


        $servers = $xml->xpath('/settings/servers/server[@cc="' . $this->countryCode . '"]');


        foreach ($servers as $server) {
            $url = parse_url((string)$server['url']);
            $this->do_server[] = array(
                'name' => (string)$server['sponsor'] . " - " . (string)$server['name'],
                'url' => $url['scheme'] . '://' . $url['host'],
                'urlParts' => $url
            );

        }

        //print "Got " . count($this->do_server) . " servers for country code " . $this->countryCode . PHP_EOL;
    }

    private function testLatency()
    {
        foreach ($this->do_server as $server => $serverdetails) {
            $this->globallatency = 0;
            //print "* Testing latency for " . $serverdetails['name'] . "... " . PHP_EOL;
            $this->latency($this->maxrounds, $serverdetails);
            $this->latencies[$server] = $this->globallatency / $this->maxrounds;
            $this->globallatency = 0;
            //print PHP_EOL;
        }
        asort($this->latencies);
        $this->latencies = array_slice($this->latencies, 0, 1, true);

        //print "keeping the 1 servers that responded the fastest:" . PHP_EOL;
        foreach ($this->latencies as $key => $value) {
			
            //print $this->do_server[$key]['name'] . " at " . $value . "ms" . PHP_EOL;
        	$this->result=$this->result + array("ServerName" =>$this->do_server[$key]['name']);
            $this->result=$this->result + array("latency" =>(int)$value*100);
        }
        
    }

    private function testDownload()
    {
        foreach ($this->latencies as $key => $value) {
            $this->globaldownloadspeed = 0;
            //print "* Testing download speed for " . $this->do_server[$key]['name'] . "..." . PHP_EOL;
            $this->download(1, $this->maxrounds,$this->do_server[$key]);
            
        }
    }

    private function latency($round, $serverdetails)
    {
        $file = $this->downloads . "latency.txt";
//        $fp = fopen($file, 'w+');
//        $ch = curl_init($serverdetails['url'] . "/speedtest/latency.txt?x=" . $this->randoms);
//        curl_setopt($ch, CURLOPT_HEADER, true);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
//        curl_setopt($ch, CURLOPT_FILE, $fp);
//        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//
//        $response = curl_exec($ch);
//        $duration = curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000;
        $duration = $this->ping($serverdetails['urlParts']['host']);

//        curl_close($ch);
//        fclose($fp);
//        unlink($file);

        //print round($duration, 2) . "ms ";

        $this->globallatency += $duration;
        if ($round > 1) {
            $this->latency(--$round, $serverdetails);
        } else {
           // print "\tAverage:" . round($this->globallatency / $this->maxrounds, 2) . "ms ";
        }
    }

    private function download($size, $round, $serverdetails)
    {
        global $globaldownloadspeed;

        $file = $this->downloads . "fails_" . $size . ".jpg";

        $fp = fopen($file, 'w+');
        $ln = $serverdetails['url'] . "/speedtest/random" . $this->do_size[$size] . "x" . $this->do_size[$size] . ".jpg?x=" . $this->randoms . "-" . $size;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ln);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);

        $duration = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        $downloadSize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
        $downloadSpeed = curl_getinfo($ch, CURLINFO_SPEED_DOWNLOAD);
        $downloadSpeed = ((($downloadSpeed * 8) / 1000) / 1000);

        // echo "Curl says avg DL speed was " . $downloadSpeed . PHP_EOL;

        if ($response === false) {
            //print "Request failed: ".curl_error( $ch ) . PHP_EOL;
        }
        curl_close($ch);
        fclose($fp);

        //unlink($file);

        if ($duration < 4 && $size != 19) {
            $this->download(++$size, $round, $serverdetails);
        } else {
            //logResults(round($downloadSpeed, 2), "d");
            if ($duration < 4) {
//                print "Duration is " . round($duration, 2) . "sec - this may introduce errors." . PHP_EOL;
            }
           // print round($downloadSize / pow(1024, 2), 2) . "MB took " . round($duration, 2) . " seconds at " . round($downloadSpeed, 2) . "Mbps" . PHP_EOL;
            $this->globaldownloadspeed += $downloadSpeed;
            if ($round > 1) {
                $this->download($size, --$round, $serverdetails);
            } else {
                //print "\tAverage: " . round($this->globaldownloadspeed / $this->maxrounds, 2) . " Mbps." . PHP_EOL;
                $this->result=$this->result + array("download" =>(int) round($this->globaldownloadspeed / $this->maxrounds, 2)*100);
            }
        }
    }

    private function ping($host)
    {
        $package = "\x08\x00\x19\x2f\x00\x00\x00\x00\x70\x69\x6e\x67";

        /* create the socket, the last '1' denotes ICMP */
        $socket = socket_create(AF_INET, SOCK_RAW, 1);

        $sec = 0;
        $usec = 500 * 1000;

        /* set socket receive timeout to 1 second */
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" =>$sec, "usec" => $usec));

        /* connect to socket */
        socket_connect($socket, $host, null);

        /* record start time */
        list($start_usec, $start_sec) = explode(" ", microtime());
        $start_time = ((float)$start_usec + (float)$start_sec);


        socket_send($socket, $package, strlen($package), 0);

        if (@socket_read($socket, 255)) {
            list($end_usec, $end_sec) = explode(" ", microtime());
            $end_time = ((float)$end_usec + (float)$end_sec);

            $total_time = $end_time - $start_time;

            return round($total_time * 1000,2);
        } else {
            return round((((float)$sec  * 1000)+((float)$usec / 1000)),2);
        }

        socket_close($socket);
    }

}


function upload($size, $round)
{
	global $server, $uploads, $do_server, $serverdetails, $iface, $randoms, $globaluploadspeed, $maxrounds;

	$file = $uploads . "upload_" . $size;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_VERBOSE, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
	curl_setopt($ch, CURLOPT_URL, $serverdetails['url'] . $serverdetails['urlParts']['path'] . "?x=0." . $randoms);
	curl_setopt($ch, CURLOPT_POST, true);
	$post = array(
			"file_box" => "@" . $file,
	);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

	$response = curl_exec($ch);

	$duration = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
	$uploadSize = curl_getinfo($ch, CURLINFO_SIZE_UPLOAD);
	$uploadSpeed = curl_getinfo($ch, CURLINFO_SPEED_UPLOAD);
	$uploadSpeed = ($uploadSpeed * 8) / pow(1000, 2);

	if ($response === false) {
		// print "Request failed: ".curl_error( $ch ) . PHP_EOL;
	}

	if ($duration < 4 && $size != 8) {
		upload(++$size, $round);
	} else {

		//logResults(round($uploadSpeed, 2), "u");
		if ($duration < 4) {
			print "Duration is " . round($duration, 2) . "sec - this may introduce errors.\n";
		}
		print round($uploadSize / pow(1024, 2), 2) . "MB took " . round($duration, 2) . " seconds at " . round($uploadSpeed, 2) . "Mbps" . PHP_EOL;
		$globaluploadspeed += $uploadSpeed;
		if ($round > 1) {
			upload($size, --$round);
		} else {
			print "\tAverage: " . round($globaluploadspeed / $maxrounds, 2) . " Mbps.\n";
		}
	}
}


function logResults($data, $updown)
{ // u - upload; d - download
    global $day, $time, $datadir, $iface;
    $fp = fopen($datadir . "data_" . $day . ".txt", "a");
    fwrite($fp, $time . "|" . $updown . "|" . $iface . "|" . $data . "\n");
    fclose($fp);
}



?>
