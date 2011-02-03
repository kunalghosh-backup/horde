<?php
/**
 * Copyright 2010-2011 Horde LLC
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author   Ben Klang <bklang@horde.org>
 * @category Horde
 * @package  Horde_Rpc
 */

class Horde_Rpc_Webdav extends Horde_Rpc
{

    private $_server;
    private $_enabledCapabilities = array();

    public function __construct($request, $params = array())
    {
        // PHP messages destroy XML output -> switch them off
        // FIXME: On for debugging...
        //ini_set('display_errors', 0);

        $rootNodes = array();
        foreach ($GLOBALS['registry']->listApps() as $app) {
            if ($GLOBALS['registry']->hasAppMethod($app, 'browse')) {
                $rootNodes[] = new Sabre_DAV_Directory_Horde($app, '');
            }
        }
        
        /* Directory structure */
        $root = new Sabre_DAV_SimpleDirectory('root', $rootNodes);
        $objectTree = new Sabre_DAV_ObjectTree($root);

        /* Initializing server */
        $this->_server = new Sabre_DAV_Server($objectTree);

        // Also make sure there is a 'data' directory, writable by the server. This directory is used to store information about locks
        $lockBackend = new Sabre_DAV_Locks_Backend_Horde();
        $lockPlugin = new Sabre_DAV_Locks_Plugin($lockBackend);
        $this->_server->addPlugin($lockPlugin);
        
        $browserPlugin = new Sabre_DAV_Browser_Plugin();
        $this->_server->addPlugin($browserPlugin);

        parent::__construct($request, $params);

    }

    /**
     * Sends an RPC request to the server and returns the result.
     *
     * @param string  The raw request string.
     *
     * @return string  The XML encoded response from the server.
     */
    function getResponse($request)
    {
        $this->_server->exec();
    }

    /**
     * Request that the RPC backend enable requested capabilities.
     *
     * Valid capabilities:
     * authentication
     * caldav
     *
     * @example $rpc->requestCapabilities('authentication', 'caldav');
     *
     * @return true on success
     * @throws Horde_Rpc_Exception, Horde_Exception
     */
    public function requestCapabilities()
    {
        foreach(func_get_args() as $capability) {
            $capability = strtolower($capability);
            if (!in_array($capability, $this->_enabledCapabilities)) {
                switch(strtolower($capability)) {
                case 'authentication':
                    $this->getAuthentication();
                case 'caldav':
                    $this->_getCalDAVServer();
                }
                $this->_enabledCapabilities[] = $capability;
            }
        }
        return true;
    }

    private function _getAuthentication()
    {


        
    }

    private function _getCalDAVServer()
    {
        /* Get Horde objects for backends */
        $auth = $GLOBALS['injector']->getInstance('Horde_Auth');
        $registry = $GLOBALS['injector']->getInstance('Horde_Registry');

        /* Backends */
        $authBackend = new Sabre_DAV_Auth_Backend_Horde($registry);
        $principals = new Sabre_DAV_Auth_PrincipalCollection($authBackend);
        $calendarBackend = new Sabre_CalDAV_Backend_Horde($auth);



        $root->addChild($principals);
        $calendars = new Sabre_CalDAV_CalendarRootNode($authBackend, $calendarBackend);
        $root->addChild($calendars);


        

        /* Server Plugins */
        $authPlugin = new Sabre_DAV_Auth_Plugin($authBackend, 'Horde DAV Server');
        $this->addPlugin($authPlugin);

        $caldavPlugin = new Sabre_CalDAV_Plugin();
        $this->addPlugin($caldavPlugin);
    }
}
