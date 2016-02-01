<?php

//class User
//{
//    /**
//     * @var
//     */
//    public $id;
//
//    /**
//     * @var
//     */
//    public $display_name;
//
//    /**
//     * @var
//     */
//    public $email;
//
//    /**
//     * @var
//     */
//    public $avatar_link;
//
//    /**
//     * @var
//     */
//    public $can_change_username;
//}

class User
{
	public $remainingUsernameChanges;
    private $email;
    private $displayName;
    private $avatar;
    private $id;
    private $arrayed = [];
    private $refreshToken = false;

    /**
     * @param $email
     * @param $displayName
     * @param $avatar
     * @param $id
     * @param $refreshToken
     */
    public function __construct($email, $displayName, $avatar, $id, $refreshToken, $remainingUsernameChanges)
    {
        $this->email        = $email;
        $this->displayName  = $displayName;
        $this->avatar       = $avatar;
        $this->id           = $id;
        $this->refreshToken = $refreshToken;
		$this->remainingUsernameChanges = $remainingUsernameChanges;

        $this->setArrayFormat(compact('email', 'displayName', 'avatar', 'id', 'refreshToken', 'remainingUsernameChanges'));
    }

    /**
     * @param $data
     *
     * @return $this
     */
    private function setArrayFormat($data)
    {
        $this->arrayed = $data;

        return $this;
    }

    /**
     * @param Plus $plus
     *
     * @return User|bool
     */
    public static function fromGoogleAuth(Plus $plus)
    {
        $person = $plus->person();

        $email = false;
        foreach ($person->getEmails() as $email) {
            if (self::emailBelongsToAccount($email)) {
                $email = $email->value;
            }
        }

        if ( ! $email) {
            // throw new Exception('Failed to determine account email');
            // return false;

            exit( 'Failed to determine account email' ); // temporary
        }

        return new self(
            $email,
            $person->getDisplayName(),
            $person->getImage()->url,
            $person->getId(),
            $plus->client()->refreshToken(),
			0
        );
    }

    /**
     * @param $email
     *
     * @return bool
     */
    private static function emailBelongsToAccount($email)
    {
        return $email->type == 'account';
    }

    /**
     * @param array $info
     *
     * @return User
     */
    public static function fromSession(array $info)
    {
        return new self(
            $info['email'],
            $info['displayName'],
            $info['avatar'],
            $info['id'],
            $info['refreshToken'],
			$info['remaining_username_changes']
        );
    }

    /**
     * @return mixed
     */
    public function email()
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function displayName()
    {
        return $this->displayName;
    }

    /**
     * @param bool $size
     *
     * @return mixed
     */
    public function avatar($size = false)
    {
        if ( ! $size) {
            $size = 50;
        }

        return str_replace('?sz=50', '?sz=' . $size, $this->avatar);
    }

    /**
     * @return mixed
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->arrayed;
    }

    /**
     * @return mixed
     */
    public function refreshToken()
    {
        return $this->refreshToken;
    }
}
