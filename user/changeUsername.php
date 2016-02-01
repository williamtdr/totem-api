<?php
require_once '../autoload.php';

$user      = Auth::user();
$dbManager = new DatabaseManager();
if ($user) {
	if($user->remainingUsernameChanges < 1) json_p(["success" => false, "reason" => "no_attempts"]);
    $freshDisplayName = $_GET['username'];

	if(!ctype_alnum(str_replace(array(' ','_'), array(''), $freshDisplayName))) {
		json_p([
		'success' => false
		]);
	}

    $status = $dbManager->changeUsername($user, $freshDisplayName);
    if ( ! is_string($status)) {
        Auth::update(['changeName' => false, 'displayName' => $freshDisplayName]);
    }

    json_p([
        'success' => $status
    ]);
} else {
    json_p([
        'success' => false
    ]);
}