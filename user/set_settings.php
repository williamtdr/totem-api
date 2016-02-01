<?php
require_once '../autoload.php';

$dbManager = new DatabaseManager();
$user = Auth::user();

if(!$user) json_p(['success' => false]);
json_p($dbManager->setUserSettings($user, $_GET));