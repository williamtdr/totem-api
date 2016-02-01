<?php

class Room
{
    /**
     * @var
     */
    public $id;

    /**
     * @var
     */
    public $display_name;

    /**
     * @var int
     */
    public $user_counter = 0;

    /**
     * @var
     */
    public $created_at;

    /**
     * @var
     */
    public $last_used;

    /**
     * @var
     */
    public $port;

    /**
     * @var
     */
    public $password;

    /**
     * @var array
     */
    public $listeners = array();

    /**
     * @var
     */
    public $song;

	public $blacklist;
	public $whitelist;
}
