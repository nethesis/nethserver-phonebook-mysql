#!/usr/bin/scl enable rh-php56 -- php
<?php
 include_once ("/etc/freepbx.conf");

 define("DEBUG",false);
 $name = '';
 $number = '';

 $duser = $amp_conf["AMPDBUSER"];
 $dpass = $amp_conf["AMPDBPASS"];
 $dhost = $amp_conf["AMPDBHOST"];

 $link = mysql_connect($dhost, $duser, $dpass);
 mysql_select_db('asterisk', $link );

 exec('perl -e \'use NethServer::Password; my $password = NethServer::Password::store("PhonebookDBPasswd") ; printf $password;\'',$out2);
 $duser2 = 'pbookuser';
 $dpass2 = $out2[0];
 $dhost2 = 'localhost';

 $local_db = mysql_connect($dhost2, $duser2, $dpass2);
 mysql_select_db('phonebook', $local_db );

 $result = mysql_query("SELECT * from `phonebook` order by name", $link);

 if(DEBUG)
  echo "Exporting Speed Dial";

 if ($result)
 while($row = mysql_fetch_array($result))
 {
   if($row["number"] != '' && $row["name"] != '') {
     
     $name = mysql_real_escape_string ($row["name"]);
     $number = mysql_real_escape_string ($row["number"]);

     if(DEBUG) 
      echo "Name: $name\n Numero: $number\n";

     @$query = "INSERT INTO phonebook.phonebook (owner_id,type, homeemail, workemail, homephone, workphone, cellphone,
                                                fax, title, company, notes, name, homestreet, homepob, homecity,
                                                homeprovince, homepostalcode, homecountry, workstreet, workpob,
                                                workcity, workprovince, workpostalcode, workcountry, url)
                VALUES ('admin', 'speeddial', '', '', '','".$number."', '', '', '', '','', '".$name."', 
						'', '', '','', '', '', '','', '', '', '','', '')";

     if(!mysql_query($query,$local_db) && DEBUG) //print errors if debug is enabled
         echo mysql_error()."\n";

   }
 }
?>
