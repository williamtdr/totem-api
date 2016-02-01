<?php

class DatabaseManager
{
    private static $config = [];

    /** @var mysqli */
    private $database;

	const PERMISSION_LEVEL_ROOM_OWNER = 0;
	const PERMISSION_LEVEL_ROOM_ADMIN = 1;
	const PERMISSION_LEVEL_ROOM_HOST = 2;
	const PERMISSION_LEVEL_ROOM_MUTED = 3;
	const PERMISSION_LEVEL_ROOM_QUEUE_BANNED = 4;
	const PERMISSION_LEVEL_ROOM_BANNED = 5;

	const PERMISSION_LEVEL_SITE_ADMIN = 6;
	const PERMISSION_LEVEL_SITE_MUTED = 7;
	const PERMISSION_LEVEL_SITE_QUEUE_BANNED = 8;
	const PERMISSION_LEVEL_SITE_BANNED = 9;

    public function __construct() {
        $this->database = new mysqli(self::$config['host'], self::$config['user'], self::$config['password'], self::$config['database']);
    }

    /**
     * @param array $config
     */
    public static function setCredentials(array $config)
    {
        self::$config = $config;
    }

	public function validateScope($scope) {
		if(!ctype_alnum(str_replace("-", "", $scope)))
			return false;
		return true;
	}

    /**
     * @param $username
     *
     * @return bool
     */
    public function checkUser($username)
    {
        if (strlen($username) < 3 or strlen($username) > 20 or ! ctype_alnum(str_replace('_', '', $username))) {
            return false;
        }
        return true;
    }

	public function suggest($q) {
		if(strlen($q) < 0 or strlen($q) > 30) return ["success" => false];
		if(!ctype_alnum(str_replace(array(' ','_'), array(), $q))) return ["success" => false];
		$dbq = $this->database->query("SELECT `display_name` FROM `users` WHERE `display_name` LIKE '".$this->database->real_escape_string($q)."%' LIMIT 10");
		$result = [];
		while($row = $dbq->fetch_assoc()) {
			$result[] = $row['display_name'];
		}
		return $result;
	}

	public function getUsersMatchingPermissionLevel($scope, $level) {
		if(!$this->validateScope($scope)) return ['success' => false, 'reason' => "Invalid room name."];
		$q = $this->database->query("SELECT `id` FROM `permissions` WHERE `scope`='".$this->database->real_escape_string($scope)."' AND `level`='" . $level . "'");
		$users = array();
		while($row = $q->fetch_assoc()) {
			$users[] = $row['id'];
		}
		$result = array();
		foreach($users as $u) {
			$result[] = $this->getUser($u)->displayName();
		}
		return $result;
	}

	public function roomUploadImage($scope, $ext, $type) {
		$id = $this->generateRandomString(10);
		if($type === "background") {
			$dir = "room_bg";
			$dest_table = "backgrounds";
		} else {
			$dir = "room_icon";
			$dest_table = "custom_icons";
		}
		$url = $this->database->real_escape_string("http://static.totem.fm/".$dir."/".$scope."/".$id.".".$ext);
		if($type != "background") {
			$q = $this->database->query("SELECT `url` FROM `custom_icons` WHERE `scope`='".strtolower($this->database->real_escape_string($scope))."'");
			while($row = $q->fetch_assoc()) {
				file_get_contents("http://api.totem.fm/room/image.php?type=icon&scope=".$scope."&action=remove&server_override=true&url=".$row['url']);
			}
			$this->database->query("UPDATE `rooms` SET `icon`='".$url."' WHERE `id`='".strtolower($this->database->real_escape_string($scope))."'");
		}
		$this->database->query("INSERT INTO `".$dest_table."` (`scope`,`url`) VALUES ('".$scope."', '".$url."')");
		return $id.".".$ext;
	}

	public function userUploadImage($user_id, $ext) {
		$id = $this->generateRandomString(10);
		$url = $this->database->real_escape_string("http://static.totem.fm/user_profile/".$id.".".$ext);
		$this->database->query("INSERT INTO `custom_profile_pictures` (`scope`,`url`) VALUES ('".$user_id."', '".$url."')");
		return $id.".".$ext;
	}

	public function removeImage($scope, $url, $type) {
		if($type === "background") {
			$this->database->query("DELETE FROM `backgrounds` WHERE `url`='".$this->database->real_escape_string($url)."' AND `scope`='".$scope."'");
		} else {
			$this->database->query("DELETE FROM `custom_icons` WHERE `url`='".$this->database->real_escape_string($url)."' AND `scope`='".$scope."'");
		}
		return ['success' => true];
	}

