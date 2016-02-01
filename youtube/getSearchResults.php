<?php
require_once '../autoload.php';

$user    = Auth::user();
$gClient = Client::fromUserSession($user)
                 ->client();

$google_youtube = new Google_Service_YouTube($gClient);
$playlistId     = 'search'; // generic name

// default params
$params = ['maxResults' => 50, 'q' => $_GET['q'], 'type' => 'video'];

// get page token
$pageToken = Pagination::getPlayListPage($playlistId);
if (isset( $_GET['page'] )) {
    if ( ! $pageToken) {
        json_encode([]);
    }

    $params['pageToken'] = $pageToken;
}

// handle results and save next page
$resultsq = $google_youtube->search->listSearch('snippet', $params);

Pagination::setPlayListPage('search', $resultsq->getNextPageToken());

/** @var Google_Service_YouTube_SearchListResponse $results */
$results       = $resultsq->getItems();
$results_final = array();
foreach ($results as $result) {
    /** @var Google_Service_YouTube_SearchResult $result */
    $snippet = $result->getSnippet();

    /** @var Google_Service_YouTube_SearchResultSnippet $snippet */
    $thumbs = $snippet->getThumbnails();

    /** @var Google_Service_YouTube_ThumbnailDetails $thumbs */
    $link = $result->getId()->videoId;

    if ($thumbs instanceof Google_Service_YouTube_ThumbnailDetails) {
        $thumb = $thumbs->getMedium()->url;
    } else {
        $thumb = "";
    }

    $results_final[] = array(
        'by'    => $snippet->channelTitle,
        'by_id' => $snippet->channelId,
        'title' => $snippet->title,
        'link'  => $link,
        'thumb' => $thumb
    );
}

json_response($results_final);