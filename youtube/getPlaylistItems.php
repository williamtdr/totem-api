<?php
require_once '../autoload.php';

function getPlaylistArray($playlistItems)
{
    global $my_channel_id;
    $new_playlist_items = array();
    foreach ($playlistItems as $playlistItem) {
        /** @var Google_Service_YouTube_PlaylistItem $playlistItem */
        $playlistItemSnippet = $playlistItem->getSnippet();
        /** @var Google_Service_YouTube_PlaylistItemSnippet $playlistItemSnippet */
        $thumbs = $playlistItemSnippet->getThumbnails();
        /** @var Google_Service_YouTube_ThumbnailDetails $thumbs */
        $link = $playlistItemSnippet->getResourceId();
        if ($thumbs instanceof Google_Service_YouTube_ThumbnailDetails) {
            $thumb = $thumbs->getMedium()->url;
        } else {
            $thumb = "";
        }

        if ($playlistItemSnippet->channelId === $my_channel_id) {
            $new_playlist_items[] = array(
                'title' => $playlistItemSnippet->title,
                'link'  => $link->videoId,
                'thumb' => $thumb
            );
        } else {
            $new_playlist_items[] = array(
                'by'    => $playlistItemSnippet->channelTitle,
                'by_id' => $playlistItemSnippet->channelId,
                'title' => $playlistItemSnippet->title,
                'link'  => $link->videoId,
                'thumb' => $thumb
            );
        }
    }
    return $new_playlist_items;
}

function getPlaylistItems($youtube, $playlistId)
{
    $params    = ['playlistId' => $playlistId, 'maxResults' => 50];
    $pageToken = Pagination::getPlayListPage($playlistId);

    if (isset( $_GET['page'] )) {
        if ( ! $pageToken) {
            return [];
        }

        $params['pageToken'] = $pageToken;
    }

    $playlistItems = $youtube->playlistItems
        ->listPlaylistItems('snippet,contentDetails', $params);

    $items = getPlaylistArray($playlistItems);

    Pagination::setPlayListPage($playlistId, $playlistItems->getNextPageToken());

    return $items;
}

$user    = Auth::user();
$gClient = Client::fromUserSession($user)
                 ->client();

$google_youtube = new Google_Service_YouTube($gClient);
$channel        = $google_youtube->channels->listChannels('contentDetails', ['mine' => true]);
$my_channel_id  = $channel->getItems()[0]->id;
echo $_GET['callback'] . "(" . json_encode(getPlaylistItems($google_youtube, $_GET['playlistId'])) . ")";