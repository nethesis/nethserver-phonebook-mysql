CREATE DATABASE IF NOT EXISTS ext_phonebook;

USE ext_phonebook;

CREATE TABLE IF NOT EXISTS `rubrica` (
  `nome` varchar(150) NOT NULL default '',
  `azienda` varchar(150) NOT NULL default '',
  `tel` varchar(20) NOT NULL default '',
  `cell` varchar(20) NOT NULL default '',
  `fax` varchar(20) NOT NULL default '',
  `email` varchar(50) NOT NULL default '',
  `via` varchar(200) NOT NULL default '',
  `citta` varchar(100) NOT NULL default '',
  `cap` varchar(20) NOT NULL default '',
  `prov` varchar(30) NOT NULL default '',
  `id` int(10) auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

GRANT ALL on rubrica TO pbookuser identified by 'pbookpass';
GRANT SELECT on ext_phonebook.rubrica TO horde;

FLUSH privileges;
