#!/usr/bin/php
<?php
/**
	This script extracts contacts from sogo and add them to centralized ponebook.
**/
define("DEBUG",false);
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
require_once("VCard.php");

if($argc<2)
	help();
$user = $argv[1];
if($argc>2)
  $name = $argv[2];
else
  $name = 'default';

if(DEBUG)
  echo "Exporting phonebook $user $name\n";

//get sogo db password
exec('perl -e \'use NethServer::Password; my $password = NethServer::Password::store("sogo") ; printf $password;\'',$out);
$db_pass = $out[0];

$link = mysql_connect("localhost","sogo",$db_pass);
mysql_select_db("sogo", $link);
mysql_set_charset("utf8");

// get pbookuser db password
exec('perl -e \'use NethServer::Password; my $password = NethServer::Password::store("PhonebookDBPasswd") ; printf $password;\'',$out2);
$dpass2 = $out2[0];

$local_db = mysql_connect("localhost", "pbookuser", $dpass2);
mysql_select_db('phonebook', $local_db );

if($name!='default') //no phonebook name specified: extract all users's phonebooks
  $n_query = "AND c_foldername='$name'";
else
  $n_query = "";

if($user == "all") //export all shared phonebooks
{
	$query = "select c_location,c_acl_location,c_path2 from sogo_folder_info where c_folder_type='Contact'";
	$res = mysql_query($query,$link);
	while($t = mysql_fetch_row($res))
	{
		$tmp = explode("/",$t[1]);
        	$acl = end($tmp);
		$cres = mysql_query("select * from $acl where c_uid='<default>' and c_role='ObjectViewer'",$link); //check if phonebook is shared
		if(mysql_num_rows($cres)) //at least one record
		{
			$tmp = explode("/",$t[0]);
			$tables[] = end($tmp);
			$users[end($tmp)] = $t[2];
		}
	}


}
else //export only selected phonebooks
{
	$query = "select c_path2,c_foldername,c_location,c_acl_location from sogo_folder_info where c_folder_type='Contact' AND c_path2='$user' $n_query";
	$res = mysql_query($query,$link);
	while($t = mysql_fetch_row($res))
	{
		$tmp = explode("/",$t[2]);
		$tables[] = end($tmp);
		$users[end($tmp)] = $user;
	}
}

// Remove Sogo contacts from centralized phonebook
mysql_query('DELETE FROM phonebook WHERE sid_imported = "sogo"',$local_db);

foreach($tables as $table)
{
	if(DEBUG)	
	  echo " ==== $user -> $table ===\n";
	$query="SELECT c_content FROM $table WHERE c_deleted IS NULL";
	$res2 = mysql_query($query,$link);
	while($row = mysql_fetch_row($res2)) {
		$TEL= array();
		$ADR= array();
		$EMAIL= array();
		$cards[] = $row[0];
		$card = new Contact_Vcard_Parse();

		$data = $card->fromText($row[0]);
		$FN =  mysql_real_escape_string($data[0]['FN'][0]['value'][0][0]);
		if(!$FN) //if no Full Name, use Name + Surname
			$FN = mysql_real_escape_string($data[0]['N'][0]['value'][1][0]." ".$data[0]['N'][0]['value'][0][0]);
		if(!$FN) //if no Name use, Nickname
			$FN = mysql_real_escape_string($data[0]['NICKNAME'][0]['value'][0][0]);
		if(!$FN) //else skip contact
			continue;

		$ORG =  mysql_real_escape_string($data[0]['ORG'][0]['value'][0][0]);
		$TITLE =  mysql_real_escape_string($data[0]['TITLE'][0]['value'][0][0]);
		$NOTE =  mysql_real_escape_string($data[0]['NOTE'][0]['value'][0][0]);
		$url =  mysql_real_escape_string($data[0]['URL'][0]['value'][0][0]);
		
		if(count($data[0]['TEL']))
			foreach($data[0]['TEL'] as $tel)
			{
				$TEL[strtolower($tel['param']['TYPE'][0])] =   mysql_real_escape_string($tel['value'][0][0]);
			}
		if(count($data[0]['ADR']))
			foreach($data[0]['ADR'] as $adr)
			{
				$ADR[strtolower($adr['param']['TYPE'][0])]['street'] =   mysql_real_escape_string($adr['value'][2][0]);
				$ADR[strtolower($adr['param']['TYPE'][0])]['city'] =   mysql_real_escape_string($adr['value'][3][0]);
				$ADR[strtolower($adr['param']['TYPE'][0])]['prov'] =   mysql_real_escape_string($adr['value'][4][0]);
				$ADR[strtolower($adr['param']['TYPE'][0])]['code'] =   mysql_real_escape_string($adr['value'][5][0]);
				$ADR[strtolower($adr['param']['TYPE'][0])]['country'] =   mysql_real_escape_string($adr['value'][6][0]);
			}
		if(count($data[0]['EMAIL']))
			foreach($data[0]['EMAIL'] as $em)
				$EMAIL[strtolower($em['param']['TYPE'][0])] =   mysql_real_escape_string($em['value'][0][0]);
		
		//map vcard 2.1 fields to vcard 3.0 fields
		if ($EMAIL['internet'])
		{
			$EMAIL['work']	= $EMAIL['internet'];
		}
		if ($TEL['voice'])
                {
                        $TEL['work']  = $TEL['voice'];
                }

		if(DEBUG)
		{
			echo "\n\n== $FN == \n";
			echo " TEL = ".print_r($TEL,true)."\n";
			echo " ADR = ".print_r($ADR,true)."\n";
			echo " EMAIL = ".print_r($EMAIL,true)."\n";
			echo " ORG = $ORG\n";
			echo " TITLE = $TITLE\n";
			echo " NOTE = $NOTE\n";
			echo " url = $url\n";
		}

		@$query = "INSERT INTO phonebook.phonebook (owner_id,workemail,homeemail,homephone,workphone,cellphone,fax,title,company,name,homestreet,homecity,homeprovince,homepostalcode,homecountry,workstreet,workcity,workprovince,workpostalcode,workcountry,notes,url,type,sid_imported) VALUES ('{$users[$table]}','{$EMAIL['work']}','{$EMAIL['home']}','{$TEL['home']}','{$TEL['work']}','{$TEL['cell']}','{$TEL['fax']}','$TITLE','$ORG','$FN','{$ADR['home']['street']}','{$ADR['home']['city']}','{$ADR['home']['prov']}','{$ADR['home']['code']}','{$ADR['home']['country']}','{$ADR['work']['street']}','{$ADR['work']['city']}','{$ADR['work']['prov']}','{$ADR['work']['code']}','{$ADR['work']['country']}','$NOTE','$url','sogo','sogo')";
		if(!mysql_query($query,$local_db) && DEBUG) //print errors if debug is enabled
		 echo mysql_error()."\n";
	}


}


function help()
{
	echo "Export  SOGo phonebooks to system shared phonebook.\n\nUsage: export.php <user> [name]\n\nWhere:\n\tuser: the phonebook owner, or 'all' for all users.\n\tname: the phonebook name. If no name is specified, the script will export all user's phonebooks.\n";
	exit(0);
}
?>
