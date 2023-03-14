#!/usr/bin/php -q
<?php

$DEBUG = isset($_ENV['DEBUG']) ? $_ENV['DEBUG'] : false;
$source_name = 'webrecall';

$sourcedb = new PDO(
        'mssql:host=SOURCE.DB.HOSTNAME;port=PORT;dbname=DBNAME',
        'USERNAME',
        'PASSWORD');

$phonebookdb = new PDO(
        'mysql:host='.$_ENV['PHONEBOOK_DB_HOST'].';port='.$_ENV['PHONEBOOK_DB_PORT'].';dbname='.$_ENV['PHONEBOOK_DB_NAME'],
        $_ENV['PHONEBOOK_DB_USER'],
	$_ENV['PHONEBOOK_DB_PASS']);

// Remove old source data from centralized phonebook
$sth = $phonebookdb->prepare('DELETE FROM phonebook WHERE sid_imported = ?');
$sth->execute([$source_name]);

$query="select QU12_RAGSOC as azienda,QU12_FAX as fax,QU12_LOCALITA as citta,QU12_CAP as cap,QU12_INDIRIZZO as via,QU12_TEL as tel,QU12_PROV as prov,QU12_EMAIL as email from QU12_RIVCLIFOR";
$sth = $sourcedb->prepare($query);
$sth->execute([]);

while($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
        if($DEBUG) {
                print_r($row);
        }
        $query = 'INSERT INTO phonebook.phonebook (
                                owner_id,
                                type,
                                homeemail,
                                workemail,
                                homephone,
                                workphone,
                                cellphone,              
                                fax,
                                title,
                                company,
                                notes,
                                name,
                                homestreet,
                                homepob,
                                homecity,               
                                homeprovince,
                                homepostalcode,
                                homecountry,
                                workstreet,
                                workpob,                
                                workcity,
                                workprovince,
                                workpostalcode,
                                workcountry,
                                url,
                                sid_imported
                        )
                        VALUES
				(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
	$sth = $phonebookdb->prepare($query);
	$sth->execute(array(
		'admin',		#owner_id
		$source_name,		#type
		$row['email'],		#homeemail
		'',			#workemail
                '',			#homephone
		preg_replace("/^00/","+",preg_replace("/[^0-9+]/","",$row['tel'])),		#workphone
		preg_replace("/^00/","+",preg_replace("/[^0-9+]/","",$row['cell'])),            #cellphone
		preg_replace("/^00/","+",preg_replace("/[^0-9+]/","",$row['fax'])),     	#fax,
                '',             	#title
                $row['azienda'],        #company
                '',             	#notes
                $row['cognome']." ".$row['nome'],		#name
                '',             	#homestreet
                '',             	#homepob
                '',             	#homecity            
                '',             	#homeprovince
                '',             	#homepostalcode
                '',             	#homecountry
                $row['via'],            #workstreet
                '',             	#workpob
                $row['citta'],          #workcity
                $row['prov'],           #workprovince
                $row['cap'],            #workpostalcode
                '',             	#workcountry
                '',             	#url
                $source_name            #sid_imported
	));
}
