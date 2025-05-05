<?php
$client_id = '4f5a42846cf7423fa433306a6298aacc';
$client_secret = 'c23377ab82a94291899200f7a35fe46a';

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://oauth.fatsecret.com/connect/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/x-www-form-urlencoded"
]);

curl_setopt($ch, CURLOPT_USERPWD, "$client_id:$client_secret");

curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    "grant_type" => "client_credentials",
    "scope" => "basic"
]));

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
echo "<pre>"; print_r($data); echo "</pre>";
?>
