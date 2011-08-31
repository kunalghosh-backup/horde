<?php
/**
 * Form class for whitelist management.
 *
 * Copyright 2003-2011 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author Max Kalika <max@horde.org>
 * @package Sam
 */
class Sam_Form_Whitelist extends Sam_Form_List
{
    public function __construct($vars)
    {
        $this->_attributes = array(
            'whitelist_from' => _("Whitelist From"),
            'whitelist_to' => _("Whitelist To"),
        );
        parent::__construct($vars, _("Whitelist Manager"));
    }
}