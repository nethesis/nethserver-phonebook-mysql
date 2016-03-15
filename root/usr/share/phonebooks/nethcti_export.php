#!/usr/bin/php
<?php

 exec('perl -e \'use NethServer::Password; my $password = NethServer::Password::store("CTIDBPasswd") ; printf $password;\'',$out);
 $duser = 'nethcti';
 $dpass = $out[0];
 $dhost = 'localhost';

 $link = @mysql_connect($dhost, $duser, $dpass) or die ("Can't connect to nethcti DB\n"); 
 mysql_select_db('nethcti2', $link );
 
 exec('perl -e \'use NethServer::Password; my $password = NethServer::Password::store("PhonebookDBPasswd") ; printf $password;\'',$out2);
 $duser2 = 'pbookuser';
 $dpass2 = $out2[0];
 $dhost2 = 'localhost';

 $local_db = mysql_connect($dhost2, $duser2, $dpass2);
 mysql_select_db('phonebook', $local_db );

 define("DEBUG",false);

 $result = mysql_query("SELECT owner_id, homeemail, workemail, homephone, workphone, cellphone, fax, title, company, notes, name, 
				homestreet, homepob, homecity, homeprovince, homepostalcode, homecountry, workstreet, workpob, workcity, 
				workprovince, workpostalcode, workcountry, url FROM cti_phonebook WHERE type='public'", $link);
 if(!mysql_query($query,$link) && DEBUG) //print errors if debug is enablenkd
    echo mysql_error()."\n";

while($row = mysql_fetch_array($result))

 {
     if(DEBUG)
      print_r($row);

     @$query = "INSERT INTO phonebook.phonebook (owner_id, type, homeemail, workemail, homephone, workphone, cellphone, 
                                                fax, title, company, notes, name, homestreet, homepob, homecity, 
                                                homeprovince, homepostalcode, homecountry, workstreet, workpob, 
                                                workcity, workprovince, workpostalcode, workcountry, url) 
		VALUES ('".$row["owner_id"]."', 'nethcti', '".$row["homeemail"]."', '".$row["workemail"]."', '".$row["homephone"]."', 
			'".$row["workphone"]."', '".$row["cellphone"]."', '".$row["fax"]."', '".$row["title"]."', '".$row["company"]."', 
			'".$row["notes"]."', '".$row["name"]."', '".$row["homestreet"]."', '".$row["homepob"]."', '".$row["homecity"]."', 
			'".$row["homeprovince"]."', '".$row["homepostalcode"]."', '".$row["homecountry"]."', '".$row["workstreet"]."', 
			'".$row["workpob"]."', '".$row["workcity"]."', '".$row["workprovince"]."', '".$row["workpostalcode"]."', 
			'".$row["workcountry"]."', '".$row["url"]."')";
     if(DEBUG)
       echo $query;
     if(!mysql_query($query,$local_db) && DEBUG) //print errors if debug is enabled
         echo mysql_error()."\n";

 }
?>

