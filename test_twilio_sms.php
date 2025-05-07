<?php
$url = "https://api.twilio.com/2010-04-01/Accounts.json";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);

if ($response === false) {
    echo "Erreur CURL : " . curl_error($ch);
} else {
    echo "Connexion réussie à Twilio : $response";
}

curl_close($ch);
?>
