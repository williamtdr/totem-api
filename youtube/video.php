<?php
require_once '../autoload.php';

$user = Auth::user();
if ( ! $user) {
    $errorMessage = 'You must be authenticated';
} elseif ( ! isset( $_GET['action'] ) || ! isset( $_GET['id'] )) {
    $errorMessage = 'Missing action / video id';
} elseif (isset( $_GET['action'] ) && $_GET['action'] == 'rate' && ! isset( $_GET['mode'] )) {
    $errorMessage = 'Rate mode is not set';
}

if ( ! isset( $errorMessage )) {
    $videoId = $_GET['id'];
    $action  = $_GET['action'];
    switch ($action) {
        default:
            $errorMessage = 'Unknown action';
            break;
        case 'status':
            $youtubeRate = YoutubeVideoRate::fromAuthenticatedUser($user);
            $rating      = $youtubeRate->rating($videoId);
            break;
        case 'rate':
            try {
                $youtubeRate = YoutubeVideoRate::fromAuthenticatedUser($user);
                $rating      = $youtubeRate->rate($videoId, $_GET['mode']);
            } catch (Google_Service_Exception $ex) {
                $errors       = $ex->getErrors();
                $errorMessage = $errors[0]['message'];
            }
            break;
    }
}

if (isset( $errorMessage )) {
    json_response([
        'success' => false,
        'message' => $errorMessage
    ]);
}

if (isset( $rating )) {
    json_response([
        'success' => true,
        'message' => $rating
    ]);
}