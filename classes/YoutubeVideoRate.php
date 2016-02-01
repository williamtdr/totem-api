<?php

class YoutubeVideoRate
{
    /**
     * @var Google_Service_YouTube
     */
    private $youtube;

    /**
     * @param Google_client $client
     */
    public function __construct(Google_client $client)
    {
        $this->initYoutube($client);
    }

    /**
     * @param $client
     *
     * @return $this
     */
    private function initYoutube($client)
    {
        $this->youtube = new Google_Service_YouTube($client);

        return $this;
    }

    /**
     * @param User $user
     *
     * @return YoutubeVideoRate
     */
    public static function fromAuthenticatedUser(User $user)
    {
        $gClient = Client::fromUserSession($user)
                         ->client();

        return new self($gClient);
    }

    /**
     * @param $videoId
     *
     * @return $this
     */
    public function like($videoId)
    {
        $this->rate($videoId, 'like');

        return $this;
    }

    /**
     * @param $videoId
     * @param $rate
     *
     * @return mixed
     * @throws Exception
     */
    public function rate($videoId, $rate)
    {
        $this->youtube->videos->rate($videoId, $rate);

        return $rate;
    }

    /**
     * @param $videoId
     *
     * @return $this
     */
    public function dislike($videoId)
    {
        $this->rate($videoId, 'dislike');

        return $this;
    }

    /**
     * @param $videoId
     *
     * @return $this
     */
    public function none($videoId)
    {
        $this->rate($videoId, 'none');

        return $this;
    }

    /**
     * @param $videoId
     *
     * @return bool
     */
    public function isLiked($videoId)
    {
        return $this->rating($videoId) == 'like';
    }

    /**
     * @param $videoId
     *
     * @return mixed
     */
    public function rating($videoId)
    {
        return $this->youtube->videos->getRating($videoId)
                                     ->current()->rating;
    }

    /**
     * @param $videoId
     *
     * @return bool
     */
    public function isDisliked($videoId)
    {
        return $this->rating($videoId) == 'dislike';
    }

    /**
     * @param $videoId
     *
     * @return bool
     */
    public function isNone($videoId)
    {
        return $this->rating($videoId) == 'none';
    }
}