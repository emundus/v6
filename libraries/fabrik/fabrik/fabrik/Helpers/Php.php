<?php
/**
 * @package         Regular Labs Library
 * @version         22.10.1331
 *
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * This is a derivative of the Php class from Peter van Weston at Regular Labs,
 * it is highly modified for Fabrik's needs but the underlying
 * architecture is copyright Peter van Westen. The following applies to
 * the derivations.
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

namespace Fabrik\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Filesystem\File;

class Php
{

    public static function Eval($params = [])
    {
        if (empty($params)) {
            return null;
        }

       
        if (array_key_exists('code', $params) === false) {
            /* we must have some code to deal with */
            return null;
        }
        
        $params['className'] = self::getClassName($params);
        if (empty($params['className'])) {
            // Something went wrong!
            return true;
        }
        
        $result = null;
        ob_start();
        $newClass = new $params['className'];
        $result = $newClass->doExecute($params['vars'] ?? [], $params['thisVars'] ?? []);
        $output = ob_get_contents();
        ob_end_clean();
        
        if (is_null($result) === false) {
            return $result;
        }
        
        return $output;
    }
    
    
    private static function createFunctionInMemory($string) 
    {
        eval($string);        
    }
    
    private static function generateClassContents($params)
     {
        $content = [];
        /* Process the passed in variables */
        $init_variables = [];
        $thisVars = $params['thisVars'] ?? [];
        $vars = $params['vars'] ?? [];

        $privateThisVars = [];
        foreach(array_keys($thisVars) as $thisVarName) {
            $privateThisVars[] = 'private $'.$thisVarName.';';
        }
        $initBasicVars = [];
        foreach(array_keys($vars) as $varName) {
            $initBasicVars[] = 'private $'.$varName.';';
        }

        $useLines = [];

        /* Check for complicated code structure */
        $codeTypes = ['preCode' => [], 'postCode' => []];
        if (is_array($params['code']) === false) $params['code'] = ['preCode' => $params['code']];
        /* Split the code into seperate lines */
        foreach (array_keys($codeTypes) as $codeType) {
            if (empty($params['code'][$codeType])) continue;
            $codeTypes[$codeType] = array_map('trim', preg_split('/\r\n|\r|\n/', $params['code'][$codeType]));
            /* Capture any use statements */
            foreach ($codeTypes[$codeType] as $idx => $codeLine) {
                if (strpos($codeLine, 'use ') === 0) {
                    /* Found one, while we are at it remove any double backslashes, these were recommended early on with F4 */
                    $useLines[] = str_replace('\\\\', '\\', $codeLine);
                    unset($codeTypes[$codeType][$idx]);
                } 
            }
        }

        /* Opening stuff  */
        $content[] = 'defined(\'_JEXEC\') or die;';

        /* the use lines from the original source */  
        $content = array_merge($content, $useLines);

        /* Define the class */
        $content[] = 'class '.$params['className'].'{';

         /* Our new function */
        $content[] = 'function doExecute($vars, $thisVars) {'; 

        /* Insert any $thisVars setup */    
        if (count($thisVars)) {
            $content = array_merge($content, [
                'foreach ($thisVars as $thisVarKey => &$thisVarValue) {',
                '   $this->{$thisVarKey} = &$thisVarValue;',
                '};'
            ]);
        } 

        /* Insert any regular var setup */    
        if (count($vars)) {
            $content = array_merge($content, [
                'foreach ($vars as $varKey => &$varValue) {',
                '   ${$varKey} = &$varValue;',
                '};'
            ]);
        }

        /* Now the actual code */
        $content = array_merge($content, $codeTypes['preCode']);
        if (!empty($params['code']['file'])) {
            $content[] = 'require_once("'.$params['code']['file'].'");';
        }
        $content = array_merge($content, $codeTypes['postCode']);
        /* In case the code left us out of php mode */
        $content[] = '?><?php';    
        
        /* Close the doExecute function */                                                            
        $content[] = '}';  
        /* And close off the class */
        $content[] = "};";                                                 

        $content = implode(PHP_EOL, $content);
        
        // Remove Zero Width spaces / (non-)joiners
        $content = str_replace(
            [
                "\xE2\x80\x8B",
                "\xE2\x80\x8C",
                "\xE2\x80\x8D",
            ],
            '',
            $content
        );
        
        return $content;
    }
    
    private static function getClassName(&$params) 
    {
        $code = '';
        if (is_array($params['code'])) {
            foreach ($params['code'] as $codeKey => $codePart) {
                $code .= $params['code'][$codeKey] ?? '';
            }
            $md5 = md5($code);
        } else {
            $md5 = md5($params['code']);
        }
        $params['className'] = 'FabrikEvalClass_' . $md5;
        
        if (class_exists($params['className'])) {
            return $params['className'];
        }

        $contents = self::generateClassContents($params);
        self::createFunctionInMemory($contents);
        
        if (!class_exists($params['className'])) {
            // Something went wrong!
            return false;
        }
        
        return $params['className'];
    }
}
