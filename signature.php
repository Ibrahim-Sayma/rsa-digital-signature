<?php
// RSA Digital Signature in PHP
// Ibrahim Sayma - 120221807

function title($text) {
    echo PHP_EOL . str_repeat("=", 55) . PHP_EOL;
    echo $text . PHP_EOL;
    echo str_repeat("=", 55) . PHP_EOL;
}

function verify($message, $signature, $publicKey) {
    $result = openssl_verify($message, $signature, $publicKey, OPENSSL_ALGO_SHA256);
    if ($result === 1) return "VALID";
    if ($result === 0) return "INVALID";
    return "ERROR: " . openssl_error_string();
}

$config = [
    "config" => "C:/xampp/php/extras/ssl/openssl.cnf",
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
];

// STEP 1: Generate keys
title("STEP 1: Generate RSA key pair (2048 bits)");
$keyPair = openssl_pkey_new($config);
if ($keyPair === false) {
    die("Error: " . openssl_error_string() . PHP_EOL);
}
openssl_pkey_export($keyPair, $privateKey, null, $config);
$publicKey = openssl_pkey_get_details($keyPair)["key"];
file_put_contents("private_key.pem", $privateKey);
file_put_contents("public_key.pem", $publicKey);
echo "Keys saved to private_key.pem and public_key.pem" . PHP_EOL;
echo $publicKey;

// STEP 2: Sign
title("STEP 2: Sign the message with the PRIVATE key");
$message = 'Transfer $1000 to Mark';
openssl_sign($message, $signature, $privateKey, OPENSSL_ALGO_SHA256);
echo "Message   : " . $message . PHP_EOL;
echo "SHA-256   : " . hash("sha256", $message) . PHP_EOL;
echo "Signature : " . substr(base64_encode($signature), 0, 60) . "..." . PHP_EOL;
echo "Length    : " . strlen($signature) . " bytes" . PHP_EOL;

// STEP 3: Verify original message
title("STEP 3: Verify the original message with the PUBLIC key");
echo "Message   : " . $message . PHP_EOL;
echo "Result    : " . verify($message, $signature, $publicKey) . PHP_EOL;

// STEP 4: Verify modified message
title("STEP 4: Verify a MODIFIED message");
$fakeMessage = 'Transfer $1000 to Oscar';
echo "Message   : " . $fakeMessage . PHP_EOL;
echo "Result    : " . verify($fakeMessage, $signature, $publicKey) . PHP_EOL;

// STEP 5: Verify with another public key
title("STEP 5: Verify with a DIFFERENT public key");
$otherPair = openssl_pkey_new($config);
$otherPublicKey = openssl_pkey_get_details($otherPair)["key"];
echo "Message   : " . $message . PHP_EOL;
echo "Result    : " . verify($message, $signature, $otherPublicKey) . PHP_EOL;