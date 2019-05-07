<?php
namespace NethServer\Module;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
 * 
 * This script is part of NethServer.
 * 
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

use Nethgui\System\PlatformInterface as Validate;

/**
 * Manage phonebook module
 *
 * @author Giacomo Sanchietti<giacomo.sanchietti@nethesis.it>
 */
class Phonebook extends \Nethgui\Controller\AbstractController
{

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return \Nethgui\Module\SimpleModuleAttributesProvider::extendModuleAttributes($base, 'Configuration', 10);
    }

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('ldap', Validate::SERVICESTATUS, array('configuration', 'phonebookjs', 'status'));
        $this->declareParameter('ldap_port', Validate::PORTNUMBER, array('configuration', 'phonebookjs', 'TCPPort'));
        $this->declareParameter('nethcti', Validate::SERVICESTATUS, array('configuration','phonebook', 'nethcti'));
        $this->declareParameter('speeddial', Validate::SERVICESTATUS, array('configuration','phonebook', 'speeddial'));
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        if( $this->getRequest()->isMutation()) {
            $port = $this->getPlatform()->getDatabase('configuration')->getProp('slapd', 'TCPPorts');
            if ($port == $this->parameters['ldap_port']) {
                $report->addValidationErrorMessage($this, 'ldap_port', 'ldap_port_inuse', array($port));
            }
        }
        parent::validate($report);
    }

    protected function onParametersSaved($changes)
    {
        $sids = array();
        foreach ($changes as $change) {
            if ($change == 'nethcti' || $change == 'speeddial') {
                $sids[] = $change;
            }
        }
        if (!empty($sids)) {
            $this->getPlatform()->signalEvent('nethserver-phonebook-mysql-save &',$sids);
        }
        if (in_array('ldap',$changes) || in_array('ldap_port',$changes)) {
            $this->getPlatform()->signalEvent('nethserver-phonebook-mysql-fwsave &');
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);

        $view['ldapDatasource'] = array_map(function($fmt) use ($view) {
            return array($fmt, $view->translate($fmt . '_label'));
        }, array('enabled', 'disabled'));

    }

}
