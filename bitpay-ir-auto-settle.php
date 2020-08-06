<?php

define('USERNAME', 'your@email.com');
define('PASSWORD', 'yourpassword');
define('COOKIE', '.bitpay-ir-cookie');

function request($url, $post = array())
{
	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . DIRECTORY_SEPARATOR . COOKIE);
	curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . DIRECTORY_SEPARATOR . COOKIE);
	curl_setopt($ch, CURLOPT_REFERER, 'https://bitpay.ir/user/settle');

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Cache-Control: no-cache',
		'Pragma: no-cache',
		'X-Requested-With: XMLHttpRequest',
	));

	return curl_exec($ch);
}

$cookie_file = __DIR__ . DIRECTORY_SEPARATOR . COOKIE;

if (file_exists($cookie_file)) {
	unlink($cookie_file);
}

$login = request(
	'https://bitpay.ir/signin-actSignin',
	array(
		'email' => USERNAME,
		'pass' => PASSWORD,
		'captcha' => '',
	)
);

if (strpos($login, 'window.open("user/myAccount"') === false) {
	die('Login error');
}

$settle = request(
	'https://bitpay.ir/user/settle'
);

if (strpos($settle, 'remain-amount') === false) {
	die('Settle error');
}

$bank_account = substr($settle, strpos($settle, 'name="bankAccount" value="') + 26);
$bank_account = substr($bank_account, 0, strpos($bank_account, '"'));

if (empty($bank_account)) {
	die('Bank account not found');
}

$request = request(
	'https://bitpay.ir/user/settle-request',
	array(
		'typeRequest' => 1,
		'amount' => '',
		'bankAccount' => $bank_account,
		'settleType' => '1',
	)
);

echo $request;
