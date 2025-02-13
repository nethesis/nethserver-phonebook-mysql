# Centralized Phonebook

Centralized phonebook is a mysql table where all contacts from various sources are stored.
By default are copied in phonebook CTI public contacts,  NethVoice extensions and every other source the user configure from NethVoice interface.
MySQL and Postgres external databases are supported. Also CSV files.
It is possible also to import contacts from Zucchetti Infinity, by creating the configuration file by hand with Zucchetti base url, username and password:
```
[root@infinity ~]# runagent -m nethvoice1 bash
bash-5.1$ podman exec -it freepbx bash
root@voice:/var/lib/asterisk# cat /etc/phonebook/sources.d/custom_1.json
{"custom_1":{"url":"https://BASE_URL","username":"USERNAME","password":"PASSWORD","dbtype":"infinity","interval":"60","enabled":true}}
```

Test with
```
/usr/share/phonebooks/phonebook-import -v /etc/phonebook/sources.d/custom_1.json
```

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

## Infinity API details

Infinity has two APIs, one tor retrieve the toke, one to retrieves contacts

### **JSON Structure Documentation**

#### **Root Object**
- **Key:** `data`
  **Type:** Array
  **Description:** Contains a list of entities, each representing a company or individual with their respective contact details.

---

### **Entity Structure (Each Item in `data` Array)**

1. **`address`**
   - **Type:** String
   - **Description:** The full postal address of the entity, including street, postal code, city, and province.

2. **`mail`**
   - **Type:** Array
   - **Description:** A list of email addresses associated with the entity.
   - **Each Object in `mail` Array:**
     - **`mail`** (String) – The email address.
     - **`type`** (String) – The type of email (e.g., "primary").

3. **`name`**
   - **Type:** String
   - **Description:** The name of the entity (company or individual).

4. **`tel`**
   - **Type:** Array
   - **Description:** A list of phone numbers associated with the entity.
   - **Each Object in `tel` Array:**
     - **`number`** (String) – The phone number.
     - **`type`** (String) – The type of phone number (e.g., "Telefono fisso" for landline, "Cellulare" for mobile).

5. **`company`**
   - **Type:** String
   - **Description:** The company name the individual is associated with (empty if not applicable).

6. **`id`**
   - **Type:** String
   - **Description:** A unique identifier for the entity.

7. **`office`**
   - **Type:** String
   - **Description:** The office location or designation (e.g., "Principale" for the main office).

8. **`status`**
   - **Type:** String
   - **Description:** The legal/business status of the entity.
   - **Possible Values:**
     - `"SPA"` – Public Limited Company
     - `"SRL"` – Private Limited Company
     - `"PER"` – Individual/Professional
     - `"PUB"` – Public Entity
     - `"IND"` – Industrial
     - `"ALT"` – Alternative/Other

---

### **Example JSON Object**
```json
{
    "address": "via Foo Bar 3/B 20100 MILANO (MI)",
    "mail": [
        {
            "mail": "foom@gmail.com",
            "type": "primary"
        }
    ],
    "name": "Light Dream",
    "tel": [
        {
            "number": "0213245678",
            "type": "Telefono fisso"
        },
        {
            "number": "3201234568",
            "type": "Cellulare"
        }
    ],
    "company": "",
    "id": "000000000000005",
    "office": "Principale",
    "status": "SPA"
}
```

