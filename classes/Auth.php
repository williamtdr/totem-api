<?php

class Auth
{
    /**
     * @var string
     */
    private static $key_auth = 'auth';

    /**
     * @param $key
     *
     * @return bool
     */
    public static function remove($key)
    {
        if (isset( $_SESSION[$key] )) {
            unset( $_SESSION[$key] );
        }

        return false;
    }

    /**
     *
     */
    public static function logout()
    {
        $_SESSION = [];
    }

    /**
     * @param array $data
     *
     * @return bool|User
     */
    public static function update(array $data)
    {
        $user = self::user();
        $user = array_merge($user->toArray(), $data);
        $user = User::fromSession($user);

        self::login($user);

        return self::user();
    }

    /**
     * @return User|bool
     */
    public static function user()
    {
        $user = self::get(self::$key_auth);
        if ( ! $user) {
            return false;
        }

        return User::fromSession($user);
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public static function get($key)
    {
        if (isset( $_SESSION[$key] )) {
            return $_SESSION[$key];
        }

        return false;
    }

    /**
     * @param User $user
     */
    public static function login(User $user)
    {
        self::set(self::$key_auth, $user->toArray());
    }

    /**
     * @param $key
     * @param $data
     */
    public static function set($key, $data)
    {
        $_SESSION[$key] = $data;
    }
}