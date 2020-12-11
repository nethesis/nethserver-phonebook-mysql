<?php

/* change API URL here*/
$url = 'http://localhost/test.html';

$ch = curl_init();

/* Add cURL options if needed
* https://www.php.net/manual/en/function.curl-setopt.php
*/
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Content-Type: application/json",
    ));
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode != 200) {
    # cURL error
    exit(1);
}

$contacts = json_decode($response,JSON_OBJECT_AS_ARRAY);

exec('perl -e \'use NethServer::Password; my $password = NethServer::Password::store(\'PhonebookDBPasswd\')  ; printf $password;\'',$out);
$pbookpass = $out[0];

$db = new PDO("mysql:host=localhost;dbname=phonebook",'pbookuser', $pbookpass);

$stmt = $db->prepare('DELETE FROM phonebook WHERE type = "api"');
$stmt->execute();

$sql = "";
$arguments = array();
foreach ($contacts as $contact) {
    /* Add other fields and another "?" for eacch field added */
    $sql .= "INSERT INTO phonebook.phonebook (type, workphone, fax, name)
                VALUES ('api', ?, ?, ?);";

    /* Add another line like this for each field added */
    $arguments[] = $contact['telephone']; 
    $arguments[] = $contact['fax'];
    $arguments[] = $contact['name'] . ' ' . $contact['surname'];
}

$stmt = $db->prepare($sql);
$stmt->execute($arguments);

