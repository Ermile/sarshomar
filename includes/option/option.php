<?php

require_once('social.php');
require_once('payment.php');
require_once('sms.php');


/**
 * save logs in other database
 */
if(!defined('db_log_name'))
{
	define('db_log_name', 'sarshomar_log');
}


self::$url['protocol']       = 'https';
self::$url['root']           = 'sarshomar';
// self::$url['tld']            = 'com';


self::$config['site']['title']  = "Sarshomar";
self::$config['site']['desc']   = "Focus on your question. Do not be too concerned about how to ask or analyze.";
self::$config['site']['slogan'] = "Ask Anyone Anywhere";

self::$config['botscout']                 = 'hIenwLNiGpPOoSk';

// max upload file size
// byte
self::$config['max_upload'] = 5 * 1024 * 1024; // 5 MB;


self::$config['billing_page'] = true;

// upload file on dl.sarshomar.com
self::$config['upload_subdomain']  = 'dl';


self::$config['default_payment']  = 'zarinpal';

/**
@ In the name Of Allah
* The base configurations of the jibres.
*/
self::$language =
[
	'default' => 'en',
	'list'    => ['fa','en',],
];
/**
 * system default lanuage
 */


self::$config['redirect']                     = 'a';

// self::$config['visitor'] = true;

self::$config['favicon']['version']           = null;


/**
 * call kavenegar template
 */
self::$config['enter']['call']                = true;
self::$config['enter']['call_template_fa'] = 'ermile-fa';
self::$config['enter']['call_template_en'] = 'ermile-en';


/**
 * first signup url
 * main redirect url . signup redirect url
 */
self::$config['enter']['singup_redirect']     = 'a';


/**
 * cronjob urls and status
 */
self::$config['cronjob']['status'] = true;


/**
 * list of units
 */
self::$config['units'] =
[
	1 =>
	[
		'title' => 'toman',
		'desc'  => "Toman",
	],

	2 =>
	[
		'title' => 'dollar',
		'desc'  => "$",
	],
];
// the unit id for default
self::$config['default_unit'] = 1;
// force change unit to this unit
self::$config['force_unit']   = 1;


self::$config['enter']['verify_telegram'] = true;
self::$config['enter']['verify_sms']      = true;
self::$config['enter']['verify_call']     = true;
self::$config['enter']['verify_sendsms']  = false;


?>