#!/usr/bin/php -q
<?php
exec('perl -e \'use NethServer::Password; my $password = NethServer::Password::store(\'PhonebookDBPasswd\')  ; printf $password;\'',$out); //get sogo db password

$pbookpass = $out[0];

$database = mysql_connect('localhost','pbookuser',$pbookpass) or die("Database error config");
mysql_select_db('phonebook', $database);

$vtdb= mysql_connect('localhost','vtdbuser','vtdbpass') or die("Database error config");
mysql_select_db('vtigerdb', $vtdb);
mysql_set_charset("utf8");

$query="SELECT  accountname as company, '' as contact, phone as workphone, fax, otherphone as homephone, '' as mobile, bill_city as city, bill_code as code, bill_country as country, bill_state as state, bill_street as street, email1 as email  FROM vtiger_account join vtiger_accountbillads on vtiger_account.accountid=vtiger_accountbillads.accountaddressid
        UNION SELECT accountname as company, concat(firstname,' ',lastname) as contact,  vtiger_contactdetails.phone as workphone, vtiger_contactdetails.fax, '' as homephone, mobile,mailingcity as city, mailingzip as code, mailingcountry as country,mailingstate as state,mailingstreet as street, email FROM vtiger_contactdetails LEFT JOIN vtiger_account on vtiger_account.accountid=vtiger_contactdetails.accountid LEFT JOIN vtiger_contactaddress  on vtiger_contactdetails.contactid=vtiger_contactaddress.contactaddressid";


 $res = mysql_query($query,$vtdb);

// Remove vtiger contacts from centralized phonebook
mysql_query('DELETE FROM phonebook WHERE sid_import = "vtiger"',$database);

 while ($record=mysql_fetch_assoc($res)) {
        $azienda=$record['company'];
        $nome=$record['contact'];
	if (!$nome) $nome = $azienda;
        $email=$record['email'];
        $via=$record['street'];
        $citta=$record['city'];
        $prov=$record['state'];
        $cap=$record['code'];
        $tel=str_replace("-","",$record['workphone']);
        $tel=str_replace(" ","",$tel);
        $tel=str_replace("/","",$tel);
        $tel=str_replace("+","00",$tel);
        $cell=str_replace("-","",$record['mobile']);
        $cell=str_replace(" ","",$cell);
        $cell=str_replace("/","",$cell);
        $cell=str_replace("+","00",$cell);
       	$fax=str_replace("-","",$record['fax']);
        $fax=str_replace(" ","",$fax);
        $fax=str_replace("/","",$fax);
        $fax=str_replace("+","00",$fax);

        $query_ins = "INSERT INTO phonebook  SET 
                        company='".mysql_escape_string($azienda)."', 
                        name='".mysql_escape_string($nome)."', 
                        workphone='".mysql_escape_string($tel)."', 
                        fax='".mysql_escape_string($fax)."', 
                        workemail='".mysql_escape_string($email)."', 
                        workstreet='".mysql_escape_string($via)."', 
                        workcity='".mysql_escape_string($citta)."', 
                        workprovince='".mysql_escape_string($prov)."', 
                        workpostalcode='".mysql_escape_string($cap)."', 
                        cellphone='".mysql_escape_string($cell)."',
                        type='vtiger',
                        sid_import='vtiger';";
 	$result = mysql_query($query_ins,$database);
 }
 
