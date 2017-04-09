<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//API Url
$url = 'https://dev.edebitdirect.com/app/api/v1/check/';

//Initiate cURL.
$ch = curl_init($url);

//The JSON data.
$jsonData = array(
  "customer_name"   => "Previtch Carl",
  "customer_street" => "742 Evergreen Terrace",
  "customer_city"   => "Seattle",
  "customer_state"  => "WA",
  "customer_zip"    => "90045",
  "customer_phone"  => "555-555-5555",
  "customer_email"  => "carl@edebitdirect.com",
  "amount"          => 2000.00,
  "check_number"    => 2007,
  "routing_number"  => "122000247",
  "account_number"  => "1234567890"
);

$jsonDataEncoded = json_encode($jsonData);
//echo $jsonDataEncoded; exit;

$header = array(
        "Authorization: apikey 407U7C7S1:a69c6194c10a0b7f343a53c3ab966d35523f0ea5",
        "Content-Type: application/json",
        "Cache-Control: no-cache"
        );

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

//Execute the request
$result = curl_exec($ch);
echo '<pre>';
print_r($result);
echo '</pre>';

curl_close($ch);
