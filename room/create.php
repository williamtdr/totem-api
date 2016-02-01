<?php
require_once '../autoload.php';

$user = Auth::user();

if (count($_GET) === 0 or ! ( isset( $_GET['name'] ) && isset( $_GET['description'] ) )) {
    // header("Location: ".$config['app_link']);
} else {
    $name        = $_GET['name'];
    $description = $_GET['description'];
    $password    = $_GET['password'];

    if (stristr($description, "<") or stristr($description, "/>")) {
        fail("< and > are disallowed for security reasons.");
    }

    if (stristr($name, "<") or stristr($name, "/>")) {
        fail("< and > are disallowed for security reasons.");
    }

    $clean_name = strtolower(str_replace(" ", "-", preg_replace("/[^0-9a-zA-Z ]/", "", $name)));

    if (strlen($clean_name) > 30) {
        fail("Room name is too long.");
    }

    if (strlen($clean_name) < 3) {
        fail("Room name is too short.");
    }

    $dbManager = new DatabaseManager();
    if ($dbManager->getRoom($clean_name)) {
        fail("A room by that name already exists.");
    }

    if (count($rooms = $dbManager->getRoomsOwnedBy($user->id())) > 0) {
        fail("There is currently a limit of one room per account. You can see or delete your room <a href=\"javascript:joinRoom('" . $rooms[0]->id . "')\">here</a>.");
    }

    $dbManager->makeRoom($clean_name, $name, $description, $user->id(), $password);

    echo $_GET['callback']."(".json_encode(array('success' => true, 'room_id' => $clean_name)).")";
}
