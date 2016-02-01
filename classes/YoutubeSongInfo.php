<?php

class YoutubeSongInfo
{
    /**
     * @var string
     */
    private static $link_pattern = 'https://www.googleapis.com/youtube/v3/videos?part=%s&id=%s&key=%s';

    /**
     * @var array
     */
    private static $parts = [];

    /**
     * @var
     */
    private static $youtube_api_key;

    /**
     * @var
     */
    private $response;

    /**
     * @var
     */
    private $link;

    /**
     * @var string
     */
    private $userAgent = 'Totem 1.0';

    /**
     * @var bool|array
     */
    private $video = false;

	private $videoId;

    /**
     * @var array
     */
    private $details = [];

    /**
     * @param $videoId
     */
    public function __construct($videoId)
    {
        $this->setLink($videoId)
             ->setResponse()
             ->setVideo()
             ->setDetails();
    }

    /**
     * @return $this
     */
    private function setDetails()
    {
        if ( ! $this->video) {
            return $this;
        }

        $snippet    = $this->video['snippet'];
        $determined = $this->determineArtistAndName($snippet);

        $this->details = [
            'name'       => $determined['name'],
            'artist'      => $determined['artist'],
            'thumbnail'   => $snippet['thumbnails']['default']['url'],
            'channel'     => $snippet['channelTitle'],
            'duration'    => $this->getVideoSeconds(),
			'id'		  => $this->videoId
        ];

        return $this;
    }

    /**
     * @param $snippet
     *
     * @return array
     */
    private function determineArtistAndName($snippet)
    {
        $name = $snippet['title'];
        $artist = $snippet['channelTitle'];

	    if(stristr($name, ' - ')) {
		    $parts = explode(' - ', $name);
		    $title_index = count($parts) - 1;
		    $name = $parts[$title_index];
		    $artist = $parts[$title_index - 1];
		    if(stristr($artist, '【') && stristr($artist, '】')) {
			    $artist = explode('】', $artist)[1];
		    } elseif(stristr($artist, '[') && stristr($artist, ']')) {
				$artist = explode(']', $artist)[1];
		    }
	    }

        return compact('artist', 'name');
    }

    /**
     * @return array|mixed
     */
    private function getVideoSeconds()
    {
        $seconds = 0;
        $minutes = 0;
        $hours = 0;
        $duration_string = str_replace("PT", "", $this->video['contentDetails']['duration']);
        if(stristr($duration_string, "H")) {
                $hours = intval(explode("H", $duration_string)[0]);
                $minutes = intval(explode("H", explode("M", $duration_string)[0])[1]);
        } elseif(stristr($duration_string, "M")) {
                $minutes = intval(explode("M", $duration_string)[0]);
        }
        if(stristr($duration_string, "S")) $seconds += intval(explode("M", explode("S", $duration_string)[0])[1]);
        $seconds += $minutes * 60;
        $seconds += $hours * 60 * 60;

        return $seconds;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    private static function getNumber($value)
    {
        return preg_replace('/[a-zA-Z]/', '', $value);
    }

    /**
     * @return $this
     */
    private function setVideo()
    {
        $response = $this->responseArray();
        if (isset( $response['items'][0] )) {
            $this->video = $response['items'][0];
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function responseArray()
    {
        return json_decode($this->response, true);
    }

    /**
     * @return $this
     */
    private function setResponse()
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->link,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT      => $this->userAgent,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FRESH_CONNECT  => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $this->response = curl_exec($ch);

        return $this;
    }

    /**
     * @param $videoId
     *
     * @return $this
     */
    private function setLink($videoId)
    {
        $part       = implode(',', self::$parts);
        $this->link = sprintf(self::$link_pattern, $part, $videoId, self::$youtube_api_key);
		$this->videoId = $videoId;

        return $this;
    }

    /**
     * @param $videoId
     *
     * @return YoutubeSongInfo
     */
    public static function withId($videoId)
    {
        return new self($videoId);
    }

    /**
     * @param $youtube
     */
    public static function setConfig($youtube)
    {
        self::setVideoParts($youtube['video_parts']);
        self::setYoutubeApiKey($youtube['key']);
    }

    /**
     * @param array $parts
     */
    private static function setVideoParts(array $parts)
    {
        self::$parts = $parts;
    }

    /**
     * @param $key
     */
    private static function setYoutubeApiKey($key)
    {
        self::$youtube_api_key = $key;
    }

    /**
     * @return mixed
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function details()
    {
        return $this->details;
    }
}

