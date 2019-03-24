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
    'https://bitpay.ir/signin',
    array(
        'email' => USERNAME,
        'password' => PASSWORD,
        'login' => '1',
    )
);

if (strpos($login, 'item user_name onright') === false) {
    die('Login error');
}

$settle = request(
    'https://bitpay.ir/user/settle'
);

if (strpos($settle, 'موجودی جاری') === false) {
    die('Settle error');
}

$amount = substr($settle, strpos($settle, '<div class="red">'));
$amount = substr($amount, strpos($amount, '<strong>') + 8);
$amount = substr($amount, 0, strpos($amount, ' '));

$bank_account = substr($settle, strpos($settle, '<select name="bankAccount">'));
$bank_account = substr($bank_account, strpos($bank_account, '<option value="default">') + 24);
$bank_account = substr($bank_account, strpos($bank_account, '<option value="') + 15);
$bank_account = substr($bank_account, 0, strpos($bank_account, '"'));

if (str_replace(',', '', $amount) < 50000) {
	die('Minimum amount is 50,000');
}

if (empty($bank_account)) {
	die('Bank account not found');
}

$request = request(
    'https://bitpay.ir/user/settle-request',
    array(
        'amount' => $amount,
        'bankAccount' => $bank_account,
        'settleType' => '1',
    )
);

echo $request;
