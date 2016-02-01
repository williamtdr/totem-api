<?php
require_once '../autoload.php';

function checkBlacklist($details) {
	if($_GET['scope']) {
		$dbManager = new DatabaseManager();
		$room = $dbManager->getRoom($_GET['scope']);
		$whitelist = explode("\n", $room->whitelist);
		$blacklist = explode("\n", $room->blacklist);
		$artist = strtolower($details["artist"]);
		$name = strtolower($details["name"]);
		if(count($details)) {
			foreach($whitelist as $term) {
				if(strlen($term) > 3 && trim($term)) {
					if(stristr($name, $term) or stristr($artist, $term)) {
						return true;
					}
				}
			}
			foreach($blacklist as $term) {
				if(strlen($term) > 3 && trim($term)) {
					if(stristr($name, $term) or stristr($artist, $term)) {
						return false;
					}
				}
			}
		}
	}
	return true;
}

$failed = 0;
$info = [];
foreach(explode(",", $_GET['data']) as $data) {
	$video   = YoutubeSongInfo::withId($data);
	$details = $video->details();

	if(!checkBlacklist($data)) {
		$failed++;
		continue;
	}
	if(count($details)) {
		$details['success'] = true;
		$info[] = $details;
	}
}

json_response(["info" => $info, "failed" => $failed]);