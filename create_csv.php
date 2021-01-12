<?php

require_once __DIR__ . '/vendor/autoload.php';

echo  "EXPORT CSV DETAILS FROM YOUR API.VIDEO ACCOUNT".".\n";
// Environment choice
echo "Choose now your environment ('p' for prod or 's' for sandbox) :".".\n";
$handleEnv = fopen ("php://stdin","r");
$lineEnv = fgets($handleEnv);
if(trim($lineEnv) == 'p'){
    echo "Prod chosen\n";
    $apiVideoEndpoint = 'https://ws.api.video';
}
elseif(trim($lineEnv) == 's'){
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
$apiVideoClient->videos->getBrowser()->getClient()->setTimeout(60);
$videos = $apiVideoClient->videos->search();

$csv = fopen('./project_details.csv', 'a');

$names= array(
    'id',
    'title',
    'createdAt',
    'iframe',
    'hls',
    'thumbnail',
    'misc'
);

fputcsv($csv, $names, $delimiter = ',', $enclosure = '"');

echo  "Building CSV :".".\n";
foreach($videos as $video)
{
    $values = array(
        $video->videoId,
        $video->title,
        ($video->publishedAt)->format('Y-m-d H:i:sP'),
        $video->assets['iframe'],
        $video->assets['hls'],
        $video->assets['thumbnail'],
    );

    if($video->mp4Support && $video->assets['mp4']){
        $values[]= 'mp4: '.$video->assets['mp4'];
    }
    else{}

    if($video->metadata) {
        foreach ($video->metadata as $metadata) {
                $values[] =  $metadata['key'] . ':  ' . $metadata['value'];
            }
    }
    fputcsv($csv, $values, $delimiter = ',', $enclosure = '"');
}

echo "Done - created file : project_details.csv.\n";
