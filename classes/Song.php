<?php

class Song
{
    /**
     *
     */
    const SOURCE_YOUTUBE = 0;

    /**
     *
     */
    const SOURCE_SOUNDCLOUD = 1;

    /**
     * @var
     */
    public $name;

    /**
     * @var
     */
    public $artist;

    /**
     * @var int
     */
    public $source = self::SOURCE_YOUTUBE;

    /**
     * @var
     */
    public $url_fragment;

    /**
     * @var
     */
    public $started_at;

    /**
     * @var
     */
    public $picture_url;
}