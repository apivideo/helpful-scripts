<?php
require __DIR__ . '/vendor/autoload.php';

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}
// Environment choice
echo "Choose now your environment ('prod' or 'sandbox') :".".\n";
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

// Create api.video client and authenticate
$apiVideoKey = trim($lineApi);
$apiVideoClient = New ApiVideo\Client\Client($apiVideoKey, $apiVideoEndpoint);

$players = $apiVideoClient->players->search(array('currentPage' => 1, 'pageSize' => 50));

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


// Ask for Google drive credentials
echo "A file credentials.json corresponding to your Google Drive api credentials must be placed at the same level of this script"."\n";
echo "If you have the script type 'yes' else  type 'no'"."\n";
$handleGD = fopen ("php://stdin","r");
$lineGD = fgets($handleGD);
if(trim($lineGD) != 'yes'){
    echo "ABORTING!\n";
    echo "You must provide credentials.json : Go to https://developers.google.com/drive/api/v3/quickstart/php "."\n";
    echo "Use the 'Enable Api drive' button and get your credentials.json"."\n";
    echo "Then launch the script again"."\n";
    exit;
}
fclose($handleGD);
echo "\n";
echo "Thank you, connecting to Google Drive, you now may be prompted to get a valid token ..."."\n";


/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Google Drive API PHP Quickstart');
    $client->setScopes(Google_Service_Drive::DRIVE_READONLY);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.
    $tokenPath = 'token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
}


// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Drive($client);

// Ask for Google Drive folder to explore
echo "Please provide a Google Drive folder name to import your videos :"."\n";
$handleFolder = fopen ("php://stdin","r");
$lineFolder = fgets($handleFolder);
if(trim($lineFolder) == ''){
    echo "You must provide a folder name !\n";
    exit;
}
fclose($handleFolder);
echo "\n";
echo "Thank you, continuing...\n";

$folderName = trim($lineFolder);
$optFolder =
    array(
        'pageSize' => 10,
        'fields' => "nextPageToken, files(contentHints/thumbnail,fileExtension,iconLink,id,name,size,thumbnailLink,webContentLink,webViewLink,mimeType,parents)",
        'q' =>     "name = '".$folderName."' and mimeType = 'application/vnd.google-apps.folder'"
    );
$folders = $service->files->listFiles($optFolder);
$folderId = $folders[0]->getId();

$optParams = array(
    'pageSize' => 10,
    'fields' => "nextPageToken, files(contentHints/thumbnail,fileExtension,iconLink,id,name,size,thumbnailLink,webContentLink,webViewLink,mimeType,parents)",
    'q' => "'".$folderId."' in parents"
);

// Retrieving data from folder
$results = $service->files->listFiles($optParams);

if (count($results->getFiles()) == 0) {
    print "No files found.\n";
} else {
    print "Files:\n";
    foreach ($results->getFiles() as $file) {
        if(strpos($file->getmimeType(), 'video/') !== false) {
            $content = $service->files->get($file->getId(), array("alt" => "media"));
            printf("%s (%s)\n", $file->getName(), $file->getId());
//create temp file
            $outHandle = fopen("tmpgd.mp4", "w+");
            while (!$content->getBody()->eof()) {
                fwrite($outHandle, $content->getBody()->read(1024));
            }
            fclose($outHandle);
// Upload a video resource from local drive
            $newVideo = $apiVideoClient->videos->upload(
                'tmpgd.mp4',
                array('title' => $file->getName())
            );

            if(null === $newVideo){
                echo "An issue has been detected for file ".$file->getName()."\n";
                var_dump($apiVideoClient->videos->getLastError());
            }
            else{
                // set player theme if provided
                if(isset($playerId)){

                    $videoId = $newVideo->videoId;

                    echo "Attach player Theme: ".$playerId."\n";
                    echo "check projection ";
                    while (!$newVideo = $apiVideoClient->videos->get($videoId)) {
                        usleep(500);
                        echo ".";
                    }
                    echo 'OK'."\n";

                    $apiVideoClient->videos->update(
                        $videoId,
                        array('playerId' => $playerId)
                    );
                    echo 'Player Id '.$playerId.' attached to videoId ' . $videoId . "\n";
                }
            }

// delete temp file
            @unlink('tmpgd.mp4');
        }
    }
    echo "All Done";
}

