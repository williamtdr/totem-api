<?php
/*
 * Endpoint: /room/permission.php
 * Arguments:
 * 		GET type: add|remove	Whether the permission is being added or removed to the target.
 * 		GET username: string	The target user to apply the permission to (case insensitive)
 * 		GET scope: string	The destination room ID.
 * 		GET level: ban|queue_ban|mute|admin|host	The permission level being added or removed.
 */

require_once '../autoload.php';

$INVALID = ['success' => false, 'reason' => "Expected parameters: type (add|remove), username, scope (room ID), level (ban|queue_ban|mute|admin|host)"];
if(!$_GET['type'] || !$_GET['username'] || !$_GET['scope'] || !$_GET['level']) json_p($INVALID);

$type = $_GET['type'];
$username = $_GET['username'];
$scope = $_GET['scope'];
$level = $_GET['level'];

if($type !== "add" && $type !== "remove") json_p(['success' => false, 'reason' => "Expected values of add or remove for parameter type."]);

$user = Auth::user();

if(!$user) json_p(["success" => false, "This endpoint requires authentication."]);

$dbManager = new DatabaseManager();

if(!$dbManager->validateScope($scope)) json_p("Invalid room name.");

switch($level) {
	case "admin":
		if(!$dbManager->isOwner($user, $scope)) json_p(['success' => false, 'reason' => "Only the room owner can appoint or demote admins."]);
		json_p($dbManager->changePermission($type, $username, $scope, DatabaseManager::PERMISSION_LEVEL_ROOM_ADMIN));
	break;
	case "host":
	case "ban":
		if(!$dbManager->isOwnerOrAdmin($user, $scope)) json_p(['success' => false, 'reason' => "You don't have permission to do this."]);
		json_p($dbManager->changePermission($type, $username, $scope, ($level === "host" ? DatabaseManager::PERMISSION_LEVEL_ROOM_HOST : DatabaseManager::PERMISSION_LEVEL_ROOM_BANNED)));
	break;
	case "queue_ban":
	case "mute":
		if(!$dbManager->isHostOrAbove($user, $scope)) json_p(['success' => false, 'reason' => "You don't have permission to do this."]);
		json_p($dbManager->changePermission($type, $username, $scope, ($level === "queue_ban" ? DatabaseManager::PERMISSION_LEVEL_ROOM_QUEUE_BANNED : DatabaseManager::PERMISSION_LEVEL_ROOM_MUTED)));
	break;
	default:
		json_p($INVALID);
}