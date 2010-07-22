#!/usr/bin/php -q
<?

$database = mysql_connect('localhost','pbookuser','pbookpass') or die("Database error config");
mysql_select_db('phonebook', $database);


 function ODBCconnect(){ 
     //Modifica i seguenti parametri di accesso all'odbc
     $dsn="DSN_NAME";
     $user="dsn_user";
     $pass="dsn_pass";

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

 $query="select an_descr1 as azienda1, an_descr2 as azienda2, 
		an_contatt as contatto, 
		an_telef as tel, 
		an_email as email, 
		an_faxtlx as fax, 
		an_indir as via, 
		an_citta as citta, 
		an_prov as prov, 
		an_cap as cap 
	from ANAGRA;";

 $rubrica_ext = ODBCquery2array($query);

 $drop_table = "TRUNCATE table rubrica"; //Azzera anche id autoincrement
 $result = mysql_query($drop_table,$database);
 
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

 	$query_ins = "INSERT INTO rubrica SET 
 			azienda='".mysql_escape_string($azienda)."', 
			nome='".mysql_escape_string($nome)."', 
			tel='".mysql_escape_string($tel)."', 
			fax='".mysql_escape_string($fax)."', 
			email='".mysql_escape_string($email)."', 
			via='".mysql_escape_string($via)."', 
			citta='".mysql_escape_string($citta)."', 
			prov='".mysql_escape_string($prov)."', 
			cap='".mysql_escape_string($cap)."', 
			cell='".mysql_escape_string($cell)."';";

 	$result = mysql_query($query_ins,$database);
 }
 
  system("/usr/share/phonebooks/rubrica2ldap");

 ?>

