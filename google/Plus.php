<?php

class Plus
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var
     */
    private $person;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->setClient($client)
             ->setPerson();
    }

    /**
     * @return $this
     */
    private function setPerson()
    {
        $plus = new Google_Service_Plus($this->client->client());

        $this->person = $plus->people->get('me');

        return $this;
    }

    /**
     * @param $client
     *
     * @return $this
     */
    private function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @param $data
     *
     * @return Plus
     */
    public static function fromRequest($data)
    {
        $client = Client::fromRequest($data);

        return new self($client);
    }

    /**
     * @return Client
     */
    public function client()
    {
        return $this->client;
    }

    /**
     * @return Google_Service_Plus_Person
     */
    public function person()
    {
        return $this->person;
    }
}