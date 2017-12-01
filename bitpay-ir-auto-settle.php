<?php

define('USERNAME', 'your@email.com');
define('PASSWORD', 'yourpassword');
define('COOKIE', '.bitpay-ir-cookie');

function request($url, $post = array()) {
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

$login = request(
	'https://bitpay.ir/signin',
	array(
		'email' => USERNAME,
		'password' => PASSWORD,
		'login' => '1'
	)
);

$settle = request(
	'https://bitpay.ir/user/settle'
);

$amount = substr($settle, strpos($settle, '<div class="red">'));
$amount = substr($amount, strpos($amount, '<strong>') + 8);
$amount = substr($amount, 0, strpos($amount, ' '));

$bank_account = substr($settle, strpos($settle, '<select name="bankAccount">'));
$bank_account = substr($bank_account, strpos($bank_account, '<option value="default">') + 24);
$bank_account = substr($bank_account, strpos($bank_account, '<option value="') + 15);
$bank_account = substr($bank_account, 0, strpos($bank_account, '"'));

$request = request(
	'https://bitpay.ir/user/settle-request',
	array(
		'amount' => $amount,
		'bankAccount' => $bank_account,
		'settleType' => '1'
	)
);

print $request;

?>