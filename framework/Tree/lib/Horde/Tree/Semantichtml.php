<?php
/**
 * The Horde_Tree_Html:: class provides HTML specific rendering functions.
 *
 * Copyright 2003-2011 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Marko Djukic <marko@oblo.com>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Tree
 */
class Horde_Tree_Semantichtml extends Horde_Tree_Base
{
    /**
     * Allowed parameters for nodes.
     *
     * @var array
     */
    protected $_allowed = array(
        'class',
        'url',
        'urlclass',
        'title',
        'target',
    );

   /**
     * Constructor.
     *
     * @param string $name   The name of this tree instance.
     * @param array $params  Additional parameters:
     * <pre>
     * alternate - (boolean) Alternate shading in the table?
     *             DEFAULT: false
     * class - (string) The class to use for the table.
     *         DEFAULT: ''
     * hideHeaders - (boolean) Don't render any HTML for the header row, just
     *               use the widths.
     *               DEFAULT: false
     * lines - (boolean) Show tree lines?
     *         DEFAULT: true
     * lines_base - (boolean) Show tree lines for the base level? Requires
     *              'lines' to be true also.
     *              DEFAULT: false
     * multiline - (boolean) Do the node labels contain linebreaks?
     *             DEFAULT: false
     * </pre>
     */
    public function __construct($name, array $params = array())
    {
        $params = array_merge(array(
            'lines' => true
        ), $params);

        parent::__construct($name, $params);
    }

    /**
     * Returns the tree.
     *
     * @return string  The HTML code of the rendered tree.
     */
    public function getTree($static = false)
    {
        $tree = '';
        foreach ($this->_root_nodes as $node_id) {
            $tree .= $this->_buildTree($node_id);
        }
        return '<ul class="nav">' . $tree . '</ul>';
    }

    /**
     * Recursive function to walk through the tree array and build the output.
     *
     * @param string $node_id  The Node ID.
     *
     * @return string  The tree rendering.
     */
    protected function _buildTree($node_id, $child = false)
    {
        $node = $this->_nodes[$node_id];
        if ($node['label'] == 'Office') {
        }
        if (isset($node['children'])) {
            if ($child) {
                $output = '<li>' . $this->_setLabel($node_id) . '<ul>';
            } else {
                $output = '<li class="menu"><a href="#" class="menu">' . htmlspecialchars($node['label']) . '</a><ul class="menu-dropdown">';
            }
            foreach ($node['children'] as $key => $val) {
                $child_node_id = $node['children'][$key];
                $output .= $this->_buildTree($child_node_id, true);
            }
            $output .= '</ul></li>';
        } else {
            $output = '<li>' . $this->_setLabel($node_id) . '</li>';
        }

        return $output;
    }

    /**
     * Sets the label on the tree line.
     *
     * @param string $node_id  The Node ID.
     *
     * @return string  The label for the tree line.
     */
    protected function _setLabel($node_id)
    {
        $n = $this->_nodes[$node_id];

        $label = $n['label'];
        if (!empty($n['url'])) {
            $target = '';
            if (!empty($n['target'])) {
                $target = ' target="' . $n['target'] . '"';
            } elseif ($target = $this->getOption('target')) {
                $target = ' target="' . $target . '"';
            }
            $output .= '<a' . (!empty($n['urlclass']) ? ' class="' . $n['urlclass'] . '"' : '') . ' href="' . $n['url'] . '"' . $target . '>' . $label . '</a>';
        } else {
            $output .= $label;
        }

        return $output;
    }
}
