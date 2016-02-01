<?php
require_once '../autoload.php';

$dbManager = new DatabaseManager();
json_p($dbManager->suggest($_GET['q']));