<?php

require_once 'Horde/Util.php';

/**
 * The Horde_Mime_Magic:: class provides an interface to determine a MIME type
 * for various content, if it provided with different levels of information.
 *
 * Copyright 1999-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Anil Madhavapeddy <anil@recoil.org>
 * @author  Michael Slusarz <slusarz@horde.org>
 * @package Horde_Mime
 */
class Horde_Mime_Magic
{
    /**
     * The MIME extension map.
     *
     * @var array
     */
    static protected $_map = null;

    /**
     * The MIME magic file.
     *
     * @var array
     */
    static protected $_magic = null;

    /**
     * Returns a copy of the MIME extension map.
     *
     * @return array  The MIME extension map.
     */
    static protected function _getMimeExtensionMap()
    {
        if (is_null(self::$_map)) {
            require dirname(__FILE__) . '/mime.mapping.php';
            self::$_map = $mime_extension_map;
        }

        return self::$_map;
    }

    /**
     * Attempt to convert a file extension to a MIME type, based
     * on the global Horde and application specific config files.
     *
     * If we cannot map the file extension to a specific type, then
     * we fall back to a custom MIME handler 'x-extension/$ext', which
     * can be used as a normal MIME type internally throughout Horde.
     *
     * @param string $ext  The file extension to be mapped to a MIME type.
     *
     * @return string  The MIME type of the file extension.
     */
    static public function extToMime($ext)
    {
        if (empty($ext)) {
           return 'application/octet-stream';
        }

        $ext = String::lower($ext);
        $map = self::_getMimeExtensionMap();
        $pos = 0;

        while (!isset($map[$ext])) {
            if (($pos = strpos($ext, '.')) === false) {
                break;
            }
            $ext = substr($ext, $pos + 1);
        }

        return isset($map[$ext])
            ? $map[$ext]
            : 'x-extension/' . $ext;
    }

    /**
     * Attempt to convert a filename to a MIME type, based on the global Horde
     * and application specific config files.
     *
     * @param string $filename  The filename to be mapped to a MIME type.
     * @param boolean $unknown  How should unknown extensions be handled? If
     *                          true, will return 'x-extension/*' types.  If
     *                          false, will return 'application/octet-stream'.
     *
     * @return string  The MIME type of the filename.
     */
    static public function filenameToMime($filename, $unknown = true)
    {
        $pos = strlen($filename) + 1;
        $type = '';

        $map = self::_getMimeExtensionMap();
        for ($i = 0; $i <= $map['__MAXPERIOD__']; ++$i) {
            $pos = strrpos(substr($filename, 0, $pos - 1), '.');
            if ($pos === false) {
                break;
            }
            ++$pos;
        }

        $type = ($pos === false) ? '' : self::extToMime(substr($filename, $pos));

        return (empty($type) || (!$unknown && (strpos($type, 'x-extension') !== false)))
            ? 'application/octet-stream'
            : $type;
    }

    /**
     * Attempt to convert a MIME type to a file extension, based
     * on the global Horde and application specific config files.
     *
     * If we cannot map the type to a file extension, we return false.
     *
     * @param string $type  The MIME type to be mapped to a file extension.
     *
     * @return string  The file extension of the MIME type.
     */
    static public function mimeToExt($type)
    {
        if (empty($type)) {
            return false;
        }

        if (($key = array_search($type, self::_getMimeExtensionMap())) === false) {
            list($major, $minor) = explode('/', $type);
            if ($major == 'x-extension') {
                return $minor;
            }
            if (strpos($minor, 'x-') === 0) {
                return substr($minor, 2);
            }
            return false;
        }

        return $key;
    }

    /**
     * Uses variants of the UNIX "file" command to attempt to determine the
     * MIME type of an unknown file.
     *
     * @param string $path      The path to the file to analyze.
     * @param string $magic_db  Path to the mime magic database.
     *
     * @return string  The MIME type of the file.  Returns false if the file
     *                 type isn't recognized or an error happened.
     */
    static public function analyzeFile($path, $magic_db = null)
    {
        /* If the PHP Mimetype extension is available, use that. */
        if (Util::extensionExists('fileinfo')) {
            $res = empty($magic_db)
                ? @finfo_open(FILEINFO_MIME)
                : @finfo_open(FILEINFO_MIME, $magic_db);

            if ($res) {
                $type = finfo_file($res, $path);
                finfo_close($res);

                /* Remove any additional information. */
                foreach (array(';', ',', '\\0') as $separator) {
                    if (($pos = strpos($type, $separator)) !== false) {
                        $type = rtrim(substr($type, 0, $pos));
                    }
                }

                if (preg_match('|^[a-z0-9]+/[.-a-z0-9]+$|i', $type)) {
                    return $type;
                }
            }
        }

        if (Util::extensionExists('mime_magic')) {
            return trim(mime_content_type($path));
        }

        return false;
    }

    /**
     * Uses variants of the UNIX "file" command to attempt to determine the
     * MIME type of an unknown byte stream.
     *
     * @param string $data      The file data to analyze.
     * @param string $magic_db  Path to the mime magic database.
     *
     * @return string  The MIME type of the file.  Returns false if the file
     *                 type isn't recognized or an error happened.
     */
    static public function analyzeData($data, $magic_db = null)
    {
        /* If the PHP Mimetype extension is available, use that. */
        if (Util::extensionExists('fileinfo')) {
            $res = empty($magic_db)
                ? @finfo_open(FILEINFO_MIME)
                : @finfo_open(FILEINFO_MIME, $magic_db);

            if (!$res) {
                return false;
            }

            $type = finfo_buffer($res, $data);
            finfo_close($res);

            /* Remove any additional information. */
            if (($pos = strpos($type, ';')) !== false) {
                $type = rtrim(substr($type, 0, $pos));
            }

            if (($pos = strpos($type, ',')) !== false) {
                $type = rtrim(substr($type, 0, $pos));
            }

            return $type;
        }

        return false;
    }
}
