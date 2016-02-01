<?php
require_once '../autoload.php';

Auth::logout();

header("Location: " . $config['app_link']);