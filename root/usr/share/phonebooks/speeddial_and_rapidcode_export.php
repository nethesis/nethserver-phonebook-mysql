#!/usr/bin/env php
<?php
define("DEBUG",false);

include_once ("/etc/freepbx.conf");

$name = '';
$number = '';

// Asterisk database connection
$duser = $amp_conf["AMPDBUSER"];
$dpass = $amp_conf["AMPDBPASS"];
$dhost = $amp_conf["AMPDBHOST"];
$db1 = new PDO("mysql:host=$dhost;dbname=asterisk",$duser, $dpass);

// Phonebook database connection
exec('perl -e \'use NethServer::Password; my $password = NethServer::Password::store("PhonebookDBPasswd") ; printf $password;\'',$out2);
$duser2 = 'pbookuser';
$dpass2 = $out2[0];
$dhost2 = 'localhost';
$db2 = new PDO("mysql:host=$dhost2;dbname=phonebook",$duser2, $dpass2);

// Export Speed Dials
try {
    if(DEBUG) {
        echo "Exporting Speed Dials\n";
    }

    $stmt = $db1->prepare('SELECT * from `phonebook` order by name');
    $stmt->execute();

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if($row["number"] != '' && $row["name"] != '') {

            if(DEBUG) {
                echo "Name: {$row["name"]}\n Numero: {$row["number"]}\n";
            }

            $query = "INSERT INTO phonebook.phonebook (owner_id,type, homeemail, workemail, homephone, workphone, cellphone,
                                                fax, title, company, notes, name, homestreet, homepob, homecity,
                                                homeprovince, homepostalcode, homecountry, workstreet, workpob,
                                                workcity, workprovince, workpostalcode, workcountry, url)
                      VALUES ('admin', 'speeddial', '', '', '',?, '', '', '', '','', ?, '', '', '','', '', '', '','', '', '', '','', '')";
            $stmt2 = $db2->prepare($query);
            $stmt2->execute(array($row["number"],$row["name"]));
        }
    }

    if(DEBUG) {
        echo "Speed Dials Exported\n";
    }
} catch (Exception $e) {
    if(DEBUG) {
        echo 'Error exporting Speed Dials: ' . $e->getMessage();
    }
}

// Export Rapid Codes
try {
    if(DEBUG) {
        echo "Exporting Rapid Codes\n";
    }
    $stmt = $db1->prepare('SELECT * from `rapidcode` order by label');
    $stmt->execute();

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if($row["number"] != '' && $row["label"] != '') {

            if(DEBUG) {
                echo "Name: {$row["label"]}\n Numero: {$row["number"]}\n";
            }
            $query = "INSERT INTO phonebook.phonebook (owner_id,type, homeemail, workemail, homephone, workphone, cellphone,
                                                fax, title, company, notes, name, homestreet, homepob, homecity,
                                                homeprovince, homepostalcode, homecountry, workstreet, workpob,
                                                workcity, workprovince, workpostalcode, workcountry, url)
                      VALUES ('admin', 'speeddial', '', '', '',?, '', '', '', '','', ?, '', '', '','', '', '', '','', '', '', '','', '')";
            $stmt2 = $db2->prepare($query);
            $stmt2->execute(array($row["number"],$row["label"]));
        }
    }
    if(DEBUG) {
        echo "Rapid Codes Exported\n";
    }
} catch (Exception $e) {
    if(DEBUG) {
        echo 'Error exporting Rapid Codes: ' . $e->getMessage();
    }
}

