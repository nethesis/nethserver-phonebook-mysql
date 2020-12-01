# phonebookjs

phonebookjs is a daemon written in nodejs.
The deamon is a simple LDAP server serving all records from phonebook database in LDAP format.

Features:

- all records are stored in-memory after the startup: to refresh the cache, restart the server
- SSL and authentication are not supported
- all search are case insensitive

Configuration is saved inside `/usr/share/phonebookjs/config.json`: 
```
{
  "basedn" : "dc=phonebook, dc=nh",
  "port": 10389,
  "debug": false,
  "db_name": "phonebook",
  "db_user": "pbookuser",
  "db_host" : "localhost",
  "db_port" : "3306",
  "db_pass": "password",
  "user": "nobody",
  "group": "nobody"
}

```

## Start and stop

The phonebookjs can be managed using ``systemctl``:

```
systemctl <start|stop|restart> phonebookjs
```

## Log

The log can be inspected using:

```
journalctl -u phonebookjs
```


## How to test it

Enable the server:

```
config setprop phonebookjs status enabled
signal-event nethserver-phonebook-mysql-update
```

Install the LDAP client:

```
yum install openldap-clients -y
```

Dump the entire LDAP tree:

```
ldapsearch -H ldap://localhost:10389 -x -b 'dc=phonebook,dc=nh'
```

Examples of queries:

```
ldapsearch -H ldap://localhost:10389 -x -b 'dc=phonebook,dc=nh' '(|(cn=*nethesis*)(givenName=*nethesis*)(ou=*nethesis*))'
ldapsearch -H ldap://localhost:10389 -x -b 'dc=phonebook,dc=nh' '(|(telephoneNumber=*0721*)(mobile=*0721*)(homePhone=*0721*))'
```

## Client configuration

- Default port: `10389`
- Default base `DN: dc=phonebook,dc=nh`
- Bind: no authentication is required
- Query by name: `(|(sn=%)(cn=%)(givenName=%)(o=%))`
- Query by number: `(|(telephoneNumber=%)(mobile=%)(homePhone=%))`

## Change search results limit number

As default, searches will return ``500`` results.

To change the number of results, use the ``Limit`` property:
```
config setprop phonebookjs Limit 800
signal-event nethserver-phonebook-mysql-save
```

If "limit" value is less or equal to 0, no limit will be applied.

## Upgrading

When upgrading old installation, remember to fix slapd configuration by removing
the SQL driver:

```
systemctl stop slapd
grep -Rl "/usr/lib64/openldap/back_sql.la" /etc/openldap/slapd.d/  | xargs rm -f
grep -Rl "olcSqlConfig" /etc/openldap/slapd.d/  | xargs rm -f
systemctl start slapd
```

## Known issues

Right now, LDAP configuration is in /usr/share/phonebookjs directory and this is incorrect according to FHS.
It will be moved into /etc/phonebookjs in the future
