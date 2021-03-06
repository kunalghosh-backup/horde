<?php
/**
 * This file specifies which backends people using your installation can log
 * in to.
 *
 * IMPORTANT: DO NOT EDIT THIS FILE!
 * Local overrides MUST be placed in backends.local.php or backends.d/.
 * If the 'vhosts' setting has been enabled in Horde's configuration, you can
 * use backends-servername.php.
 *
 * Example configuration file that enables the Samba backend in favor of the
 * FTP backend and sets a server name for the Samba server:
 *
 * <code>
 * <?php
 * $backends['ftp']['disabled'] = true;
 * $backends['smb']['disabled'] = false;
 * $backends['smb']['params']['hostspec'] = 'FILESERVER HOST';
 * </code>
 *
 * Properties that can be set for each server:
 *   - attributes: (array) The list of attributes that the driver supports.
 *       + 'edit'
 *       + 'download'
 *       + 'group'
 *       + 'modified'
 *       + 'name'
 *       + 'owner'
 *       + 'permission'
 *       + 'size'
 *       + 'type'
 *   - createhome: (boolean) If this parameter is set to true, and the home
 *                 directory does not exist, attempt to create the home
 *                 directory on login.
 *   - driver: (string) The VFS (Virtual File System) driver to use.
 *             (See below examples for additional parameters needed.)
 *       + file: Access a local file system.
 *       + ftp: Connect to a FTP server.
 *       + smb: Connect to a SMB fileshare.
 *       + sql: Connect to VFS filesystem stored in SQL database.
 *       + ssh2: Connect to a remote server via SSH2.
 *   - filter: (string) If set, all files that match the regex will be hidden
 *             in the folder view.  The regex must be in PCRE syntax (see
 *             http://www.php.net/pcre).
 *   - home: (string) The directory that will be used as home directory for the
 *           user. This parameter will overrule a home parameter in the params.
 *           If empty, this will default to the active working directory
 *           immediately after logging into the VFS backend (i.e. for ftp,
 *           this will most likely be ~user, for SQL based VFS backends,
 *           this will probably be the root directory).
 *   - hordeauth: (mixed) One of the following values:
 *       + true: Gollem will attempt to use the user's existing credentials
 *               (the username/password they used to log in to Horde) to login
 *               to this source.
 *       + false: [DEFAULT] Everything after and including the first @ in the
 *                username will be stripped off before attempting
 *                authentication.
 *       + 'full': The username will be used unmodified.
 *   - loginparams: (array) A list of parameters that can be changed by the
 *                  user on the login screen.  The key is the parameter name
 *                  that can be changed, the value is the text that will be
 *                  displayed next to the entry box on the login screen.
 *   - name: (string) This is the name displayed in the server list on the
 *           login screen.
 *   - quota: (string) If set, turn on VFS quota checking for the backend (if
 *            supported). Supported values:
 *       + false: [DEFAULT] Quota is disabled.
 *       + 'size [metric]': Quota value. Metric can be one of the following:
 *           - B: bytes [DEFAULT]
 *           - KB: kilobytes
 *           - MB: megabytes
 *           - GB: gigabytes
 *         Examples: "2 MB", "2048 B", "1.5 GB"
 *   - shares: (boolean) Whether to enable share support for this backend.
 *             This allows flexible file sharing independent from the
 *             permission support in the storage backend. For sharing to work
 *             properly, you need a backend type that does not implicitly
 *             enforce user permissions, and individual home directories for
 *             each user.
 *   - root: (string) The directory that will be the "top" or "root" directory,
 *           being the topmost directory where users can change to. This is in
 *           addition to any 'vfsroot' parameter set in the params array.
 *
 * *** The following options should NOT be set unless you REALLY know what ***
 * *** you are doing! FOR MOST PEOPLE, AUTO-DETECTION OF THESE PARAMETERS  ***
 * *** (the default if the parameters are not set) SHOULD BE USED!         ***
 *
 *   - preferred: (string or array) Useful if you want to use the same
 *                backends.php file for different machines. If the hostname of
 *                the Gollem machine is identical to one of those in the
 *                preferred list, then that entry will be selected by default
 *                on the login screen. Otherwise the first entry in the list
 *                is selected.
 *
 * $Id: ab0ea802731049d75e59e3e05c02e6acd838f04f $
 */

