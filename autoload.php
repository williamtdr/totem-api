<?php
session_start();

require_once 'vendor/autoload.php';

$config = include( 'config.live.php' );
if (file_exists('../config.dev.php')) {
    $config = include( 'config.dev.php' );
}

DatabaseManager::setCredentials($config['database']);

Client::setCredentials($config['google']);

YoutubeSongInfo::setConfig($config['youtube']);