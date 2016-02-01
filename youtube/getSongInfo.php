<?php
require_once '../autoload.php';

$video   = YoutubeSongInfo::withId($_GET['id']);
$details = $video->details();

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

if(!checkBlacklist($details)) {
	json_response(['success' => false, 'reason' => "That song matches the blacklist in this room."]);
}

if (count($details)) {
    $details['success'] = true;
    json_response($details);
}

$response = $video->responseArray();
if (isset( $response['error'] )) {
    json_response([
        'success' => false,
        'message' => $response['error']['message']
    ]);
}