	public function getRoomBackgrounds($scope) {
		if(!$this->validateScope($scope)) return ['success' => false, 'reason' => "Invalid room name."];
		$backgrounds = array();
		$q = $this->database->query("SELECT `url` FROM `backgrounds` WHERE `scope`='".$scope."'");
		while($row = $q->fetch_assoc()) {
			$backgrounds[] = $row['url'];
		}
		return $backgrounds;
	}

	public function setRoomPassword($scope, $password) {
		if($this->database->query("UPDATE `rooms` SET `password`='".$this->database->real_escape_string($password)."' WHERE `id`='".strtolower($scope)."'")) return ['success' => true];
		return ['success' => false, 'reason' => "You must be the room owner to set the room password."];
	}

	public function removeRoomPassword($scope) {
		$room = $this->getRoom($scope);
		if(!$room->password) return ['success' => false, 'reason' => "This room doesn't have a password set."];
		$this->database->query("UPDATE `rooms` SET `password`='false' WHERE `id`='".strtolower($this->database->real_escape_string($scope))."'");
		return ['success' => true];
	}

	public function checkUpload($authkey, $scope, $type = "background") {
		if(!ctype_alnum($authkey)) return ['success' => false, 'reason' => 'Invalid authkey.'];
		if(!$this->validateScope($scope)) return ['success' => false, 'reason' => "Invalid room name."];
		if($q = $this->database->query("SELECT `id` FROM `chatkeys` WHERE `auth`='".$authkey."'")) {
			$row = $q->fetch_assoc();
			$uid = $row['id'];
			if($scope == "profile") {
				if($q->num_rows > 0) {
					return ['success' => true];
				} else {
					return ['success' => false];
				}
			}
			if($permission_q = $this->database->query("SELECT `scope`,`level` FROM `permissions` WHERE `id`='".$uid."'")) {
				while($permission_row = $permission_q->fetch_assoc()) {
					if($permission_row['scope'] == $scope) {
						if($permission_row['level'] == '1' or $permission_row['level'] == '0') {
							if($type === "background" && count($this->getRoomBackgrounds($scope)) >= 30) return ['success' => false];
							return ['success' => true];
						}
					}
				}
			}
		}
		return ['success' => false, 'reason' => "Failure."];
	}

	public function authkeytoid($authkey) {
		if(!ctype_alnum($authkey)) return ['success' => false, 'reason' => 'Invalid authkey.'];
		if($q = $this->database->query("SELECT `id` FROM `chatkeys` WHERE `auth`='".$authkey."'")) {
			$row = $q->fetch_assoc();
			$uid = $row['id'];
			return $uid;
		}
	}

	public function manageList($action, $user, $scope, $content) {
		if(!$this->isOwnerOrAdmin($user, $scope)) return ['success' => false, 'reason' => "You don't have permission to do that in this room."];
		if($this->database->query("UPDATE `rooms` SET `" . $action . "`='".$this->database->real_escape_string(str_replace("\n", ",", $content))."' WHERE `id`='".strtolower($this->database->real_escape_string($scope))."'")) return ['success' => true];
		return ['success' => false];
	}

	public function changePermission($type, $username, $scope, $level) {
		$target = $this->getUserByDisplayName($username);
		if(!$target) return ['success' => false, 'reason' => "That user doesn't exist."];

		$q = $this->database->query("SELECT * FROM `permissions` WHERE `id`='".$target->id()."' AND `scope`='".$scope."'");
		$permissions = array();
		while($row = $q->fetch_assoc()) {
			$permissions[] = $row['level'];
		}

		switch($type) {
			case "add":
				if(in_array($level, $permissions)) return ['success' => false, 'reason' => "Those settings have already been applied to said user in this room."];

				if($this->database->query("INSERT INTO `permissions` (`id`,`scope`,`level`) VALUES ('".$target->id()."', '".$this->database->real_escape_string($scope)."',".$level.")")) return ['success' => true];
			break;
			case "remove":
				if(!in_array($level, $permissions)) return ['success' => false, 'reason' => "Those settings don't exist on said user in this room."];

				if($this->database->query("DELETE FROM `permissions` WHERE `id` = '".$target->id()."' AND `scope`='".$this->database->real_escape_string($scope)."' AND `level`='".$level."'")) return ['success' => true];
			break;
		}
		return ['success' => false, 'reason' => 'Unknown error.'];
	}

    /**
     * @return string
     */
    public function makeHash()
    {
        return $this->generateRandomString();
    }

