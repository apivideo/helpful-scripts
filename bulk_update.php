<?php
require_once __DIR__ . '/vendor/autoload.php';

// Environment choice
echo "Choose now your environment ('p' for prod or 's' for sandbox) :" . ".\n";
$handleEnv = fopen("php://stdin", "r");
$lineEnv = fgets($handleEnv);
if (trim($lineEnv) == 'p') {
    echo "Prod chosen\n";
    $apiVideoEndpoint = 'https://ws.api.video';
} elseif (trim($lineEnv) == 's') {
    echo "Sandbox chosen\n";
    $apiVideoEndpoint = 'https://sandbox.api.video';
} else {
    echo "Choice not recognized : ABORTING!\n";
    exit;
}
fclose($handleEnv);
echo "\n";

// Ask for api.video credentials
echo "Please provide your api.video key :" . "\n";
$handleApi = fopen("php://stdin", "r");
$lineApi = fgets($handleApi);
if (trim($lineApi) == '') {
    echo "ABORTING!\n";
    exit;
}
fclose($handleApi);
echo "\n";

// Create api.video client and authenticate
$apiVideoKey = trim($lineApi);
$apiVideoClient = New ApiVideo\Client\Client($apiVideoKey, $apiVideoEndpoint);
$apiVideoClient->videos->getBrowser()->getClient()->setTimeout(60);
$videos = $apiVideoClient->videos->search();


// Operation choice
echo "Choose what operation to apply on all videos :" . ".\n\n";
echo "- 'private' choice will update all videos to private mode :" . ".\n";
echo "- 'public' choice will update all videos to public mode :" . ".\n";
echo "- 'mp4on' choice will update all videos with download available :" . ".\n";
echo "- 'mp4off' choice will update all videos with download disabled :" . ".\n";


$handleFunc = fopen("php://stdin", "r");
$lineFunc = fgets($handleFunc);
if (trim($lineFunc) == 'private') {
    echo "All videos will be updated to private mode\n";
} elseif (trim($lineFunc) == 'public') {
    echo "All videos will be updated to public mode\n";
} elseif (trim($lineFunc) == 'mp4on') {
    echo "All videos will be updated with mp4Support on\n";
} elseif (trim($lineFunc) == 'mp4off') {
    echo "All videos will be updated with mp4Support off\n";
} else {
    echo "Choice not recognized : ABORTING!\n";
    exit;
}
fclose($handleFunc);
echo "\n";


foreach ($videos as $video) {
    switch (trim($lineFunc)) {
        case "private":
            $apiVideoClient->videos->setPrivate($video->videoId);
            if (!empty($apiVideoClient->videos->getLastError())) {
                echo $video->videoId . ':Error on video update :' . "\n";
                var_dump($apiVideoClient->videos->getLastError());
            }
            echo $video->videoId . " is now private \n";
            break;
        case "public":
            $apiVideoClient->videos->setPublic($video->videoId);
            if (!empty($apiVideoClient->videos->getLastError())) {
                echo $video->videoId . ': Error on video update :' . "\n";
                var_dump($apiVideoClient->videos->getLastError());
            }
            echo $video->videoId . " is now public \n";
            break;
        case "mp4on":
            if ($video->mp4Support == false) {
                $apiVideoClient->videos->update(
                    $video->videoId,
                    array('mp4Support' => true)
                );
                if (!empty($apiVideoClient->videos->getLastError())) {
                    echo $video->videoId . ': Error on mp4Support update' . "\n";
                    var_dump($apiVideoClient->videos->getLastError());
                }
                echo $video->videoId . " is now downloadable \n";
            }
            break;
        case "mp4off":
            if ($video->mp4Support == true) {
                $apiVideoClient->videos->update(
                    $video->videoId,
                    array('mp4Support' => false)
                );
                if (!empty($apiVideoClient->videos->getLastError())) {
                    echo $video->videoId . ': Error on mp4Support update' . "\n";
                    var_dump($apiVideoClient->videos->getLastError());
                }
                echo $video->videoId . " is now NOT downloadable \n";
            }
            break;
    }
}
echo "script finished\n";
