<?php
require_once '../autoload.php';

$user      = Auth::user();
$dbManager = new DatabaseManager();
if (isset( $_GET['code'] )) {
    $plus  = Plus::fromRequest($_GET);
    $gUser = User::fromGoogleAuth($plus);

    $user = $dbManager->userExists($gUser->id());
    if (!$user) {
        $user = $dbManager->makeUser($gUser);
    } else if ($gUser->refreshToken()) {
        $user = $dbManager->updateRefreshToken($gUser);
    }

	$tmp_user = $dbManager->getUser($user->id());
	$user->remainingUsernameChanges = $tmp_user->remainingUsernameChanges;

    Auth::login($user);
}

$key         = 'unauthenticated';
$displayName = 'false';
if ($user) {
    $displayName = $user->displayName();
    $key         = $dbManager->genAuthKey($user);

    if ($user->remainingUsernameChanges === 3) {
        echo "showUsernameModal();\n";
        die();
    }
}

// ...
echo "authkey = '" . $key . "';\n";
echo "display_name = '" . $displayName . "';\n";
echo "sessionComplete();\n";
