<?php
// save this as generate_hash.php
$password = 'mailuser-password';
$salt = base64_encode(random_bytes(12));
$hash = crypt($password, '$6$' . $salt);
echo $hash . "\n";
?>