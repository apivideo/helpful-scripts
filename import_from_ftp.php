<?php

require_once __DIR__ . '/vendor/autoload.php';

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

// Environment choice
echo "Choose your environment ('prod' or 'sandbox') :".".\n";
$handleEnv = fopen ("php://stdin","r");
$lineEnv = fgets($handleEnv);
if(trim($lineEnv) == 'prod'){
    echo "Prod chosen\n";
    $apiVideoEndpoint = 'https://ws.api.video';
}
elseif(trim($lineEnv) == 'sandbox'){
    echo "Sandbox chosen\n";
    $apiVideoEndpoint = 'https://sandbox.api.video';
}
else{
    echo "Choice not recognized : ABORTING!\n";
    exit;
}
fclose($handleEnv);
echo "\n";

// Ask for api.video credentials
echo "Please provide your api.video key :"."\n";
$handleApi = fopen ("php://stdin","r");
$lineApi = fgets($handleApi);
if(trim($lineApi) == ''){
    echo "ABORTING!\n";
    exit;
}
fclose($handleApi);
echo "\n";

// Create client and authenticate
$client = New ApiVideo\Client\Client(trim($lineApi), $apiVideoEndpoint);
$players = $client->players->search(array('currentPage' => 1, 'pageSize' => 50));

// Ask for player id
if(count($players) !=0){
    echo "Do you want to apply an existing playerId on imported videos ? (y /n ) : "."\n";
    $handlePlayer = fopen ("php://stdin","r");
    $linePlayer = fgets($handlePlayer);
    fclose($handlePlayer);
    if(trim($linePlayer) == 'y'){
        echo "Choose a player Id in the following list:\n";
        foreach($players as $player){
            echo $player->playerId."\n";
        }
        echo "\n";
        echo "PlayerId : ";
        $handlePlayerId = fopen ("php://stdin","r");
        $linePlayerId = fgets($handlePlayerId);
        if(trim($linePlayerId != '')) $playerId = trim($linePlayerId);
    }
    echo "\n";
}

// Ask for ftp credentials
echo "Please provide ... \n";
echo "ftp address (ftp.mydomain.com) : ";
$handleFtpServer = fopen ("php://stdin","r");
$lineFtpServer = fgets($handleFtpServer);
if(trim($lineFtpServer) == ''){
    echo "You must provide an ftp server : ABORTING!\n";
    exit;
}
fclose($handleFtpServer);

echo "ftp username : ";
$handleFtpUserName = fopen ("php://stdin","r");
$lineFtpUserName  = fgets($handleFtpUserName);
if(trim($lineFtpUserName) == ''){
    echo "You must provide a username : ABORTING!\n";
    exit;
}
fclose($handleFtpUserName);

echo "ftp password : ";
$handleFtpUserPass = fopen ("php://stdin","r");
$lineFtpUserPass  = fgets($handleFtpUserPass);
if(trim($lineFtpUserPass) == ''){
    echo "You must provide a password : ABORTING!\n";
    exit;
}
fclose($handleFtpUserPass);

echo "Path to folder to analyze (e.g. : 'www/video') : ";
$handleFtpFolderPath = fopen ("php://stdin","r");
$lineFtpFolderPath  = fgets($handleFtpFolderPath);
if(trim($lineFtpFolderPath) == ''){
    echo "You must provide a folder path : ABORTING!\n";
    exit;
}
fclose($handleFtpFolderPath);

echo "\n";

$ftp_server = trim($lineFtpServer);
$ftp_user_name = trim($lineFtpUserName);
$ftp_user_pass = trim($lineFtpUserPass);
$conn_id = ftp_connect($ftp_server);

$folder = trim($lineFtpFolderPath);


// Ftp Ident with credentials
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

// test video mime type
function isVideo($file) {
    return is_file($file) && (0 === strpos(mime_content_type($file), 'video/'));
}

// List directory content
$contents = ftp_mlsd($conn_id, $folder);

$local_file = 'tmp_ftp.mp4';

// Check content is file and type video
foreach($contents as $content) {
	if($content['name'] != '.' && $content['name'] != '..'){
		$server_file = $content['name'];
		if (ftp_get($conn_id, $local_file, $folder.'/'.$server_file, FTP_BINARY)) {
 			   echo "File ".$local_file." has been written"." \n";
		} else {
    			echo "There was an issue writing ".$server_file."\n";
		}

		if(isVideo($local_file)){
            // Create and upload a video resource from local drive
            $video = $client->videos->upload(
                $local_file,
                array('title' => $server_file)
            );

            if(null === $video){
                var_dump($client->videos->getLastError());
            }
            else{
                @unlink($local_file);
            }
            // set player theme if provided
            if(isset($playerId)){

                $videoId = $video->videoId;

                echo "Attach player Theme: ".$playerId."\n";
                echo "check projection ";
                while (!$video = $client->videos->get($videoId)) {
                    usleep(500);
                    echo ".";
                }
                echo 'OK'."\n";

                $client->videos->update(
                    $videoId,
                    array('playerId' => $playerId)
                );
                echo 'Player Id '.$playerId.' attached to videoId ' . $videoId . "\n";
            }
        }

		else{
		    echo "File ".$server_file." is not a video"."\n";
			@unlink($local_file);
		}
	}
}
// Close ftp connection
ftp_close($conn_id);

