<?php
require_once '../autoload.php';

if (stristr($_GET['email'], "@") && stristr($_GET['email'], ".") && strlen($_GET['email']) > 5) {
    $dbmanager = new DatabaseManager();
    $dbmanager->query("INSERT IGNORE INTO `subscriptions` (`email`) VALUES ('" . $database->real_escape_string($_GET['email']) . "')");

    echo $_GET['callback'] . "(" . json_encode(array("success" => true)) . ")";
} else {
    echo $_GET['callback'] . "(" . json_encode(array("success" => false)) . ")";
}