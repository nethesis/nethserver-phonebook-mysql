=========
Phonebook
=========

Configure the centralized phonebook.
The system takes care to populate the phonebook database using
all selected sources.
The phonebook is the table named *phonebook* inside the MySQL database called *phonebook*.

The phonebook is built from scratch every day.
Custom scripts for special imports can be placed in ``/usr/share/phonebooks/scripts/``.

SOGo shared contacts
  Import all contacts shared with all users.
NethCTI shared contacts
  Import all personal contacts marked as *public*.
Speed dials
  Import speed dials number from NethVoice.
  
Export contacts to LDAP
   If enabled, all contacts from the phonebook can be accessed via LDAP.
   Phonebook is accessible using the LDAP tree: *dc=phonebook,dc=nh*

