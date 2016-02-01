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

if(!$_GET['action']) json_p(['success' => false, 'reason' => "Expected parameter action (check_upload|start_upload)"]);
$action = $_GET['action'];

$dbManager = new DatabaseManager();

switch($action) {
	case "check_upload":
		json_p($dbManager->checkUpload($_GET['authkey'], "profile"));
	break;
	case "start_upload":
		echo $dbManager->userUploadImage($dbManager->authkeytoid($_GET['authkey']), $_GET['ext']);
	break;
}