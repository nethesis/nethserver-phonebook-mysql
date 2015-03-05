#!/usr/bin/php -q
<?php
exec('perl -e \'use NethServer::Directory; my $password = NethServer::Password::store(\'PhonebookDBPasswd\')  ; printf $password;\'',$out); //get sogo db password
$pbookpass = $out[0];


$database = mysql_connect('localhost','pbookuser',$pbookpass) or die("Database error config");
mysql_select_db('phonebook', $database);



$db="business";
$user="sa";
$pass="nts";

if (isset($argv[1]))
    $i=$argv[1];
else
    $i=1;

while (true)
{
    $query = "WITH ordered AS (
        SELECT an_descr1,
		an_descr2,
		an_contatt,
		an_telef,
		an_email,
		an_faxtlx,
		an_indir,
		an_citta,
		an_prov,
		an_cap,
		ROW_NUMBER() OVER (ORDER BY an_descr1,an_descr2,an_contatt) AS 'RowNumber' 
	FROM ANAGRA)
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
	0	n_descr1,
	1       an_descr2,
	2       an_contatt,
	3       an_telef,
	4       an_email,
	5       an_faxtlx,
	6       an_indir,
	7       an_citta,
	8       an_prov,
	9       an_cap
	10	row number
*/
    $azienda=trim($res[0]).' '.trim($res[1]);
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
