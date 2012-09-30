<?php
/**
 * See horde/config/prefs.php for documentation on the structure of this file.
 *
 * IMPORTANT: DO NOT EDIT THIS FILE!
 * Local overrides MUST be placed in pref.local.php or pref.d/.
 * If the 'vhosts' setting has been enabled in Horde's configuration, you can
 * use prefs-servername.php.
 */

$prefGroups['display'] = array(
    'column' => _("Other Preferences"),
    'label' => _("Display Preferences"),
    'desc' => _("Set how to display bookmark listings and how to open links."),
    'members' => array('sortby', 'sortdir', 'show_in_new_window')
);

// bookmark sort order
$_prefs['sortby'] = array(
    'value' => 'dt',
    'locked' => false,
    'type' => 'enum',
    'enum' => array(
        'title' => _("Title"),
        'clicks' => _("Most Clicked"),
        'dt' => _("Bookmarked on"),
    ),
    'desc' => _("Sort bookmarks by:"),
);

// user preferred sorting direction
$_prefs['sortdir'] = array(
    'value' => 1,
    'locked' => false,
    'type' => 'enum',
    'enum' => array(0 => _("Ascending (A to Z or oldest to newest)"),
                    1 => _("Descending (9 to 1 or newest to oldest)")),
    'desc' => _("Sort direction:"),
);

// Open links in new windows?
$_prefs['show_in_new_window'] = array(
    'value' => 1,
    'locked' => false,
    'type' => 'checkbox',
    'desc' => _("Open links in a new window?")
);
