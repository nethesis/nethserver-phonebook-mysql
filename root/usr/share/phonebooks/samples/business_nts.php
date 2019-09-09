#!/usr/bin/php -q
<?php
/****************************
*  Business DB credentials  *
*****************************/
$dsn="business";
$user="sa";
$pass="nts";
/****************************/

$code = 0;

// Get NethServer phonebook database credentials
exec('perl -e \'use NethServer::Password; my $password = NethServer::Password::store(\'PhonebookDBPasswd\')  ; printf $password;\'',$out);
$pbookpass = $out[0];

// Connect to NethServer phonebook database
$phonebookDB = new PDO(
    'mysql:host=localhost;dbname=phonebook',
    'pbookuser',
    $pbookpass);
$phonebookDB->exec("set names utf8");

// Connect to MSSQL using PDO odbc driver
$mssqlDB = new PDO(
    'odbc:'.$dsn,
    $user,
    $pass);
$phonebookDB->exec("set names utf8");

// Remove Business contacts from centralized phonebook
try {
    $phonebookDB->exec('DELETE FROM phonebook WHERE sid_imported = "business"');
} catch (Exception $e) {
    echo "Error '".$e->getMessage()."' cleaning phonebook";
    $code ++;
}
//Query da modificare secondo le esigenze specifiche del server usato
# ATTENZIONE!! è necessario fare il cast() dei campi di testo per evitare l'errore "Out of Memory"
$query="select cast(an_descr1 as varchar(255)) as azienda1, cast(an_descr2 as varchar(255)) as azienda2,
		cast(an_contatt as varchar(255)) as contatto,
		an_telef as tel,
		an_email as email,
		an_faxtlx as fax,
		an_cell as cell,
		cast(an_indir as varchar(255)) as via,
		cast(an_citta as varchar(255)) as citta,
		an_prov as prov,
		an_cap as cap
	from ANAGRA where an_tipo = 'c' or an_tipo = 'f';";

try {
    $sth = $mssqlDB->prepare($query);
    $sth->execute(array());
} catch (Exception $e) {
    echo "Error '".$e->getMessage()."' executing query: $query";
    $code ++;
}
while ($record = $sth->fetch(PDO::FETCH_ASSOC,PDO::FETCH_ORI_NEXT)) {
    $azienda = $record['azienda1'].' '.$record['azienda2'];
    $azienda = (isset($azienda) ? $azienda : '');
    $nome = (isset($record['contatto']) ? $record['contatto'] : '' );
    $email = (isset($record['email']) ? $record['email'] : '' );
    $via = (isset($record['via']) ? $record['via'] : '' );
    $citta = (isset($record['citta']) ? $record['citta'] : '' );
    $prov= (isset($record['prov']) ? $record['prov'] : '' );
    $cap= (isset($record['cap']) ? $record['cap'] : '' );
    foreach (['tel','fax','cell','homephone'] as $field) {
        if (isset($record[$field])) {
            $$field = preg_replace("/-| |\//","",$record[$field]);
            $$field = str_replace("+","00",$$field);
        } else {
            $$field = '';
        }
    }

    $query_ins = "INSERT INTO phonebook
        (company,name,workphone,fax,workemail,workstreet,workcity,workprovince,workpostalcode,cellphone,type,sid_imported)
        VALUES
        (?,?,?,?,?,?,?,?,?,?,?,?)";

    try {
        $sth2 = $phonebookDB->prepare($query_ins);
        $sth2->execute(array(
            $azienda,
            $nome,
            $tel,
            $fax,
            $email,
            $via,
            $citta,
            $prov,
            $cap,
            $cell,
            'business',
            'business'
        ));
    } catch (Exception $e) {
        echo "Error '".$e->getMessage()."' executing query: $query_ins";
        $code ++;
    }
}

exit($code);

