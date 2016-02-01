<?php

class Pagination
{
    /**
     * @var string
     */
    public static $pattern = 'page_%s';

    /**
     * @param $playListId
     * @param $nextPageToken
     */
    public static function setPlayListPage($playListId, $nextPageToken)
    {
        $key = self::getSessionKey($playListId);

        if (is_null($nextPageToken)) {
            $nextPageToken = false;
        }

        $_SESSION[$key] = $nextPageToken;
    }

    /**
     * @param $playListId
     *
     * @return string
     */
    private static function getSessionKey($playListId)
    {
        return sprintf(self::$pattern, $playListId);
    }

    /**
     * Method returns NULL if no more pages.
     *
     * @param $playListId
     *
     * @return bool|null
     */
    public static function getPlayListPage($playListId)
    {
        $key = self::getSessionKey($playListId);

        if (isset( $_SESSION[$key] )) {
            return $_SESSION[$key];
        }

        return false;
    }
}