#!/usr/bin/php -q
<?php

exec('perl -e \'use NethServer::Password; my $password = NethServer::Password::store(\'PhonebookDBPasswd\')  ; printf $password;\'',$out); //get sogo db password
$pbookpass = $out[0];

$database = mysql_connect('localhost','pbookuser',$pbookpass) or die("Database error config");
mysql_select_db('phonebook', $database);
mysql_set_charset("utf8");

 function ODBCconnect(){ 
     //Modifica i seguenti parametri di accesso all'odbc
     $dsn="business";
     $user="sa";
     $pass="nts";

     global $ODBC_handle;
     if (!isset($ODBC_handle)){
         $ODBC_handle = odbc_connect($dsn,$user,$pass);
     }
     if (!isset($ODBC_handle)) die("ODBCconnect() failed!");
 }

 //Questa funzione definisce la connessione con il DSN creato precedentemente

 function ODBCquery($query){
     ODBCconnect();
     global $ODBC_handle;
     return odbc_exec($ODBC_handle,$query);
 }

 //Questa funzione esegue la query su MSSQLSERVER tramite l'odbc

 function ODBCquery2array($query){
     $result = ODBCquery($query);
     while ($row = odbc_fetch_array($result)){
         $returnarray[] = $row;
     }
     if (isset($returnarray)) return $returnarray; else return false;
 }

 //Query da modificare secondo le esigenze specifiche del server usato
 # ATTENZIONE!! è necessario fare il cast() dei campi di testo per evitare l'errore "Out of Memory"

 $query="select cast(an_descr1 as varchar(255)) as azienda1, cast(an_descr2 as varchar(255)) as azienda2, 
		cast(an_contatt as varchar(255)) as contatto, 
		an_telef as tel, 
		an_email as email, 
		an_faxtlx as fax, 
		cast(an_indir as varchar(255)) as via, 
		cast(an_citta as varchar(255)) as citta, 
		an_prov as prov, 
		an_cap as cap 
	from ANAGRA;";

 $rubrica_ext = ODBCquery2array($query);

 foreach ($rubrica_ext as $record) {
        $azienda=$record['azienda1'].' '.$record['azienda2'];
        $nome=$record['contatto'];
        $email=$record['email'];
        $via=$record['via'];
        $citta=$record['citta'];
        $prov=$record['prov'];
        $cap=$record['cap'];
        $cell=$record['cell'];
        $tel=str_replace("-","",$record['tel']);
        $tel=str_replace(" ","",$tel);
        $tel=str_replace("/","",$tel);
        $tel=str_replace("+","00",$tel);
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
			cellphone='".mysql_escape_string($cell)."';";

 	$result = mysql_query($query_ins,$database);
 }
 