// FTP Example.
$backends['ftp'] = array(
    // ENABLED by default
    'disabled' => false,
    'name' => 'FTP Server',
    'driver' => 'ftp',
    'hordeauth' => false,
    'params' => array(
        // The hostname/IP Address of the FTP server
        'hostspec' => 'localhost',
        // The port number of the FTP server
        'port' => 21,
        // Use passive mode?
        'pasv' => false,
        // The return formatting from the 'ls' command. Possible Values: 'aix',
        // 'standard'.
        // 'lsformat' => 'standard',
        // If true and the POSIX extension is available the driver will map
        // the user and group IDs returned from the FTP server with the local
        // IDs from the local password file.  This is useful only if the FTP
        // server is running on localhost or if the local user/group
        // IDs are identical to the remote FTP server.
        // 'maplocalids' => true,
        // The default permissions to set for newly created folders and files.
        // 'permissions' => '750',
        // If true, and PHP had been compiled with OpenSSL support, TLS
        // transport-level encryption will be negotiated with the server.
        // 'ssl' => false,
        // Set timeout (in seconds) for the FTP server. Default: 90 seconds
        // 'timeout' => 90,
        // The type of the remote FTP server. Possible values: 'unix', 'win',
        // 'netware'. By default, we attempt to auto-detect type.
        // 'type' => 'unix',
    ),
    'loginparams' => array(
        // Allow the user to change the FTP server
        // 'hostspec' => 'Hostname',
        // Allow the user to change the FTP port
        // 'port' => 'Port'
    ),
    // 'root' => '',
    // 'home' => '',
    // 'createhome' => false,
    // 'filter' => '^regex$',
    // 'quota' => false,
    'attributes' => array(
        'type',
        'name',
        'edit',
        'download',
        'modified',
        'size',
        'permission',
        'owner',
        'group'
    )
);

// This backend uses Horde credentials to automatically log in.
$backends['hordeftp'] = array(
    // Disabled by default
    'disabled' => true,
    'name' => 'FTP Server',
    'driver' => 'ftp',
    'hordeauth' => true,
    'params' => array(
        // The hostname/IP Address of the FTP server.
        'hostspec' => 'localhost',
        // The port number of the FTP server.
        'port' => 21,
        // Use passive mode?
        'pasv' => false,
        // The return formatting from the 'ls' command. Possible Values: 'aix',
        // 'standard'.
        // 'lsformat' => 'standard',
        // If true and the POSIX extension is available the driver will map
        // the user and group IDs returned from the FTP server with the local
        // IDs from the local password file.  This is useful only if the FTP
        // server is running on localhost or if the local user/group
        // IDs are identical to the remote FTP server.
        // 'maplocalids' => true,
        // The default permissions to set for newly created folders and files.
        // 'permissions' => '750',
        // If true, and PHP had been compiled with OpenSSL support, TLS
        // transport-level encryption will be negotiated with the server.
        // 'ssl' => false,
        // Set timeout (in seconds) for the FTP server. Default: 90 seconds
        // 'timeout' => 90,
        // The type of the remote FTP server. Possible values: 'unix', 'win',
        // 'netware'. By default, we attempt to auto-detect type.
        // 'type' => 'unix',
    ),
    'loginparams' => array(
        // Allow the user to change the FTP server.
        // 'hostspec' => 'Hostname',
        // Allow the user to change the FTP port.
        // 'port' => 'Port'
    ),
    // 'root' => '',
    // 'home' => '',
    // 'createhome' => false,
    // 'filter' => '^regex$',
    // 'quota' => false,
    'attributes' => array(
        'type',
        'name',
        'edit',
        'download',
        'modified',
        'size',
        'permission',
        'owner',
        'group'
    )
);

// SQL Example.
$backends['sql'] = array(
    // Disabled by default
    'disabled' => true,
    'name' => 'SQL Server',
    'driver' => 'sql',
    'hordeauth' => true,

    // The default connection details are pulled from the Horde-wide SQL
    // connection configuration.
    'params' => array_merge($GLOBALS['conf']['sql'],
                            array('table' => 'horde_vfs')),

    // If you need different connection details than from the Horde-wide SQL
    // connection configuration, uncomment and set the following lines.
    // 'params' => array(
    //     // The SQL connection parameters. See horde/config/conf.php for
    //     // descriptions of each parameter.
    //     'phptype' => 'mysql',
    //     'hostspec' => 'localhost',
    //     'database' => 'horde',
    //     'username' => 'horde',
    //     'password' => 'horde',
    //
    //     // The SQL table containing the VFS. See the horde/scripts/db
    //     // directory for examples.
    //     'table' => 'horde_vfs'
    // ),
    'loginparams' => array(),
    // 'root' => '',
    // 'home' => '',
    // 'createhome' => false,
    // 'filter' => '^regex$',
    // 'quota' => false,
    'attributes' => array(
        'type',
        'name',
        'edit',
        'download',
        'modified',
        'size'
    )
);

