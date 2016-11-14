# phonebookjs

phonebookjs is a daemon written in nodejs.
The deamon is a simple LDAP server serving all records from phonebook database in LDAP format.

Features:

- all records are stored in-memory after the startup: to refresh the cache, restart the server
- SSL and authentication are not supported
- all search are case insensitive

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
- Default base `DN: dc=directory,dc=nh`
- Bind: no authentication is required
- Query by name: `(|(cn=%)(givenName=%)(ou=%))`
- Query by number: `(|(telephoneNumber=%)(mobile=%)(homePhone=%))`
