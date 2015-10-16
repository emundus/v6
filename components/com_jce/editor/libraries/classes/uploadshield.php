<?php

/**
 * @package   	JCE
 * @copyright 	Copyright (c) 2009-2015 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined('_JEXEC') or die('RESTRICTED');

abstract class WFUploadShield {

    /**
     * Checks an uploaded for suspicious naming and potential PHP contents which could indicate a hacking attempt.
     *
     * The options you can define are:
     * null_byte                   Prevent files with a null byte in their name (buffer overflow attack)
     * forbidden_extensions        Do not allow these strings anywhere in the file's extension
     * php_tag_in_content          Do not allow <?php tag in content
     * shorttag_in_content         Do not allow short tag <? in content
     * shorttag_extensions         Which file extensions to scan for short tags in content
     * fobidden_ext_in_content     Do not allow forbidden_extensions anywhere in content
     * php_ext_content_extensions  Which file extensions to scan for .php in content
     *
     * This code is an adaptation and improvement of Admin Tools' UploadShield feature,
     * relicensed and contributed by its author.
     *
     * @param   array  $file     An uploaded file descriptor
     * @param   array  $options  The scanner options (see the code for details)
     *
     * @return  boolean  True of the file is safe
     *
     * https://github.com/joomla/joomla-cms/blob/staging/libraries/joomla/filter/input.php
     * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
     */
    public static function isSafeFile($file, $options = array()) {
        $defaultOptions = array(
            // Null byte in file name
            'null_byte' => true,
            // Forbidden string in extension (e.g. php matched .php, .xxx.php, .php.xxx and so on)
            'forbidden_extensions' => array(
                'php', 'phps', 'php5', 'php3', 'php4', 'inc', 'pl', 'cgi', 'fcgi', 'java', 'jar', 'py'
            ),
            // <?php tag in file contents
            'php_tag_in_content' => true,
            // <? tag in file contents
            'shorttag_in_content' => true,
            // Which file extensions to scan for short tags
            'shorttag_extensions' => array(
                'inc', 'phps', 'class', 'php3', 'php4', 'php5', 'txt', 'dat', 'tpl', 'tmpl'
            ),
            // Forbidden extensions anywhere in the content
            'fobidden_ext_in_content' => true,
            // Which file extensions to scan for .php in the content
            'php_ext_content_extensions' => array('zip', 'rar', 'tar', 'gz', 'tgz', 'bz2', 'tbz', 'jpa'),
        );

        $options = array_merge($defaultOptions, $options);

        // Make sure we can scan nested file descriptors
        $descriptors = $file;

        if (isset($file['name']) && isset($file['tmp_name'])) {
            $descriptors = self::decodeFileData(
                            array(
                                $file['name'],
                                $file['type'],
                                $file['tmp_name'],
                                $file['error'],
                                $file['size']
                            )
            );
        }

        // Handle non-nested descriptors (single files)
        if (isset($descriptors['name'])) {
            $descriptors = array($descriptors);
        }

        // Scan all descriptors detected
        foreach ($descriptors as $fileDescriptor) {
            if (!isset($fileDescriptor['name'])) {
                // This is a nested descriptor. We have to recurse.
                if (!self::isSafeFile($fileDescriptor, $options)) {
                    return false;
                }

                continue;
            }

            $tempNames = $fileDescriptor['tmp_name'];
            $intendedNames = $fileDescriptor['name'];

            if (!is_array($tempNames)) {
                $tempNames = array($tempNames);
            }

            if (!is_array($intendedNames)) {
                $intendedNames = array($intendedNames);
            }

            $len = count($tempNames);

            for ($i = 0; $i < $len; $i++) {
                $tempName = array_shift($tempNames);
                $intendedName = array_shift($intendedNames);

                // 1. Null byte check
                if ($options['null_byte']) {
                    if (strstr($intendedName, "\x00")) {
                        return false;
                    }
                }

                // 2. PHP-in-extension check (.php, .php.xxx[.yyy[.zzz[...]]], .xxx[.yyy[.zzz[...]]].php)
                if (!empty($options['forbidden_extensions'])) {
                    $explodedName = explode('.', $intendedName);
                    $explodedName = array_reverse($explodedName);
                    array_pop($explodedName);
                    array_map('strtolower', $explodedName);

                    /*
                     * DO NOT USE array_intersect HERE! array_intersect expects the two arrays to
                     * be set, i.e. they should have unique values.
                     */
                    foreach ($options['forbidden_extensions'] as $ext) {
                        if (in_array($ext, $explodedName)) {
                            return false;
                        }
                    }
                }

                // 3. File contents scanner (PHP tag in file contents)
                if ($options['php_tag_in_content'] || $options['shorttag_in_content'] || ($options['fobidden_ext_in_content'] && !empty($options['forbidden_extensions']))) {
                    $fp = @fopen($tempName, 'r');

                    if ($fp !== false) {
                        $data = '';

                        while (!feof($fp)) {
                            $buffer = @fread($fp, 131072);
                            $data .= $buffer;

                            if ($options['php_tag_in_content'] && strstr($buffer, '<?php')) {
                                return false;
                            }

                            if ($options['shorttag_in_content']) {
                                $suspiciousExtensions = $options['shorttag_extensions'];

                                if (empty($suspiciousExtensions)) {
                                    $suspiciousExtensions = array(
                                        'inc', 'phps', 'class', 'php3', 'php4', 'txt', 'dat', 'tpl', 'tmpl'
                                    );
                                }

                                /*
                                 * DO NOT USE array_intersect HERE! array_intersect expects the two arrays to
                                 * be set, i.e. they should have unique values.
                                 */
                                $collide = false;

                                foreach ($suspiciousExtensions as $ext) {
                                    if (in_array($ext, $explodedName)) {
                                        $collide = true;

                                        break;
                                    }
                                }

                                if ($collide) {
                                    // These are suspicious text files which may have the short tag (<?) in them
                                    if (strstr($buffer, '<?')) {
                                        return false;
                                    }
                                }
                            }

                            if ($options['fobidden_ext_in_content'] && !empty($options['forbidden_extensions'])) {
                                $suspiciousExtensions = $options['php_ext_content_extensions'];

                                if (empty($suspiciousExtensions)) {
                                    $suspiciousExtensions = array(
                                        'zip', 'rar', 'tar', 'gz', 'tgz', 'bz2', 'tbz', 'jpa'
                                    );
                                }

                                /*
                                 * DO NOT USE array_intersect HERE! array_intersect expects the two arrays to
                                 * be set, i.e. they should have unique values.
                                 */
                                $collide = false;

                                foreach ($suspiciousExtensions as $ext) {
                                    if (in_array($ext, $explodedName)) {
                                        $collide = true;

                                        break;
                                    }
                                }

                                if ($collide) {
                                    /*
                                     * These are suspicious text files which may have an executable
                                     * file extension in them
                                     */
                                    foreach ($options['forbidden_extensions'] as $ext) {
                                        if (strstr($buffer, '.' . $ext)) {
                                            return false;
                                        }
                                    }
                                }
                            }

                            /*
                             * This makes sure that we don't accidentally skip a <?php tag if it's across
                             * a read boundary, even on multibyte strings
                             */
                            $data = substr($data, -8);
                        }

                        fclose($fp);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Method to decode a file data array.
     *
     * @param   array  $data  The data array to decode.
     *
     * @return  array
     *
     * https://github.com/joomla/joomla-cms/blob/staging/libraries/joomla/filter/input.php
     * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
     */
    protected static function decodeFileData(array $data) {
        $result = array();

        if (is_array($data[0])) {
            foreach ($data[0] as $k => $v) {
                $result[$k] = self::decodeFileData(array($data[0][$k], $data[1][$k], $data[2][$k], $data[3][$k], $data[4][$k]));
            }

            return $result;
        }

        return array('name' => $data[0], 'type' => $data[1], 'tmp_name' => $data[2], 'error' => $data[3], 'size' => $data[4]);
    }
}