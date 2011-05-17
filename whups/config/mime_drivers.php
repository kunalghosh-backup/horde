<?php
/**
 * MIME Viewer configuration for Whups.
 *
 * Settings in this file override settings in horde/config/mime_drivers.php.
 * All drivers configured in that file, but not configured here, will also
 * be used to display MIME content.
 *
 * IMPORTANT: DO NOT EDIT THIS FILE!
 * Local overrides MUST be placed in mime_drivers.local.php.
 * If the 'vhosts' setting has been enabled in Horde's configuration, you can
 * use mime_drivers-servername.php.
 */

$mime_drivers = array(
    /* Zip File archive viewer. */
    'zip' => array(
        'handles' => array(
            'application/x-compressed',
            'application/x-zip-compressed',
            'application/zip',
            'x-extension/zip',
        ),
        'icons' => array(
            'default' => 'compressed.png'
        )
    )
);
