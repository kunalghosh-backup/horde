<?php
/**
 * Horde_ActiveSync_Message_Folder class represents a single ActiveSync Folder
 * object.
 *
 * @copyright 2010 The Horde Project (http://www.horde.org)
 *
 * @author Michael J. Rubinsky <mrubinsk@horde.org>
 * @package Horde_ActiveSync
 */
class Horde_ActiveSync_Message_Folder extends Horde_ActiveSync_Message_Base
{
    public $serverid;
    public $parentid;
    public $displayname;
    public $type;

    public function __construct($params = array())
    {
        $mapping = array (
            SYNC_FOLDERHIERARCHY_SERVERENTRYID => array (Horde_ActiveSync_Message_Base::KEY_ATTRIBUTE => 'serverid'),
            SYNC_FOLDERHIERARCHY_PARENTID => array (Horde_ActiveSync_Message_Base::KEY_ATTRIBUTE => 'parentid'),
            SYNC_FOLDERHIERARCHY_DISPLAYNAME => array (Horde_ActiveSync_Message_Base::KEY_ATTRIBUTE => 'displayname'),
            SYNC_FOLDERHIERARCHY_TYPE => array (Horde_ActiveSync_Message_Base::KEY_ATTRIBUTE => 'type')
        );

        parent::__construct($mapping, $params);
    }

    public function getClass()
    {
        return 'Folders';
    }
}