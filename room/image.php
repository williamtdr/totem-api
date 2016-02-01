<?php
/*
 * Endpoint: /room/image.php
 * Arguments:
 * 		GET action: remove|check_upload|start_upload	Whether the permission is being added or removed to the target.
 * 		GET scope: string	The id of the room in question.
 * 		GET url: (optional)	string	The url of the background to remove.
 * 		GET type:	background|icon
 * 		GET ext:	string	The file extension that's being uploaded.
 * 		GET authkey: (optional)	string	The last known identifying string representing the user uploading the image.
 */

require_once '../autoload.php';

$user = Auth::user();

if(!$_GET['scope'] || !$_GET['action']) json_p(['success' => false, 'reason' => "Expected parameter scope (room id), action (remove|check_upload|start_upload)"]);
$scope = $_GET['scope'];
$action = $_GET['action'];

if($action === 'remove' && !$_GET['url']) json_p(['success' => false, 'reason' => "The remove action requires a target url."]);
if($action === 'check_upload' && !($_GET['type'] === 'background' || $_GET['type'] === 'icon')) json_p(['success' => false]);

if(!$user && $action === "remove" && !$_GET['server_override']) {
	json_p(["success" => false, "This endpoint requires authentication."]);
}

$dbManager = new DatabaseManager();
if(!$dbManager->validateScope($scope)) json_p(['success' => false, 'reason' => "Invalid room name."]);

switch($action) {
	case "check_upload":
		json_p($dbManager->checkUpload($_GET['authkey'], $scope, $_GET['type']));
	break;
	case "start_upload":
		echo $dbManager->roomUploadImage($scope, $_GET['ext'], $_GET['type']);
	break;
	case "remove":
		json_p($dbManager->removeImage($scope, $_GET['url'], $_GET['type']));
}