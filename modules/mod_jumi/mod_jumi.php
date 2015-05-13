<?php
/**
* @version   $Id$
* @package   Jumi
* @copyright (C) 2008 - 2010 Martin Hajek, 2011 Edvard Ananyan
* @license   GNU/GPL v3 http://www.gnu.org/licenses/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}
//
if ($params->def('prepare_content', 1))
{
	JPluginHelper::importPlugin('content');
	$module->content = JHtml::_('content.prepare', $module->content);
}
//
require_once(dirname(__FILE__).DS.'helper.php');

$code_written   = modJumiHelper::getCodeWritten($params); //code written or ""
$storage_source = modJumiHelper::getStorageSource($params); //filepathname or record id or ""

if(is_int($storage_source)) { //it is record id
    $code_stored = modJumiHelper::getCodeStored($storage_source); //code or null(error]
}

require(JModuleHelper::getLayoutPath('mod_jumi'));