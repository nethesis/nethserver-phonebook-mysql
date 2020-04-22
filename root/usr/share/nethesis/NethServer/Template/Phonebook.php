<?php

echo $view->header('Phonebook')->setAttribute('template', $T('Phonebook_header'));

$panel = $view->panel()
    ->insert($view->fieldset()->setAttribute('template', $T('sources_label'))
        ->insert($view->checkBox('nethcti', 'enabled')->setAttribute('uncheckedValue', 'disabled'))
        ->insert($view->checkBox('speeddial', 'enabled')->setAttribute('uncheckedValue', 'disabled'))
    );

$panel->insert(
   $view->fieldset()->setAttribute('template', $T('ldap_label'))
   ->insert($view->radioButton('ldap', 'disabled'))
   ->insert($view->fieldsetSwitch('ldap', 'enabled', $view::FIELDSETSWITCH_EXPANDABLE)
     ->insert($view->textInput('ldap_port'))
     ->insert($view->textInput('baseDn', $view::STATE_READONLY))
    ))
->insert($view->fieldset()->setAttribute('template', $T('ldaps_label'))
     ->insert($view->radioButton('ldaps', 'disabled'))
     ->insert($view->fieldsetSwitch('ldaps', 'enabled', $view::FIELDSETSWITCH_EXPANDABLE)
        ->insert($view->textInput('ldaps_port'))
        ->insert($view->textInput('baseDn', $view::STATE_READONLY))
        ->insert($view->textInput('username', $view::STATE_READONLY))
        ->insert($view->textInput('password', $view::STATE_READONLY))
    ))
;


echo $panel;

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);

