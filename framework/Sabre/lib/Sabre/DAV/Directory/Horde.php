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


        /**
     * Creates a new file in the directory
     *
     * data is a readable stream resource
     *
     * @param string $name Name of the file
     * @param resource $data Initial payload
     * @return void
     */
    public function createFile($name, $data = null)
    {
        die('FIXME');
    }

    /**
     * Creates a new subdirectory
     *
     * @param string $name
     * @return void
     */
    public function createDirectory($name)
    {
        die('FIXME');
    }

    /**
     * Checks if a child-node with the specified name exists
     *
     * @return bool
     */
    public function childExists($name)
    {
        die('FIXME');
    }

    /**
     * Deleted the current node
     *
     * @return void
     */
    public function delete()
    {
        die('FIXME');
    }

    /**
     * Returns the name of the node
     *
     * @return string
     */
    public function getName()
    {
        return implode('/', array($this->_app, $this->_path));
    }

    /**
     * Renames the node
     *
     * @param string $name The new name
     * @return void
     */
    public function setName($name)
    {
        throw new Horde_Exception('Not Implemented');
    }

    /**
     * Returns the last modification time, as a unix timestamp
     *
     * @return int
     */
    public function getLastModified()
    {
        # FIXME: There may be a better way to handle this
        return time();
    }
}