// This backend specifies a home directory and root directory in a SQL vfs.
$backends['sqlhome'] = array(
    // Disabled by default
    'disabled' => true,
    'name' => 'SQL Server with home',
    'driver' => 'sql',
    'hordeauth' => true,

    // The default connection details are pulled from the Horde-wide SQL
    // connection configuration.
    'params' => array_merge($GLOBALS['conf']['sql'],
                            array('table' => 'horde_vfs')),

    // If you need different connection details than from the Horde-wide SQL
    // connection configuration, uncomment and set the following lines.
    // 'params' => array(
    //     // The SQL connection parameters. See horde/config/conf.php for
    //     // descriptions of each parameter.
    //     'phptype' => 'mysql',
    //     'hostspec' => 'localhost',
    //     'database' => 'horde',
    //     'username' => 'horde',
    //     'password' => 'horde',
    //
    //     // The SQL table containing the VFS. See the horde/scripts/db
    //     // directory for examples.
    //     'table' => 'horde_vfs'
    // ),
    'loginparams' => array(),
    'root' => '/home',
    'home' => '/home/' . $GLOBALS['registry']->getAuth(),
    // 'createhome' => false,
    // 'filter' => '^regex$',
    // 'quota' => false,
    'shares' => true,
    'attributes' => array(
        'type',
        'name',
        'share',
        'edit',
        'download',
        'modified',
        'size',
        'owner',
    )
);

// NOTE: /exampledir/home and all subdirectories should be, for
// security reasons, owned by your web server user and mode 700 or you
// will need to use suexec or something else that can adjust the web
// server effective uid.
$backends['file'] = array(
    // Disabled by default
    'disabled' => true,
    'name' => 'Virtual Home Directories',
    'driver' => 'file',
    'hordeauth' => true,
    'params' => array(
        // The base location under which the user home directories live.
        'vfsroot' => '/exampledir/home/',
        // The default permissions to set for newly created folders and files.
        // 'permissions' => '750'
    ),
    'loginparams' => array(),
    'root' => '/',
    'home' => $GLOBALS['registry']->getAuth(),
    // 'createhome' => false,
    // 'filter' => '^regex$',
    // 'quota' => false,
    'shares' => true,
    'attributes' => array(
        'type',
        'name',
        'share',
        'edit',
        'download',
        'modified',
        'size',
        'permission',
        'owner',
        'group'
    )
);

// SMB Example
$backends['smb'] = array(
    // Disabled by default
    'disabled' => true,
    'name' => 'SMB Server',
    'driver' => 'smb',
    'hordeauth' => false,
    'params' => array(
        'hostspec' => 'example',
        'port' => 139,
        'share' => 'homes',
        // Path to the smbclient executable.
        'smbclient' => '/usr/bin/smbclient',
        // IP address of server (only needed if hostname is different from
        // NetBIOS name).
        // 'ipaddress' => '127.0.0.1',
        // The default permissions to set for newly created folders and
        // files.
        // 'permissions' => '750'
    ),
    'loginparams' => array(
        // Allow the user to change to Samba server.
        // 'hostspec' => 'Hostname',
        // Allow the user to change the Samba port.
        // 'port' => 'Port',
        // Allow the user to change the Samba share.
        // 'share' => 'Share',
    ),
    // 'root' => '',
    // 'home' => '',
    // 'createhome' => false,
    // 'filter' => '^regex$',
    // 'quota' => false,
    'attributes' => array(
        'type',
        'name',
        'edit',
        'download',
        'modified',
        'size'
    )
);

// SSH2 Example
$backends['ssh2'] = array(
    // Disabled by default
    'disabled' => true,
    'name' => 'SSH2 Server',
    'driver' => 'ssh2',
    'hordeauth' => false,
    'params' => array(
        // The hostname/IP Address of the SSH server
        'hostspec' => 'ssh2.example.com',
        // The port number of the SSH server
        'port' => 22,
        // Set timeout (in seconds) for the SSH server. Default: 90 seconds
        // 'timeout' => 90,
        // If true and the POSIX extension is available the driver will map
        // the user and group IDs returned from the SSH server with the local
        // IDs from the local password file.  This is useful only if the SSH
        // server is running on localhost or if the local user/group
        // IDs are identical to the remote SSH server.
        // 'maplocalids' => true,
        // The default permissions to set for newly created folders and
        // files.
        // 'permissions' => '750'
    ),
    'loginparams' => array(
        // Allow the user to change the SSH server
        // 'hostspec' => 'Hostname',
        // Allow the user to change the SSH port
        // 'port' => 'Port'
    ),
    // 'root' => '',
    // 'home' => '',
    // 'createhome' => false,
    // 'filter' => '^regex$',
    // 'quota' => false,
    'attributes' => array(
        'type',
        'name',
        'edit',
        'download',
        'modified',
        'size',
        'permission',
        'owner',
        'group'
    )
);
