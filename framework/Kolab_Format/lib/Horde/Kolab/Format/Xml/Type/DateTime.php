<?php
/**
 * Handles date-time attributes.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  Kolab_Format
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_Format
 */

/**
 * Handles date-time attributes.
 *
 * Copyright 2011 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you did not
 * receive this file, see
 * http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @since Horde_Kolab_Format 1.1.0
 *
 * @category Kolab
 * @package  Kolab_Format
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_Format
 */
class Horde_Kolab_Format_Xml_Type_DateTime
extends Horde_Kolab_Format_Xml_Type_String
{
    /**
     * Load the value of a node.
     *
     * @param DOMNode $node   Retrieve value for this node.
     * @param array   $params The parameters for this parse operation.
     *
     * @return mixed|null The value or null if no value was found.
     */
    public function loadNodeValue($node, $params = array())
    {
        $result = $params['helper']->fetchNodeValue($node);
        $tz = $node->getAttribute('tz');
        if (empty($tz)) {
            /**
             * @todo Be more strict once KEP2 has been completely adopted
             * if (!$this->isRelaxed()) throw new Horde_Kolab_Format_Exception();
             */
            $tz = 'UTC';
        }
        if (strlen($result) == 10) {
            $date = array(
                'date' => Horde_Kolab_Format_Date::readDate($result, $tz),
                'date-only' => true
            );
        } else {
            $date = array(
                'date' => Horde_Kolab_Format_Date::readDateTime(
                    $result, $tz
                ),
                'date-only' => true
            );
        }
        if ($date['date'] === false && !$this->isRelaxed($params)) {
            throw new Horde_Kolab_Format_Exception(
                sprintf('Invalid date input "%s"!', $result)
            );
        }
        return $date;
    }

    /**
     * Update the specified attribute.
     *
     * @param string       $name        The name of the the attribute
     *                                  to be updated.
     * @param mixed        $value       The value to store.
     * @param DOMNode      $parent_node The parent node of the node that
     *                                  should be updated.
     * @param array        $params      The parameters for this write operation.
     * @param DOMNode|NULL $old_node    The previous value (or null if
     *                                  there is none).
     *
     * @return DOMNode|boolean The new/updated child node or false if this
     *                         failed.
     *
     * @throws Horde_Kolab_Format_Exception If converting the data to XML failed.
     */
    public function saveNodeValue(
        $name,
        $value,
        $parent_node,
        $params,
        $old_node = false
    ) {
        if (!isset($value['date']) || !$value['date'] instanceOf DateTime) {
            throw new Horde_Kolab_Format_Exception(
                sprintf(
                    'Missing or invalid data in the "date" element of the "%s" entry!',
                    $name
                )
            );
        }
        if (empty($value['date-only'])) {
            $date = Horde_Kolab_Format_Date::writeDateTime($value['date']);
        } else {
            $date = Horde_Kolab_Format_Date::writeDate($value['date']);
        }
        $node = parent::saveNodeValue(
            $name, $date, $parent_node, $params, $old_node
        );
        $node->setAttribute('tz', $value['date']->getTimezone()->getName());
        return $node;
    }
}