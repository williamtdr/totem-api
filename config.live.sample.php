<?php
return [
	'app_link' => 'http://totem.fm/',
	'database' => [
		'host'     => '127.0.0.1',
		'user'     => 'totem',
		'password' => 'password',
		'database' => 'totem'
	],
	'google'   => [
		'client_id'     => '{google client ID}',
		'client_secret' => '{google client secret}'
	],
	'youtube'  => [
		'key'         => '{youtube key}',
		'video_parts' => [
			'snippet',
			'contentDetails',
			'statistics',
			'status'
		]
	],
];
