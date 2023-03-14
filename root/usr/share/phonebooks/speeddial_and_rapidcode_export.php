#!/usr/bin/env php
<?php
$DEBUG = isset($_ENV['DEBUG']) ? $_ENV['DEBUG'] : false;

$nethvoicedb = new PDO(
        'mysql:host='.$_ENV['AMPDBHOST'].';port='.$_ENV['NETHVOICE_MARIADB_PORT'].';dbname='.$_ENV['AMPDBNAME'],
        $_ENV['AMPDBUSER'],
        $_ENV['AMPDBPASS']);

$phonebookdb = new PDO(
        'mysql:host='.$_ENV['PHONEBOOK_DB_HOST'].';port='.$_ENV['PHONEBOOK_DB_PORT'].';dbname='.$_ENV['PHONEBOOK_DB_NAME'],
        $_ENV['PHONEBOOK_DB_USER'],
        $_ENV['PHONEBOOK_DB_PASS']);


// Remove NethVoice extensions from centralized phonebook
$sth = $phonebookdb->prepare('DELETE FROM phonebook WHERE sid_imported = "NethVoice RapidCodes" OR sid_imported = "speeddial"');

// Import Speed dials
$sth = $nethvoicedb->prepare('SELECT number AS extension, label AS name from `phonebook` order by name ');
$sth->execute([]);

$qm = [];
while($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
        if($DEBUG) {
                print_r($row);
        }
        if (empty($query)) {
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
			VALUES ';
	}
	$query .= '("admin", "speeddial", "", "", "", ?, "", "", "", "","", ?, "", "", "", "", "", "", "", "", "", "", "", "", "", "speeddial")';
	$qm[] = $row['extension'];
	$qm[] = $row['name'];
}

if (!empty($qm)) {
        $sth = $nethvoicedb->prepare($query);
        $sth->execute($qm);
}


// Import NethVoice Rapidcode
$sth = $nethvoicedb->prepare('SELECT number AS extension, label AS name from `phonebook` order by name ');
$sth->execute([]);

$qm = [];
while($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
        if($DEBUG) {
                print_r($row);
        }
        if (empty($query)) {
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
			VALUES ';
	}
	$query .= '("admin", "rapidcode", "", "", "", ?, "", "", "", "","", ?, "", "", "", "", "", "", "", "", "", "", "", "", "", "NethVoice RapidCodes")';
	$qm[] = $row['extension'];
	$qm[] = $row['name'];
}

if (!empty($qm)) {
        $sth = $nethvoicedb->prepare($query);
        $sth->execute($qm);
}