    /**
     * @param int $length
     *
     * @return string
     */
    public function generateRandomString($length = 10)
    {
        $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i ++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @param bool|false $full
     *
     * @return array
     */
    public function getRooms($full = false)
    {
        $rooms = [];
        $q     = $this->database->query("SELECT `id`,`display_name`,`user_counter`,`created_at`,`last_used`,`password`,`song_name`,`song_artist`,`song_started_at`,`song_url_fragment`,`song_picture_url`,`song_source` FROM `rooms` ORDER BY `user_counter` DESC, `song_started_at` DESC");
        while ($row = $q->fetch_assoc()) {
			if($this->roomHasNoPassword($row['password'])) {
				$room = [
					'id'           => $row['id'],
					'display_name' => $row['display_name'],
					'user_counter' => intval($row['user_counter']),
					'created_at'   => $row['created_at'],
					'last_used'    => $row['last_used'],
					'password'	   => false,
					'song'         => 0
				];
				if(!is_null($row['song_url_fragment'])) {
					$room['song'] = [
						'name'         => $row['song_name'],
						'artist'       => $row['song_artist'],
						'started_at'   => $row['song_started_at'],
						'url_fragment' => $row['song_url_fragment'],
						'picture_url'  => $row['song_picture_url'],
						'source'       => $row['song_source'],
					];
				}
			} else {
				$room = [
					'id'           => $row['id'],
					'display_name' => $row['display_name'],
					'user_counter' => intval($row['user_counter']),
					'created_at'   => $row['created_at'],
					'last_used'    => $row['last_used'],
					'password'	   => true,
					'song'         => 0
				];
			}

            $rooms[] = $room;
        }

        return $rooms;
    }

    /**
     * @param $password
     *
     * @return bool
     */
    private function roomHasNoPassword($password)
    {
        $password = strtolower($password);

        return ! trim($password) || $password == 'false';
    }

    /**
     * @param $id
     *
     * @return bool|User
     */
    public function userExists($id)
    {
        return $this->getUser($id);
    }

    /**
     * @param $id
     *
     * @return bool|User
     */
    public function getUser($id)
    {
        $q = $this->database->query("SELECT * FROM `users` WHERE `id` = '" . $this->database->real_escape_string($id) . "'");
        while ($row = $q->fetch_assoc()) {
            $user = new User(
                $row['email'],
                $row['display_name'],
                $row['avatar_link'],
                $row['id'],
                $row['refresh_token'],
				$row['remaining_username_changes']
            );

            return $user;
        }

        return false;
    }

    /**
     * @param User $user
     *
     * @return bool|User
     */
    public function makeUser(User $user)
    {
        $id           = $user->id();
        $email        = $user->email();
        $avatar_link  = $user->avatar();
        $display_name = $user->displayName();
        $refreshToken = $user->refreshToken();

        $this->database->query("INSERT INTO `users` (`id`, `email`, `avatar_link`, `display_name`, `refresh_token`) VALUES ($id,'$email','$avatar_link','$display_name', '$refreshToken')");

        return $this->getUser($id);
    }

    /**
     * @param User $user
     *
     * @return bool|User
     */
    public function updateRefreshToken(User $user)
    {
        $q[] = 'UPDATE `users`';
        $q[] = 'SET `refresh_token`=\'' . $user->refreshToken() . '\'';
        $q[] = 'WHERE `id`=\'' . $user->id() . '\'';

        $this->database->query(implode(' ', $q));

        return $this->getUser($user->id());
    }

	public function getRemainingUsernameChanges($user) {
		if($q = $this->database->query("SELECT `remaining_username_changes` FROM `users` WHERE `id`='".$user->id()."'")) {
			while($row = $q->fetch_assoc()) {
				return $row['remaining_username_changes'];
			}
		}

		return 0;
	}

    /**
     * @param User $user
     * @param $username
     *
     * @return bool|string
     */
    public function changeUsername(User $user, $username)
    {
        if (strtolower($username) === strtolower($user->displayName())) {
            $this->database->query("UPDATE `users` SET `remaining_username_changes`=`remaining_username_changes` - 1 WHERE `id`='" . $user->id() . "'");
            return true;
        }
        if (strlen($username) < 3 or strlen($username) > 30) {
            return "length";
        }

        if (stristr($username, "<") or stristr($username, ">") or stristr($username, ";")) {
            return "invalid";
        }

        if ($this->getUserByDisplayName($username)) {
            return "exists";
        }

        $this->database->query("UPDATE `users` SET `remaining_username_changes`=`remaining_username_changes` - 1,`display_name`='" . $this->database->real_escape_string($username) . "' WHERE `id`='" . $user->id() . "'");
        return true;
    }

    /**
     * @param $name
     *
     * @return bool|User
     */
    public function getUserByDisplayName($name)
    {
        $q = $this->database->query("SELECT * FROM `users` WHERE `display_name` LIKE '" . $this->database->real_escape_string($name) . "'");
        while ($row = $q->fetch_assoc()) {
            $user = new User(
                $row['email'],
                $row['display_name'],
                $row['avatar_link'],
                $row['id'],
				$row['refresh_token'],
				$row['remaining_username_changes']
            );

            return $user;
        }

        return false;
    }

    /**
     * @param $id
     *
     * @return array
     */
    public function getRoomsOwnedBy($id)
    {
        $rooms = array();
        $initial_q     = $this->database->query("SELECT `scope` FROM `permissions` WHERE `id`='" . $id . "' AND `level`=".DatabaseManager::PERMISSION_LEVEL_ROOM_OWNER);
	    if($initial_q->num_rows == 0) return $rooms;
        while ($row = $initial_q->fetch_assoc()) {
	        $q = $this->database->query("SELECT * FROM `rooms` WHERE `id`='".strtolower($row['scope'])."'");
	        while($data = $q->fetch_assoc()) {
		        $r               = new Room();
		        $r->display_name = $data["display_name"];
		        $r->user_counter = intval($data["user_counter"]);
		        $r->created_at   = $data["created_at"];
		        $r->last_used    = $data["last_used"];
		        $r->id           = $data["id"];
		        $r->password     = $data["password"];
		        if ($data["song_url_fragment"] === null) {
			        $r->song = false;
		        } else {
			        $r->song               = new Song();
			        $r->song->name         = $data["song_name"];
			        $r->song->artist       = $data["song_artist"];
			        $r->song->started_at   = $data["song_started_at"];
			        $r->song->url_fragment = $data["song_url_fragment"];
			        $r->song->picture_url  = $data["song_picture_url"];
			        $r->song->source       = $data["song_source"];
		        }

		        $rooms[] = $r;
	        }
        }

        return $rooms;
    }

	public function checkPermissionLevel($user, $scope, $query) {
		if(!$user) return false;
		$q = $this->database->query($query);
		while ($row = $q->fetch_assoc()) {
			if($row['scope'] === $scope) return true;
		}

		return false;
	}

	public function isOwner($user, $scope) {
		return $this->checkPermissionLevel($user, $scope, "SELECT `scope` FROM `permissions` WHERE `id`='" . $user->id() . "' AND `level`=".DatabaseManager::PERMISSION_LEVEL_ROOM_OWNER);
	}

	public function isHostOrAbove($user, $scope) {
		return $this->checkPermissionLevel($user, $scope, "SELECT `scope` FROM `permissions` WHERE `id`='" . $user->id() . "' AND (`level`=".DatabaseManager::PERMISSION_LEVEL_ROOM_OWNER." OR `level`=".DatabaseManager::PERMISSION_LEVEL_ROOM_ADMIN." OR `level`=".DatabaseManager::PERMISSION_LEVEL_ROOM_HOST.")");
	}

	public function isOwnerOrAdmin($user, $scope) {
		return $this->checkPermissionLevel($user, $scope, "SELECT `scope` FROM `permissions` WHERE `id`='" . $user->id() . "' AND (`level`=".DatabaseManager::PERMISSION_LEVEL_ROOM_OWNER." OR `level`=".DatabaseManager::PERMISSION_LEVEL_ROOM_ADMIN.")");
	}

	public function getDefaultSettings() {
		return [
			"notif_song_change" => true,
			"notif_chat" => "mention",
			"hide_hints" => false,
			"video_quality" => "720p"
		];
	}

	public function setUserSettings($user, $data) {
		foreach(["notif_song_change", "notif_chat", "hide_hints", "video_quality"] as $val) {
			$value = ${$val};
			if(!($value === "true" || $value === "false" || $value == false || $value == true || $value === "mention" || $val === "480p" || $val === "720p" || $val === "1080p")) return ['success' => false, 'reason' => "Invalid value."];
		}
		$notif_song_change = $data['notif_song_change'];
		$notif_chat = $data['notif_chat'];
		$hide_hints = $data['hide_hints'];
		$video_quality = $data['video_quality'];
		$q = $this->database->query("SELECT * FROM `custom_settings` WHERE `id`='".$user->id()."'");
		if($q->num_rows > 0) {
			$this->database->query("UPDATE `custom_settings` SET `notif_song_change`='".($notif_song_change === "true" ? 1 : 0)."',`notif_chat`='".$notif_chat."',`hide_hints`='".($hide_hints === "true" ? 1 : 0)."',`video_quality`='".$video_quality."' WHERE `id`='".$user->id()."'");
		} else {
			$this->database->query("INSERT INTO `custom_settings` (`id`,`notif_song_change`,`notif_chat`,`hide_hints`,`video_quality`) VALUES(".$user->id().",'".($notif_song_change === "true" ? 1 : 0)."','".$notif_chat."','".($hide_hints === "true" ? 1 : 0)."','".$video_quality."')");
		}
		return ["success" => true];
	}

	public function getUserSettings($user) {
		$q = $this->database->query("SELECT * FROM `custom_settings` WHERE `id`='".$user->id()."'");
		while($row = $q->fetch_assoc()) {
			return [
				"notif_song_change" => $row['notif_song_change'],
				"notif_chat" => $row['notif_chat'],
				"hide_hints" => $row['hide_hints'],
				"video_quality" => $row['video_quality']
			];
		}
		return $this->getDefaultSettings();
	}

	public function deleteRoom($scope) {
		if(!$this->getRoom($scope)) return ["success" => false, 'reason' => "Room does not exist."];
		if($this->database->query("DELETE FROM `permissions` WHERE `scope`='".strtolower($this->database->real_escape_string($scope))."'") &&
			$this->database->query("DELETE FROM `rooms` WHERE `id`='".strtolower($this->database->real_escape_string($scope))."'") &&
			$this->database->query("DELETE FROM `backgrounds` WHERE `scope`='".strtolower($this->database->real_escape_string($scope))."'")) return ["success" => true];
		return ["success" => false, 'reason' => "Internal server error."];
	}

	public function setOwner($id, $scope) {
		if($this->database->query("DELETE FROM `permissions` WHERE `scope`='" . $scope . "' AND `level`=".DatabaseManager::PERMISSION_LEVEL_ROOM_OWNER) && $this->database->query("INSERT INTO `permissions` (`scope`,`level`,`id`) VALUES ('".$scope."', ".DatabaseManager::PERMISSION_LEVEL_ROOM_OWNER.", ".$id.")")) return ["success" => true];
		return ["success" => true];
	}

    /**
     * @param User $user
     *
     * @return string
     */
    public function genAuthKey(User $user)
    {
        $this->database->query("REPLACE INTO `chatkeys` (`auth`,`display_name`, `id`) VALUES('" . ( $key = $this->generateRandomString(20) ) . "','" . $this->database->real_escape_string($user->displayName()) . "', '" . $user->id() . "')");
        return $key;
    }

    /**
     * @param $id
     *
     * @return bool|Room
     */
    public function getRoom($id)
    {
        $q = $this->database->query("SELECT * FROM `rooms` WHERE `id` = '" . $id . "' ORDER BY `user_counter`");
        while ($row = $q->fetch_assoc()) {
            $r               = new Room();
            $r->id           = $id;
            $r->user_counter = $row['user_counter'];
            $r->created_at   = $row['created_at'];
            $r->last_used    = $row['last_used'];
            $r->display_name = $row['display_name'];

            $r->blacklist = str_replace(",", "\n", $row['blacklist']);
            $r->whitelist = str_replace(",", "\n",  $row['whitelist']);

            $r->password           = $row['password'];
            $r->song               = new Song();
            $r->song->artist       = $row['song_artist'];
            $r->song->name         = $row['song_name'];
            $r->song->started_at   = $row['song_started_at'];
            $r->song->url_fragment = $row['song_url_fragment'];
            $r->song->picture_url  = $row["song_picture_url"];
            $r->song->source       = $row['song_source'];

            return $r;
        }

        return false;
    }

    /**
     * @param $id
     * @param $display_name
     * @param $description
     * @param $owner
     * @param $password
     */
    public function makeRoom($id, $display_name, $description, $owner, $password) {
        if(!trim($password)) $password = 'false';

        $q = "INSERT INTO `totem`.`rooms` (`id`, `display_name`, `description`, `password`)";
        $q .= " VALUES (";
        $q .= "'" . strtolower($this->database->real_escape_string($id)) . "',";
        $q .= "'" . $this->database->real_escape_string($display_name) . "',";
        $q .= "'" . $this->database->real_escape_string($description) . "',";
        $q .= "'" . $password . "'";
        $q .= ");";

        $this->database->query($q);

	    $this->database->query("INSERT INTO `totem`.`permissions` (`id`, `scope`, `level`) VALUES ('" . $owner . "', '" . strtolower($this->database->real_escape_string($id)) . "', 0)");
    }
}
