<?php
// Call to php sdk

require_once __DIR__ . '/vendor/autoload.php';

// Api connection needed to know files duration

$client = new ApiVideo\Client\Client('your api key here', 'https://ws.api.video');

// fake value for example purpose
$secondsSinceLiveStarted = 60;

// fake live begginning time (change it with real value as shown in comment below)
//$fakeliveStartTime = strtotime('14:29:00');
$fakeliveStartTime = strtotime(date('H:i:s', time() - $secondsSinceLiveStarted));

// Determine when is located start time from now
$now = strtotime('now');
$timeInSeconds = $now - $fakeliveStartTime;

// VideoIds from your project add tags if you want to filter
if(isset($_POST['tags'])){
    $videos = $client->videos->search(
        array(
            'currentPage' => 1,
            'pageSize' => 25,
            'sortBy' => 'publishedAt',
            'sortOrder' => 'desc',
            'tags' => $_POST['tags']
        )
    );
}
else{
    $videos = $client->videos->search(
        array(
            'sortBy' => 'publishedAt',
            'sortOrder' => 'desc',
            'currentPage' => 1,
            'pageSize' => 25
        )
    );
}

//We check if content is playable before adding it in the array of videos to play
foreach ($videos as $video){
    try {
        if($client->videos->getStatus($video->videoId)->encoding->playable) $videoIds[] = $video->videoId;
    } catch (Exception $e) {
    }
}

// We'll get total duration of all videos to know where to begin the reading
$totalDuration =0;

foreach ($videoIds as $videoId){
    $status = $client->videos->getStatus($videoId);
    $duration = $status->encoding->metadata['duration'] ;
    $startTime = $totalDuration;
    $stopTime = $startTime + $duration;
    if($timeInSeconds > $startTime && $timeInSeconds < $stopTime){
        $seekTime = $timeInSeconds - $startTime;
        $videoToRead = $videoId;
    }
    $totalDuration += $duration;
}


// We reorganize the array to put first video to be read at the beginning of the array
$keys = array_keys($videoIds);
$key = array_search($videoToRead, $videoIds);
$index = array_search($key, $keys);

$newVideoIds = array_merge(array_slice($videoIds, $index), array_slice($videoIds, 0, $index));


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>api.video : How to create a fake live with my videos that starts related to a defined hour in the past</title>
    <script src="https://unpkg.com/@api.video/player-sdk"></script>
</head>
<body>
<b>--> Fake Live has began at : <?php echo date('H:i:s', time() -  $secondsSinceLiveStarted); ?> and this video is running since : <?php echo $seekTime; ?> seconds</b>
<button onclick="javascript:player.play()">play</button>
<button onclick="javascript:player.pause()">pause</button>
<button onclick="javascript:player.mute()">mute</button>
<button onclick="javascript:player.unmute()">unmute</button>
<div id="target" style="height: 500px;"></div>
</body>
<script type="text/javascript">
    var jsonArray = <?php echo json_encode($newVideoIds); ?>;
    var jsonArrayLength = jsonArray.length;

    var first = jsonArray[0];

    var seekTime = <?php echo $seekTime?>;

    var currentVideoIndex = 0;
    var defaultOptions = {
        hideTitle: true,
        // comment next command to remove chromeless
        hideControls: true,
        iframeUrl: "https://embed.api.video/${type}/${id}",
    };

    var autoplay = {
        autoplay: true,
    };
    var player = new PlayerSdk("#target", {
        ...defaultOptions,
        id: jsonArray[currentVideoIndex],
    });

    // add seek time that allow to create fake live
    player.seek(seekTime);

    player.addEventListener('ended',function() {
        onEnd(currentVideoIndex);
    });

    function onEnd(currentVideoIndex){
        console.log(currentVideoIndex + ' ended');
        player.loadConfig({
            ...autoplay,
            ...defaultOptions,
            id: nextVideoId(),
        });
    }

    function nextVideoId() {
        if (currentVideoIndex >= jsonArray.length - 1) {
            currentVideoIndex = 0;
        } else {
            currentVideoIndex++;
        }
        console.log(currentVideoIndex + ' will be loaded');
        return jsonArray[currentVideoIndex];
    }
</script>
</html>