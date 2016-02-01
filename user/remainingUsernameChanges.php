<?php
require_once '../autoload.php';

$user = Auth::user();

if(!$user) json_p(["success" => false, "This endpoint requires authentication."]);

$dbManager = new DatabaseManager();

json_p(["data" => $dbManager->getRemainingUsernameChanges($user)]);