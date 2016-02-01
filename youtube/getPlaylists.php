<?php
require_once '../autoload.php';

$user = Auth::user();
if ( ! $user) {
    json_p([
        'success' => false,
        'reason'  => 'needs_login',
    ]);
}

$playlists = [];
try {
    $gClient = Client::fromUserSession($user)
                     ->client();

    $google_youtube = new Google_Service_YouTube($gClient);
    $channel        = $google_youtube->channels->listChannels('contentDetails', ['mine' => true]);

    /** @var Google_Service_YouTube_ChannelListResponse $channel */
    $mychannel = $channel->getItems()[0];

    /** @var Google_Service_YouTube_Channel $mychannel */
    $mychanneldetails = $mychannel->getContentDetails();

    /** @var Google_Service_YouTube_ChannelContentDetails $mychanneldetails */
    $relatedplaylists = $mychanneldetails->getRelatedPlaylists();

    /** @var Google_Service_YouTube_ChannelContentDetailsRelatedPlaylists $relatedplaylists */
    $playlists["Liked Videos"] = $relatedplaylists->getLikes();
    $playlists["Favorites"]    = $relatedplaylists->getFavorites();
    foreach ($google_youtube->playlists->listPlaylists('snippet', ['mine' => true, 'maxResults' => 50])->getItems() as $item) {
        /** @var Google_Service_YouTube_PlaylistItem $item */
        $snippet           = $item->getSnippet();
        $title             = $snippet->title;
        $playlists[$title] = $item->id;
    }

    json_p([
        'success' => true,
        'data'    => $playlists
    ]);
} catch (Google_Service_Exception $e) {
    $errors = $e->getErrors();

    json_p([
        'success' => false,
        'reason'  => isset( $errors[0]['reason'] ) ? $errors[0]['reason'] : 'Unknown',
        'message' => isset( $errors[0]['message'] ) ? $errors[0]['message'] : 'Unknown',
    ]);
} catch (Exception $e) {
    json_p([
        'success' => false,
        'reason'  => 'no_account'
    ]);
}