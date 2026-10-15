<?php

$ch = curl_init('https://api.ipify.org');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$ip = curl_exec($ch);
curl_close($ch);

echo 'IP sortante : ' . $ip;