=======
Rubrica
=======

Configura la rubrica centralizzata.
Il sistema si occupa di popolare il database della rubrica usando le
sorgenti selezionate.
La rubrica è rappresentata dalla tabella *phonebook* all'interno del database MySQL chiamato *phonebook*.

La rubrica è ricreata da zero ogni giorni.
Script personalizzati per importazioni speciali possono essere posizionati in ``/usr/share/phonebooks/scripts/``.

Contatti condivisi di SOGo
  Importa i contatti condivisi con tutti gli utenti.
Contatti condivisi di NethCTI
  Importa i contatti personali marcati come *pubblici*.
Numeri brevi
  Importa i numeri brevi da NethVoice.
  
Esporta contatti in LDAP
   Se abilitato, tutti i contatti della rubrica sono accessibili attraverso LDAP.
   La rubrica è accessibile usando il ramo LDAP: *dc=phonebook,dc=nh*

