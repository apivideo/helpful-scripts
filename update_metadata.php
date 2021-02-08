<?php

require_once __DIR__ . '/vendor/autoload.php';

echo  "UPDATE METADATA USING CSV FROM YOUR API.VIDEO ACCOUNT".".\n";
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

// Ask for metadata filename

echo "Please provide your filename (must be located in the current folder with csv file extension) :"."\n";
$handleFileName = fopen ("php://stdin","r");
$lineFileName = fgets($handleFileName);
if(trim($lineFileName) == ''){
    echo "ABORTING!\n";
    exit;
}
fclose($handleFileName);
echo "\n";


// Create api.video client and authenticate
$apiVideoKey = trim($lineApi);
$apiVideoClient = New ApiVideo\Client\Client($apiVideoKey, $apiVideoEndpoint);
$apiVideoClient->videos->getBrowser()->getClient()->setTimeout(60);

$csvVideoMetas = file('./'.trim($lineFileName));

//HANDLE FILE INTEGRITY
echo "Before all, your file will be checked to detect errors"."\n";
echo "E.g.: separator must be a tab"."\n";
echo "First row: \n";
echo "id    metanameX   metanameY metanameZ "."\n";
echo "Following rows: \n";
echo "23-24charsvideoId metavalueX   metavalueY  metavalueZ"."\n\n";
echo "We gonna check that the first column name is 'id', and then that all id fields are set and corresponds to api.video standards"."\n";
echo "READY ?(y/n)"."\n";

$handleQuest = fopen ("php://stdin","r");
$lineQuest = fgets($handleQuest);
if(trim($lineQuest) == 'y'){
    echo "Ok let's check\n";
    $firstRowTabs = explode("\t", $csvVideoMetas[0]);
    if($firstRowTabs[0] != 'id') {
        echo 'first column must be called id : ABORTING!'."\n";
        exit;
    }

    $z=1;
    foreach ($csvVideoMetas as $otherRow){
        if($otherRow != $csvVideoMetas[0]){
            $z++;
            $otherRowTabs = explode("\t", $otherRow);
            if( !preg_match('/^vi[a-zA-Z0-9]{21,22}$/', $otherRowTabs[0]) ){
                echo 'Please fix problem detected on id located line '.$z.': id must be 23/24 chars long and alphanumeric beginning by vi : ABORTING!'."\n";
                exit;
            }
        }
    }
    echo "File seems ok, proceeding ..."."\n";
}
else{
    echo "Sorry we need to check your file before anything happens : ABORTING!\n";
    exit;
}
fclose($handleQuest);
echo "\n";


//creating an array with meta keys
$cleanedMetaKeys = trim($csvVideoMetas[0]);
$cleanedMetaKeysArray = explode("\t", $cleanedMetaKeys);
foreach ($cleanedMetaKeysArray as $cleanedMetaKey){
    // line with id and metadatas keys(names)
    if($cleanedMetaKey != $cleanedMetaKeysArray[0]){
        $metaKeysArray[] = trim($cleanedMetaKey);
    }
}

foreach ($csvVideoMetas as $linedata) {
    //initialize var to fit the correct metadata key
    $i=0;

    // lines with video ids and metadatas values
    if($linedata != $csvVideoMetas[0]){
        // get the video object from video Id
        $cleanedline = trim($linedata);
        $lineTabs = explode("\t", $cleanedline);
        $videoId = $lineTabs[0];
        $videoObject = $apiVideoClient->videos->get($videoId);


        // clean metadataArray if isset
        if(isset($metaArray)) unset($metaArray);

        // check for existing metadatas on object
        if($videoObject->metadata) {
            foreach ($videoObject->metadata as $metadata) {
                $metaArray[] =  array('key' => $metadata['key'], 'value' => $metadata['value']);
            }
        }

        // read new metadatas from file to add
        foreach ($lineTabs as $linemeta){
            if($linemeta != $lineTabs[0]){
                if($linemeta !=''){
                    $metaValue = trim($linemeta);
                    // add new meta to existing array
                    $metaArray[] =  array('key' => $metaKeysArray[$i], 'value' => $metaValue);
                }
                $i++;
            }
        }

        //update metadata
        if($metaArray) {
            //$cleanarray = array_unique($metaArray);
            echo "Adding metadata to ". $videoId;
            $apiVideoClient->videos->update(
                $videoId,
                array(
                    'metadata' => $metaArray,
                )
            );
            if (!empty($apiVideoClient->videos->getLastError())) {
                echo ' -> Error on metadata update :' . "\n";
                var_dump($apiVideoClient->videos->getLastError());
            }
            else echo " : Ok"."\n";
        }
    }
}

echo "Script applied!"."\n";
