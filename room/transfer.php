<?php
require_once '../autoload.php';

$user = Auth::user();

$dbManager = new DatabaseManager();
if($dbManager->isOwner($user, $_GET['scope'])) {
	$new_user = $dbManager->getUserByDisplayName($_GET['target']);
	if($new_user) json_p(["success" => false, "reason" => "New target owner not found."]);
	$dbManager->setOwner($new_user, $_GET['scope']);
	json_p(["success" => true]);
} else {
	json_p(["success" => false, "reason" => "You are not the owner of this room."]);
}