<?php
/*
 * Endpoint: /room/settings.php
 * Arguments:
 * 		GET action: whitelist|blacklist|password_set|password_remove|transfer_ownership	What's being done.
 * 		GET scope: string	The target room's ID.
 * 		GET password: (optional) string		Password that will be applied to the room (set_password only)
 * 		GET username: (optional) string		The username to transfer ownership to.
 */

require_once '../autoload.php';

$user = Auth::user();

if(!$_GET['action'] || !$_GET['scope']) json_p(['success' => false, 'reason' => "Expected parameters action (whitelist|blacklist|password_set|password_remove|transfer_ownership), scope (room ID)"]);
$action = $_GET['action'];
$scope = $_GET['scope'];

$dbManager = new DatabaseManager();

if(($action === 'set_password') && !($_GET['password'])) json_p(['success' => false, 'reason' => "Expected parameter: password"]);
if(($action === 'transfer_ownership') && !($_GET['username'])) json_p(['success' => false, 'reason' => "Expected parameter: username"]);

if($action === 'transfer_ownership') $target = $_GET['username'];

if(!$user) json_p(["success" => false, "This endpoint requires authentication."]);
if(!$dbManager->validateScope($scope)) json_p("Invalid room name.");

switch($action) {
	case "whitelist":
	case "blacklist":
		if(!$_GET['content']) $_GET['content'] = "";
		json_p($dbManager->manageList($action, $user, $scope, $_GET['content']));
	break;
	case "set_password":
		if(!$dbManager->isOwner($user, $scope)) json_p(['success' => false, 'reason' => "You must be the room owner to make changes to the room password."]);
		json_p($dbManager->setRoomPassword($scope, $_GET['password']));
	break;
	case "remove_password":
		if(!$dbManager->isOwner($user, $scope)) json_p(['success' => false, 'reason' => "You must be the room owner to make changes to the room password."]);
		json_p($dbManager->removeRoomPassword($scope));
	break;
	case "transfer_ownership":
		if(!$dbManager->isOwner($user, $scope)) json_p(['success' => false, 'reason' => "You must be the room owner to transfer room ownership."]);
		if($dbManager->checkUser($target)) {
			$target_obj = $dbManager->getUserByDisplayName($target);
			if($target_obj) {
				json_p($dbManager->setOwner($target_obj->id(), $scope));
			} else {
				json_p(['success' => false, 'reason' => 'The user you want to transfer to does not exist.']);
			}
		} else {
			json_p(['success' => false, 'reason' => 'Invalid transfer target.']);
		}
	break;
	case "delete":
		if(!$dbManager->isOwner($user, $scope)) json_p(['success' => false, 'reason' => "You must be the room owner to delete the room."]);
		json_p($dbManager->deleteRoom($scope));
	break;
}