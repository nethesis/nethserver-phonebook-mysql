#!/usr/bin/env php
<?php

#
# Copyright (C) 2022 Nethesis S.r.l.
# http://www.nethesis.it - nethserver@nethesis.it
#
# This script is part of NethServer.
#
# NethServer is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License,
# or any later version.
#
# NethServer is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with NethServer.  If not, see COPYING.
#


/***************************************************
 *
 *   Use VTE REST API to popolate phonebook
 *   HOW TO USE:
 *   - copy this script in /usr/share/phonebooks/scripts/ directory
 *   - Change $url with your API URL
 *   - Put your authorization token into $authorization_token
 *
 * *************************************************/

// URL of the API
$url = 'https://trial01.vtecrm.net/40182/restapi/v1/vtews/query';

// Authorization token used for authentication
$authorization_token = '';

//Count the number of results
$query = 'SELECT count(*) FROM Contacts;';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 4);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query'=>$query]));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json;charset=utf-8",
        "Accept: application/json;charset=utf-8",
        "Authorization: Basic ".$authorization_token,
));

$res = json_decode(curl_exec($ch),TRUE);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// DEBUG
//print_r($res);

if ($res['status'] != 200 || $httpCode != 200) {
        error_log("Error contacting phonebook download API: ".$res['status']);
        exit(1);
}

if ($res['data'] != false && !empty($res['data'][0]['count'])) {
	$limit = 1000;
	$count = $res['data'][0]['count'];
	// Connect to phonebook database using PDO
	exec('perl -e \'use NethServer::Password; my $password = NethServer::Password::store(\'PhonebookDBPasswd\')  ; printf $password;\'',$out);
	$phonebookDB = new PDO(
		'mysql:host=localhost;dbname=phonebook;charset=utf8',
		'pbookuser',
		$out[0]);

	// Delete old contacts
	$phonebookDB->exec('DELETE FROM phonebook WHERE sid_imported = "vte"');

	for ($offset = 0; $offset < $count; $offset+=$limit) {
		$query = "SELECT * FROM Contacts limit $offset,$limit;";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 4);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query'=>$query]));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		        "Content-Type: application/json;charset=utf-8",
		        "Accept: application/json;charset=utf-8",
		        "Authorization: Basic ".$authorization_token,
		));

		$res = json_decode(curl_exec($ch),TRUE);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		// DEBUG
		//print_r($res);

		if ($res['status'] != 200 || $httpCode != 200) {
		        error_log("Error contacting phonebook download API: ".$res['status']);
 	       		exit(1);
		}
		// Write result to phonebook database
		if (!empty($res['data']) && is_array($res['data'])) {
			$query_insert = 'INSERT INTO phonebook (name,company,title,workphone,cellphone,homephone,workemail,fax,workstreet,workpob,workcity,workprovince,workpostalcode,workcountry,type,sid_imported) VALUES ';
			$questionmarks = [];
			foreach ($res['data'] as $record) {
				//Extract company and name from firstname and lastname
				$pattern = '/^(.*) \(([^)]*)\)$/';
				preg_match($pattern,$record['firstname'].' '.$record['lastname'],$matches);
				if (empty($matches)) {
			                $name = $record['firstname'].' '.$record['lastname'];
			                $company = "";
			        } else {
			                $name = $matches[1];
			                $company = $matches[2];
			        }

				$questionmarks[] = '(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
				$query_data[] = $name;
				$query_data[] = $company;
				$query_data[] = $record['title'];
				$query_data[] = $record['phone'];
				$query_data[] = $record['mobile'];
				$query_data[] = $record['homephone'];
				$query_data[] = $record['email'];
				$query_data[] = $record['fax'];
				$query_data[] = $record['mailingstreet'];
				$query_data[] = $record['mailingpobox'];
				$query_data[] = $record['mailingcity'];
				$query_data[] = $record['mailingstate'];
				$query_data[] = $record['mailingzip'];
				$query_data[] = $record['mailingcountry'];
				$query_data[] = 'vte';
				$query_data[] = 'vte';
			}
			$query_insert .= implode(',',$questionmarks);
			
			$sth = $phonebookDB->prepare($query_insert);
			$sth->execute($query_data);
			// DEBUG
			//echo $query_insert."\n";
			//print_r($query_data);
		}
	}
}

/** VTE fields **
            "salutationtype": "--None--",
            "firstname": "Andrea",
            "contact_no": "CON14",
            "lastname": "Marchionni 3",
            "phone": "07211741005",
            "account_id": "",
            "mobile": "",
            "leadsource": "--None--",
            "homephone": "",
            "title": "",
            "otherphone": "",
            "department": "",
            "fax": "",
            "email": "",
            "birthday": "",
            "assistant": "",
            "contact_id": "",
            "assistantphone": "",
            "emailoptout": "0",
            "donotcall": "0",
            "reference": "0",
            "assigned_user_id": "19x1",
            "createdtime": "2022-07-21 12:40:37",
            "modifiedtime": "2022-07-21 12:40:37",
            "vendor_id": "",
            "newsletter_unsubscrpt": "1",
            "creator": "19x1",
            "portal": "0",
            "support_start_date": "21-07-2022",
            "support_end_date": "21-07-2023",
            "mailingstreet": "",
            "otherstreet": "",
            "mailingpobox": "",
            "otherpobox": "",
            "mailingcity": "",
            "othercity": "",
            "mailingstate": "",
            "otherstate": "",
            "mailingzip": "",
            "otherzip": "",
            "mailingcountry": "",
            "othercountry": "",
            "description": "",
            "gdpr_privacypolicy": "0",
            "gdpr_privacypolicy_checkedtime": "0000-00-00 00:00:00",
            "gdpr_privacypolicy_remote_addr": "",
            "gdpr_personal_data": "0",
            "gdpr_personal_data_checkedtime": "0000-00-00 00:00:00",
            "gdpr_personal_data_remote_addr": "",
            "gdpr_marketing": "0",
            "gdpr_marketing_checkedtime": "0000-00-00 00:00:00",
            "gdpr_marketing_remote_addr": "",
            "gdpr_thirdparties": "0",
            "gdpr_thirdparties_checkedtime": "0000-00-00 00:00:00",
            "gdpr_thirdparties_remote_addr": "",
            "gdpr_profiling": "0",
            "gdpr_profiling_checkedtime": "0000-00-00 00:00:00",
            "gdpr_profiling_remote_addr": "",
            "gdpr_restricted": "0",
            "gdpr_restricted_checkedtime": "0000-00-00 00:00:00",
            "gdpr_restricted_remote_addr": "",
            "gdpr_notifychange": "0",
            "gdpr_notifychange_checkedtime": "0000-00-00 00:00:00",
            "gdpr_notifychange_remote_addr": "",
            "gdpr_deleted": "0",
            "gdpr_deleted_checkedtime": "0000-00-00 00:00:00",
            "gdpr_deleted_remote_addr": "",
            "gdpr_sentdate": "0000-00-00 00:00:00",
            "id": "4x292"
*/
