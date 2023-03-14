#!/usr/bin/env php
<?php
$DEBUG = isset($_ENV['DEBUG']) ? $_ENV['DEBUG'] : false;

$nethctidb = new PDO(
	'mysql:host='.$_ENV['NETHCTI_DB_HOST'].';port='.$_ENV['NETHCTI_DB_PORT'].';dbname=nethcti3',
	$_ENV['NETHCTI_DB_USER'],
	$_ENV['NETHCTI_DB_PASSWORD']);

$phonebookdb = new PDO(
        'mysql:host='.$_ENV['PHONEBOOK_DB_HOST'].';port='.$_ENV['PHONEBOOK_DB_PORT'].';dbname='.$_ENV['PHONEBOOK_DB_NAME'],
        $_ENV['PHONEBOOK_DB_USER'],
        $_ENV['PHONEBOOK_DB_PASS']);


// Remove NethCTI contacts from centralized phonebook
$sth = $phonebookdb->prepare('DELETE FROM phonebook WHERE sid_imported = "nethcti"');
$sth->execute([]);

$sth = $nethctidb->prepare("SELECT owner_id, homeemail, workemail, homephone, workphone, cellphone, fax, title, company, notes, name,
                                homestreet, homepob, homecity, homeprovince, homepostalcode, homecountry, workstreet, workpob, workcity,
				workprovince, workpostalcode, workcountry, url FROM cti_phonebook WHERE type='public'");
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
				(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "nethcti")';
	$sth = $phonebookdb->prepare($query);
	$sth->execute(array_values($row));
}
