<?php
require_once '../autoload.php';

$dbManager = new DatabaseManager();
$rooms     = $dbManager->getRooms();

json_p($rooms);