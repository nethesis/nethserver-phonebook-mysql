#!/usr/bin/php -q
<?
exec('perl -e \'use NethServer::Directory; my $password = NethServer::Password::store(\'PhonebookDBPasswd\')  ; printf $password;\'',$out); //get sogo db password
$pbookpass = $out[0];

$database = mysql_connect('localhost','pbookuser',$pbookpass) or die("Database error config");
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

 //Questa funzione definisce la connessione con il DNS creato precedentemente

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

 //Quest'ultima utilizza la funzione precedente, e distribuisce il risultato in un array
 # ATTENZIONE!! è necessario fare il cast() dei campi di testo per evitare l'errore "Out of Memory"

 $query="select cast(CG16_RAGSOANAG as varchar(255)) as azienda, cast(CG16_COGNOME as varchar(255)) as cognome, cast(CG16_NOME as varchar(255)) as nome, CG16_TEL1NUM as tel, CG16_INDEMAIL as email, CG16_FAXNUM as fax, cast(CG16_INDIRIZZO as varchar(255)) as via, cast(CG16_CITTA as varchar(255)) as citta, CG16_PROV as prov, CG16_CAP as cap from CG16_ANAGGEN;";
 $rubrica_ext = ODBCquery2array($query);

 foreach ($rubrica_ext as $record) {
        $azienda=$record['azienda'];
        $nome=$record['cognome']." ".$record['nome'];
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
 
 ?>

