//
// phonebook.js
// 
// This is a simple LDAP server which reads records from 
// phonebook MySQL database and return results in LDAP format.
//
// No LDAP bind is requires by clients.
//
// Usage:
//    node phonebook.js <config_file>
//
// The config file must be in JSON format

const util = require('util')
var ldap = require('ldapjs');
var mysql = require("mysql");
var addrbooks = [];
var config_file = "";
var config = {
  "debug" : false,
  "port" : 389,
  "db_host" : "localhost",
  "db_port" : "3306",
  "db_user" : "",
  "db_pass" : "",
  "db_name" : "",
  "basedn" : "dc=phonebook, dc=nh",
  "user": "nobody",
  "group": "nobody",
  "limit": -1
}

// load config file;
if (process.argv[2]) {
  config_file = process.argv[2];
}

if (config_file) {
  if ( !config_file.startsWith("/") ) {
      config_file = "./"+config_file;
  }
  config = require(config_file);
}
_debug("Loaded config: "+util.inspect(config));

var server = ldap.createServer();
var db = mysql.createConnection({
  host: config.db_host,
  port: config.db_port,
  user: config.db_user,
  password: config.db_pass,
  database: config.db_name
});



function _debug (msg) {
  if (config.debug) {
      console.log(msg);
  }
}

db.query("SELECT name,company,homephone,workphone,cellphone,fax FROM phonebook", function(err, contacts) {
  if (err) {
    console.log("Error fetching records", err);
    process.exit(1);
  }

  for (var i = 0; i < contacts.length; i++) {

    if (!contacts[i].workphone && !contacts[i].cellphone && !contacts[i].homephone) {
        continue;
    }

    company = contacts[i].company;
    if (company) {
        company = company.toLowerCase();
    }

    if (contacts[i].name) {
        name = contacts[i].name.toLowerCase();
    } else {
        if (company) {
          name = company;
        } else {
          continue;
        }
    }
    // replace invalid chars in dn
    name = name.replace(/\+/g,' ');
    name = name.replace(/,/g,' ');
    name = name.toLowerCase();

    var cn = "cn=" + name + ", " + config.basedn;
    try {
      var dn = ldap.parseDN(cn);
    } catch (err) {
      // skip still invalid dn
      _debug("Skipping invalid CN:" + dn.toString());
      continue;
    }

    _debug("Adding CN: "+cn);
    var obj = { dn: cn, attributes: {objectclass: [ "inetOrgPerson" ], cn: name,sn: name, givenName: name } };
    if (contacts[i].workphone) {
        obj.attributes.telephoneNumber = contacts[i].workphone;
    }
    if (contacts[i].cellphone) {
        obj.attributes.mobile = contacts[i].cellphone;
    }
    if (contacts[i].homephone) {
        obj.attributes.homePhone = contacts[i].homephone;
    }

    if (company) {
        obj.attributes.o = company;
    }

    addrbooks.push(obj);
  }

  server.bind(config.basedn, function (req, res, next) {
    // Only anonymous bind
    res.end();
    return next();
  });

  server.search(config.basedn, function(req, res, next) {
    // Gigaset workaround
    if (req.filter == '(objectclass=*)') {
      for (index = 0; index < req.baseObject.rdns.length; ++index) {
        if (req.baseObject.rdns[index].attrs.cn && req.baseObject.rdns[index].attrs.cn.value) {
          req.filter = new ldap.EqualityFilter({
            attribute: 'cn',
            value: req.baseObject.rdns[index].attrs.cn.value
          });
          _debug("Query filter changed");
          break;
        }
      }
    }
    _debug("Query from " + req.connection.remoteAddress + ":" + req.filter);
    sent = 0;
    for (var i = 0; i < addrbooks.length; i++) {
      if (req.filter.matches(addrbooks[i].attributes)) {
        if (config.limit > 0 && sent >= config.limit) {
            break;
        } else {
            res.send(addrbooks[i]);
            sent++;
        }
      }
    }
    res.end();
  });

  server.listen(config.port, function() {
    console.log("phonebook.js started at " + server.url);
    process.setgid(config.group);
    process.setuid(config.user);
  });
});
