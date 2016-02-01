<?php
require_once '../autoload.php';

$dbManager = new DatabaseManager();
$user = Auth::user();

if(!$user) json_p($dbManager->getDefaultSettings());
json_p($dbManager->getUserSettings($user));