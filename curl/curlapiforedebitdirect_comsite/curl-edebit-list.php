<?php
ini_set('display_errors', 1);
ini_set('display_errors','on');
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//API Url
$url = 'https://edebitdirect.com/app/api/v1/check/';

//Initiate cURL.
$ch = curl_init($url);

$header = array(
        "Authorization: apikey 1209CMU887:38cb55a41fc58df6deb4b2809031f54ab53bec58",
        "Content-Type: application/json",
        "Cache-Control: no-cache"
        );
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

//Execute the request
$result = curl_exec($ch);
$data = json_decode($result, true);
echo '<pre>';
print_r($data);
echo '</pre>';

 foreach ($data['objects'] as $draft){
   echo '<pre>';
   print_r($draft);
   echo '</pre>';
 }

curl_close($ch);
