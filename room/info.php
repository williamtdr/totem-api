<?php
/*
 * Endpoint: /room/info.php
 * Arguments:
 * 		GET scope: string	The id of the room in question.
 * 		GET q: backgrounds|blacklist|whitelist|host|admin|mute|ban|queue_ban	The information that should be retrieved.
 */

$ERR_INVALID = ['success' => false, 'reason' => 'scope: Room ID, q: backgrounds|blacklist|whitelist|host|admin|mute|ban|queue_ban'];

if(!$_GET['q'] || !$_GET['scope']) json_p($ERR_INVALID);

$scope = $_GET['scope'];
$q = $_GET['q'];

require_once "../autoload.php";
$dbManager = new DatabaseManager();

if(!$dbManager->validateScope($scope)) json_p(['success' => false, 'reason' => "Invalid room name."]);

switch($q) {
	case "blacklist":
	case "whitelist":
		$room = $dbManager->getRoom($scope);
		json_p(['success' => true, 'data' => $room->{$q}]);
	break;
	case "host":
		json_p($dbManager->getUsersMatchingPermissionLevel($scope, DatabaseManager::PERMISSION_LEVEL_ROOM_HOST));
	break;
	case "admin":
		json_p($dbManager->getUsersMatchingPermissionLevel($scope, DatabaseManager::PERMISSION_LEVEL_ROOM_ADMIN));
	break;
	case "mute":
		json_p($dbManager->getUsersMatchingPermissionLevel($scope, DatabaseManager::PERMISSION_LEVEL_ROOM_MUTED));
	break;
	case "ban":
		json_p($dbManager->getUsersMatchingPermissionLevel($scope, DatabaseManager::PERMISSION_LEVEL_ROOM_BANNED));
	break;
	case "queue_ban":
		json_p($dbManager->getUsersMatchingPermissionLevel($scope, DatabaseManager::PERMISSION_LEVEL_ROOM_QUEUE_BANNED));
	break;
	case "backgrounds":
		json_p($dbManager->getRoomBackgrounds($scope));
	break;
	default:
		json_p($ERR_INVALID);
}