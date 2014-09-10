<?php

echo $view->header('Phonebook')->setAttribute('template', $T('Phonebook_header'));

echo $view->panel()
    ->insert($view->fieldset()->setAttribute('template', $T('sources_label'))
        ->insert($view->checkBox('sogo', 'all')->setAttribute('uncheckedValue', 'disabled'))
        ->insert($view->checkBox('nethcti', 'enabled')->setAttribute('uncheckedValue', 'disabled'))
        ->insert($view->checkBox('speeddial', 'enabled')->setAttribute('uncheckedValue', 'disabled'))
     )
    ->insert($view->fieldset()->setAttribute('template', $T('ldap_label'))
        ->insert($view->radioButton('ldap', 'enabled'))
        ->insert($view->radioButton('ldap', 'disabled'))
    );

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);

