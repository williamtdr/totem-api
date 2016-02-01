<?php
try {
    $songId = $_GET['id'];
    $clientId = "{soundcloud client ID}";
    $json = file_get_contents("http://api.soundcloud.com/tracks/$songId?client_id=$clientId");
    $json = json_decode($json);
	$duration = 0;
	$seconds = 0;
	$minutes = 0;
	$hours = 0;

	$duration_string = str_replace("PT", "", $video->getContentDetails()->duration);
	if(stristr($duration_string, "H")) {
		$hours = intval(explode("H", $duration_string)[0]);
		$minutes = intval(explode("H", explode("M", $duration_string)[0])[1]);
	} elseif(stristr($duration_string, "M")) {
		$minutes = intval(explode("M", $duration_string)[0]);
	}
	if(stristr($duration_string, "S")) $seconds = intval(explode("M", explode("S", $duration_string)[0])[1]);
	$duration += $seconds;
	$duration += $minutes * 60;
	$duration += $hours * 60 * 60;

	if(stristr($snippet->channelTitle, $snippet->title)) {
		echo json_encode(array('title' => trim(str_replace($json->title." -", "", $json->title)), 'artist' => $json->username, 'duration' => $duration, 'success' => true, 'thumbnail' => $json->artwork_url));
	} else {
		if(stristr($snippet->title, " - ")) {
			echo json_encode(array('title' => trim(explode(" - ", $snippet->title)[1]), 'artist' => trim(explode(" - ", $json->title)[0]), 'duration' => $duration, 'success' => true, 'thumbnail' => $json->artwork_url));
		} else {
			echo json_encode(array('title' => $json->title, 'artist' => $json->username, 'duration' => $duration, 'success' => true, 'thumbnail' => "https://i.ytimg.com/vi/".$_GET['id']."/hqdefault.jpg"));
		}
	}
} catch(Exception $e) {
	echo json_encode(array('success' => false));
}