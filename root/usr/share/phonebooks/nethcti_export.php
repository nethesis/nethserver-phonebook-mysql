#!/usr/bin/env php
<?php

 exec('perl -e \'use NethServer::Password; my $password = NethServer::Password::store("CTIDBPasswd") ; printf $password;\'',$out);
 $duser = 'nethcti';
 $dpass = $out[0];
 $dhost = 'localhost';

 $link = @mysql_connect($dhost, $duser, $dpass) or die ("Can't connect to nethcti DB\n"); 
 if (!$link) {
     exit(1);
 }

 # Select nethcti3 DB if present, nethcti 2 if not
 $db_list = mysql_list_dbs($link);
 while ($row = mysql_fetch_object($db_list)) {
     $dbs[] = $row->Database;
 }
 if (in_array('nethcti3',$dbs)) {
     mysql_select_db('nethcti3', $link );
 } elseif (in_array('nethcti2',$dbs)) {
     mysql_select_db('nethcti2', $link );
 } else {
     exit(1);
 }

 exec('perl -e \'use NethServer::Password; my $password = NethServer::Password::store("PhonebookDBPasswd") ; printf $password;\'',$out2);
 $duser2 = 'pbookuser';
 $dpass2 = $out2[0];
 $dhost2 = 'localhost';

 $local_db = mysql_connect($dhost2, $duser2, $dpass2);
 if ($local_db) mysql_select_db('phonebook', $local_db );
 else exit(1);

 define("DEBUG",false);

 mysql_set_charset('utf8',$local_db);
 mysql_set_charset('utf8',$link);

 $result = mysql_query("SELECT owner_id, homeemail, workemail, homephone, workphone, cellphone, fax, title, company, notes, name, 
				homestreet, homepob, homecity, homeprovince, homepostalcode, homecountry, workstreet, workpob, workcity, 
				workprovince, workpostalcode, workcountry, url FROM cti_phonebook WHERE type='public'", $link);

// Remove NethCTI contacts from centralized phonebook
mysql_query('DELETE FROM phonebook WHERE sid_imported = "nethcti"',$local_db);

if ($result)
 while($row = mysql_fetch_array($result))
 {
     if(DEBUG)
      print_r($row);

     @$query = "INSERT INTO phonebook.phonebook (owner_id, type, homeemail, workemail, homephone, workphone, cellphone, 
                                                fax, title, company, notes, name, homestreet, homepob, homecity, 
                                                homeprovince, homepostalcode, homecountry, workstreet, workpob, 
                                                workcity, workprovince, workpostalcode, workcountry, url, sid_imported)
		VALUES ('".$row["owner_id"]."', 'nethcti', '".mysql_real_escape_string($row["homeemail"])."', '".mysql_real_escape_string($row["workemail"])."', '".mysql_real_escape_string($row["homephone"])."', 
			'".mysql_real_escape_string($row["workphone"])."', '".mysql_real_escape_string($row["cellphone"])."', '".mysql_real_escape_string($row["fax"])."', '".mysql_real_escape_string($row["title"])."', '".mysql_real_escape_string($row["company"])."', 
			'".mysql_real_escape_string($row["notes"])."', '".mysql_real_escape_string($row["name"])."', '".mysql_real_escape_string($row["homestreet"])."', '".mysql_real_escape_string($row["homepob"])."', '".mysql_real_escape_string($row["homecity"])."', 
			'".mysql_real_escape_string($row["homeprovince"])."', '".mysql_real_escape_string($row["homepostalcode"])."', '".mysql_real_escape_string($row["homecountry"])."', '".mysql_real_escape_string($row["workstreet"])."', 
			'".mysql_real_escape_string($row["workpob"])."', '".mysql_real_escape_string($row["workcity"])."', '".mysql_real_escape_string($row["workprovince"])."', '".mysql_real_escape_string($row["workpostalcode"])."', 
			'".mysql_real_escape_string($row["workcountry"])."', '".mysql_real_escape_string($row["url"])."', 'nethcti')";
     if(DEBUG)
       echo $query;
     if(!mysql_query($query,$local_db) && DEBUG) //print errors if debug is enabled
         echo mysql_error()."\n";
 }
