<?php
/**
 * This class implements an authentication backend for Sabre_DAV based on
 * Horde_Auth.
 *
 * Copyright 2011 Horde LLC (http://www.horde.org)
 *
 * @package Sabre
 * @author  Ben Klang <bklang@horde.org>
 * @license LGPL
 */

class Sabre_DAV_Directory_Horde implements Sabre_DAV_ICollection
{

    private $_app;
    private $_path;

    public function __construct($app, $path) {
        $this->_app = $app;
        $this->_path = $path;
    }

    public function getChildren()
    {
        $children = $GLOBALS['registry']->$app->browse($this->_path);

        foreach($children as $child) {

        }

    }


    /**
     * Returns a child object by its name.
     *
     * This method makes use of the getChildren method to grab all the child nodes, and compares the name. 
     * Generally its wise to override this, as this can usually be optimized
     * 
     * @param string $name
     * @throws Sabre_DAV_Exception_FileNotFound
     * @return Sabre_DAV_INode 
     */
    public function getChild($name) {
        $GLOBALS['registry']->$app->$browse();
    }
}
