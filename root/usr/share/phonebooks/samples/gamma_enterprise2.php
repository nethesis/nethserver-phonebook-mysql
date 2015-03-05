#!/usr/bin/php -q
<?php
exec('perl -e \'use NethServer::Directory; my $password = NethServer::Password::store(\'PhonebookDBPasswd\')  ; printf $password;\'',$out); //get sogo db password
$pbookpass = $out[0];


$database = mysql_connect('localhost','pbookuser',$pbookpass) or die("Database error config");
mysql_select_db('phonebook', $database);



$db="DSN_NAME";
$user="dsn_user";
$pass="dsn_pass";

if (isset($argv[1]))
    $i=$argv[1];
else
    $i=1;

while (true)
{
    $query = "WITH ordered AS (
        SELECT CG16_RAGSOANAG,
		CG16_COGNOME,
		CG16_NOME,
		CG16_TEL1NUM,
		CG16_INDEMAIL,
		CG16_FAXNUM,
		CG16_INDIRIZZO,
		CG16_CITTA,
		CG16_PROV,
		CG16_CAP,
		ROW_NUMBER() OVER (ORDER BY CG16_RAGSOANAG,CG16_COGNOME,CG16_NOME) AS 'RowNumber' 
	FROM CG16_ANAGGEN)
	SELECT * FROM ordered WHERE RowNumber = $i";
    
    //Remove newline char from query
    $query=preg_replace('~([\r\n]+|\s\s+)~', ' ', $query);
    $cmd="/usr/bin/isql $db $user $pass -b -d\| <<EOF
$query
EOF";

    //DEBUG
    //print "$cmd\n";

    $row = exec($cmd);

    //Exit from loop if result is empty
    if ($row==''|| !isset($row)) break;

    //DEBUG: print raw query output
    //print "$row\n";

    $res=explode('|',$row);
    {
        $res[]='"'.mysql_real_escape_string(trim($t)).'"';
    }

//   DEBUG
//   print_r($res);
/*
	0	CG16_RAGSOANAG,
        1        CG16_COGNOME,
        2        CG16_NOME,
        3        CG16_TEL1NUM,
        4        CG16_INDEMAIL,
        5        CG16_FAXNUM,
        6        CG16_INDIRIZZO,
        7        CG16_CITTA,
        8        CG16_PROV,
        9        CG16_CAP,
	10	row number
*/
    $azienda=trim($res[0]);
    $email=trim($res[4]);
    $via=trim($res[6]);
    $citta=trim($res[7]);
    $prov=trim($res[8]);
    $cap=trim($res[9]);
    $tel=str_replace("-","",trim($res[3]));
    $tel=str_replace(" ","",$tel);
    $tel=str_replace("/","",$tel);
    $tel=str_replace("+","00",$tel);
    $fax=str_replace("-","",trim($res[5]));
    $fax=str_replace(" ","",$fax);
    $fax=str_replace("/","",$fax);
    $fax=str_replace("+","00",$fax);
//    $cell=str_replace("-","",trim($res[3]));
//    $cell=str_replace(" ","",$cell);
//    $cell=str_replace("/","",$cell);
//    $cell=str_replace("+","00",$cell);
    $cell='';

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
			cellphone='".mysql_escape_string($cell)."';";

    //DEBUG
    //print "$query_ins\n";

    $result = mysql_query($query_ins,$database);

    $i++;
    //exit if a row was given by input
    if (isset($argv[1]))break;
}
mysql_close($database);

?>
