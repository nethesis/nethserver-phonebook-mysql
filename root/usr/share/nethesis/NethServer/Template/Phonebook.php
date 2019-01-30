<?php

echo $view->header('Phonebook')->setAttribute('template', $T('Phonebook_header'));

$panel = $view->panel();

$panel->insert(
   $view->fieldset()->setAttribute('template', $T('ldap_label'))
   ->insert($view->radioButton('ldap', 'disabled'))
   ->insert($view->fieldsetSwitch('ldap', 'enabled', $view::FIELDSETSWITCH_EXPANDABLE)
     ->insert($view->textInput('ldap_port')))
);


echo $panel;

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);

