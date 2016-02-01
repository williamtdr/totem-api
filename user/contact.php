<?php
require_once '../autoload.php';

$user = Auth::user();
if(!$user) json_p(['success' => false]);
if(!$_GET['subject'] or !(strlen($_GET['subject']) > 0)) json_p(['success' => false]);
if(!$_GET['text'] or !(strlen($_GET['text']) > 0)) json_p(['success' => false]);

try {
	$mandrill = new Mandrill('{mandrill key}');
	$message = array(
		'text' => $_GET['text'],
		'subject' => $_GET['subject'],
		'from_email' => '{from email}',
		'from_name' => $user->displayName(),
		'to' => array(
			array(
				'email' => '{reply email}',
				'name' => '{reply name}',
				'type' => 'to'
			)
		),
		'headers' => array('Reply-To' => $user->email()),
		'important' => false,
		'tags' => array('totem-contact')
	);
	$async = false;
	$ip_pool = 'Main Pool';
	$result = $mandrill->messages->send($message, $async, $ip_pool);
	json_p(['success' => true]);
} catch(Mandrill_Error $e) {
	json_p(['success' => false]);
}
?>