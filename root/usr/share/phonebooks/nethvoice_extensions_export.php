#!/usr/bin/env php
<?php

#
# Copyright (C) 2022 Nethesis S.r.l.
# SPDX-License-Identifier: GPL-3.0-or-later
#

$DEBUG = isset(getenv('DEBUG')) ? getenv('DEBUG') : false;

$nethvoicedb = new PDO(
        'mysql:host='.getenv('AMPDBHOST').';port='.getenv('NETHVOICE_MARIADB_PORT').';dbname='.getenv('AMPDBNAME'),
        getenv('AMPDBUSER'),
        getenv('AMPDBPASS'));

$phonebookdb = new PDO(
        'mysql:host='.getenv('PHONEBOOK_DB_HOST').';port='.getenv('PHONEBOOK_DB_PORT').';dbname='.getenv('PHONEBOOK_DB_NAME'),
        getenv('PHONEBOOK_DB_USER'),
        getenv('PHONEBOOK_DB_PASS'));


// Remove NethVoice extensions from centralized phonebook
$sth = $phonebookdb->prepare('DELETE FROM phonebook WHERE sid_imported = "nethvoice extensions"');
$sth->execute([]);

$sth = $nethvoicedb->prepare('SELECT default_extension as extension,displayname as name FROM userman_users WHERE default_extension != "none"');
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

	$query .= '("admin", "extension", "", "", "",? , "", "", "", "","", ?, "", "", "", "", "", "", "", "", "", "", "", "", "", "nethvoice extensions")';
	$qm[] = $row['extension'];
	$qm[] = $row['name'];
}

if (!empty($qm)) {
	$sth = $nethvoicedb->prepare($query);
	$sth->execute($qm);
}

