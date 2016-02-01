<?php
require_once '../autoload.php';

$user = Auth::user();

$dbManager = new DatabaseManager();
if($dbManager->isOwner($user, $_GET['scope'])) {
	$dbManager->deleteRoom($_GET['scope']);
	json_p(["success" => true]);
} else {
	json_p(["success" => false, "reason" => "You are not the owner of this room."]);
}