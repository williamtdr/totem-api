<?php

class Client
{
    /**
     * @var array
     */
    private static $config = [];

    /**
     * @var
     */
    private $code;

    /**
     * @var
     */
    private $refreshToken = false;

    /**
     * @var Google_client
     */
    private $client;

    /**
     * @var bool
     */
    private $isRefresh = false;

    /**
     * @param $code
     * @param bool $isRefresh
     */
    public function __construct($code, $isRefresh = false)
    {
        $this->setCode($code)
             ->setMode($isRefresh)
             ->initClient();
    }

    /**
     * @return $this
     */
    private function initClient()
    {
        $this->client = new Google_Client();
        $this->client->setClientId(self::$config['client_id']);
        $this->client->setClientSecret(self::$config['client_secret']);

        if ($this->isAuthMode()) {
            $this->makeClientAuth()
                 ->andSetRefreshToken();
        }

        if ($this->isRefreshMode()) {
            $this->makeClientRefresh();
        }

        return $this;
    }

    /**
     * @return bool
     */
    private function isAuthMode()
    {
        return ! $this->isRefresh;
    }

    /**
     * @return $this
     */
    private function andSetRefreshToken()
    {
        if ( ! $this->isAuthMode()) {
            return $this;
        }

        $this->refreshToken = $this->client->getRefreshToken();

        return $this;
    }

    /**
     * @return $this
     */
    private function makeClientAuth()
    {
        if ( ! $this->isAuthMode()) {
            return $this;
        }

        $this->client->setRedirectUri('postmessage');
        $this->client->authenticate($this->code);
        $this->client->setAccessType('offline');


        return $this;
    }

    /**
     * @return bool
     */
    private function isRefreshMode()
    {
        return $this->isRefresh;
    }

    /**
     * @return $this
     */
    private function makeClientRefresh()
    {
        if ( ! $this->isRefreshMode()) {
            return $this;
        }

        $this->client->refreshToken($this->code);
        $this->client->verifyIdToken();

        return $this;
    }

    /**
     * @param $isRefresh
     *
     * @return $this
     */
    private function setMode($isRefresh)
    {
        $this->isRefresh = $isRefresh;

        return $this;
    }

    /**
     * @param $code
     *
     * @return $this
     */
    private function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @param array $config
     */
    public static function setCredentials(array $config)
    {
        self::$config = $config;
    }

    /**
     * @param $data
     *
     * @return Client
     */
    public static function fromRequest($data)
    {
        if ( ! isset( $data['code'] )) {
            // throw new Exception('Missing data required for authentication');
            exit( 'Missing data required for authentication' ); // temporary
        }

        return new self($data['code']);
    }

    /**
     * @param User $user
     *
     * @return Client
     */
    public static function fromUserSession(User $user)
    {
        return new self($user->refreshToken(), true);
    }

    /**
     * @return Google_client
     */
    public function client()
    {
        return $this->client;
    }

    /**
     * @return mixed
     */
    public function refreshToken()
    {
        return $this->refreshToken;
    }
}