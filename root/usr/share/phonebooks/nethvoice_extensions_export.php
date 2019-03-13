#!/usr/bin/env php
<?php
 include_once ("/etc/freepbx.conf");

 exec('perl -e \'use NethServer::Password; my $password = NethServer::Password::store("PhonebookDBPasswd") ; printf $password;\'',$out2);
 $duser2 = 'pbookuser';
 $dpass2 = $out2[0];
 $dhost2 = 'localhost';

 $db2 = new PDO("mysql:host=$dhost2;dbname=phonebook",$duser2, $dpass2);

 $tableExists = $db->getOne('SELECT COUNT(*) FROM information_schema.TABLES WHERE (TABLE_SCHEMA = "asterisk") AND (TABLE_NAME = "userman_users")');
 if ($tableExists == 1) {
     $ext = $db->getAll('SELECT default_extension as extension,displayname as name FROM userman_users WHERE default_extension != "none"',DB_FETCHMODE_ASSOC);
 } else {
     $ext = $db->getAll('SELECT extension,name FROM users',DB_FETCHMODE_ASSOC);
 }

 if (DB::IsError($tableExists) || DB::IsError($ext)){
     echo "Error reading extensions\n";
     exit (1);
 } 

 // Remove NethVoice extensions from centralized phonebook
 $db2->query('DELETE FROM phonebook WHERE sid_import = "nethvoice extensions"');

 if (empty($ext)) exit (0);

 $query = "INSERT INTO phonebook.phonebook (owner_id,type, homeemail, workemail, homephone, workphone, cellphone,
                                                fax, title, company, notes, name, homestreet, homepob, homecity,
                                                homeprovince, homepostalcode, homecountry, workstreet, workpob,
                                                workcity, workprovince, workpostalcode, workcountry, url, sid_import) VALUES ";
 $v = array();
 foreach ($ext as $e){
     $values[] .= "('admin', 'extension', '', '', '',?, '', '', '', '','', ?,'', '', '','', '', '', '','', '', '', '','', '', 'nethvoice extensions')";
     $v[] = $e['extension'];
     $v[] = $e['name'];
 }
 
 $query .= implode(',',$values);
 $stmt2 = $db2->prepare($query);
 $stmt2->execute($v);

