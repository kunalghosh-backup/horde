<?php
/**
 * This class implements an locking backend for Sabre_DAV based on
 * Horde_Lock.
 *
 * Copyright 2011 Horde LLC (http://www.horde.org/)
 *
 * @package Sabre
 * @author  Ben Klang <bklang@horde.org>
 * @license LGPL
 */

// TODO: This whole class is a noop right now.
// Implement Horde_Lock connectivity.
class Sabre_DAV_Locks_Backend_Horde extends Sabre_DAV_Locks_Backend_Abstract {

    /**
     * Returns a list of Sabre_DAV_Locks_LockInfo objects  
     * 
     * This method should return all the locks for a particular uri, including
     * locks that might be set on a parent uri.
     *
     * @param string $uri 
     * @return array 
     */
    public function getLocks($uri)
    {
        return true;
        // FIXME: Need a way to pass the app in
        $locks = $GLOBALS['injector']->getInstance('Horde_Lock')->getLocks($app, $uri);


    }

    /**
     * Locks a uri 
     * 
     * @param string $uri 
     * @param Sabre_DAV_Locks_LockInfo $lockInfo 
     * @return bool 
     */
    public function lock($uri,Sabre_DAV_Locks_LockInfo $lockInfo)
    {
        return true;
    }

    /**
     * Removes a lock from a uri 
     * 
     * @param string $uri 
     * @param Sabre_DAV_Locks_LockInfo $lockInfo 
     * @return bool 
     */
    public function unlock($uri,Sabre_DAV_Locks_LockInfo $lockInfo)
    {
        return true;
    }

}
