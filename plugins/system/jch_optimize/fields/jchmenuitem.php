<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for
 * optmized downloads
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license GNU/GPLv3, See LICENSE file
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die;

use JchOptimize\Platform\Utility;
use JchOptimize\Core\FileRetriever;

include_once dirname(dirname(__FILE__)) . '/jchoptimize/loader.php';

/**
 * 
 */
class JFormFieldJchmenuitem extends JFormFieldMenuitem
{

        public $type = 'jchmenuitem';

        /**
         * 
         * @param SimpleXMLElement $element
         * @param type $value
         * @param type $group
         * @return boolean
         */
        public function setup(SimpleXMLElement $element, $value, $group = NULL)
        {
                //$this->loadResources();

                if (!$value)
                {
                        $value = $this->getHomePageLink();
                }

                try
                {
                        $this->checkPcreVersion();
                        $oFileRetriever = FileRetriever::getInstance();
                }
                catch (Exception $ex)
                {
                        $GLOBALS['bTextArea'] = TRUE;

                        JFactory::getApplication()->enqueueMessage($ex->getMessage(), 'warning');

                        return FALSE;
                }

                if (!$oFileRetriever->isHttpAdapterAvailable())
                {
                        return FALSE;
                }

                return parent::setup($element, $value, $group);
        }

        /**
         * 
         * @throws Exception
         */
        protected function checkPcreVersion()
        {
                $pcre_version = preg_replace('#(^\d++\.\d++).++$#', '$1', PCRE_VERSION);

                if (version_compare($pcre_version, '7.2', '<'))
                {
                        throw new Exception('This plugin requires PCRE Version 7.2 or higher to run. Your installed version is ' . PCRE_VERSION);
                }
        }

        /**
         * 
         * @return type
         */
        public static function getHomePageLink()
        {
                $oMenu            = JFactory::getApplication()->getMenu('site');
                $oDefaultMenuItem = $oMenu->getDefault();

                return $oDefaultMenuItem->id;
        }

        /**
         * 
         */
        protected function loadResources()
        {
		if (!defined('JCH_VERSION'))
		{
			define('JCH_VERSION', '5.0.5.22');
		}

                JHtml::script('jui/jquery.min.js', FALSE, TRUE);

                $oDocument = JFactory::getDocument();
                $sScript   = '';

                $oDocument->addStyleSheetVersion(JUri::root(true) . '/media/plg_jchoptimize/css/admin.css', JCH_VERSION);
                $oDocument->addScriptVersion(JUri::root(true) . '/media/plg_jchoptimize/js/admin-joomla.js', JCH_VERSION);
                $oDocument->addScriptVersion(JUri::root(true) . '/media/plg_jchoptimize/js/admin-utility.js', JCH_VERSION);

                $uri         = clone JUri::getInstance();
                $domain      = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port')) . Helper::getBaseFolder();
                $plugin_path = 'plugins/system/jch_optimize/';

                $ajax_url = JURI::getInstance()->toString() . '&jchajax=1';

                $sScript .= <<<JCHSCRIPT
function submitJchSettings(){
        Joomla.submitbutton('plugin.apply');
}                        
jQuery(document).ready(function() {
    jQuery('.collapsible').collapsible();
  });
                        
var jch_form_id = 'jform_params'; 
var jch_observers = [];        
var jch_ajax_url = '$ajax_url';
JCHSCRIPT;

                $oDocument->addScriptDeclaration($sScript);
                $oDocument->addStyleSheet('//netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.css');
                JHtml::script('plg_jchoptimize/jquery.collapsible.js', FALSE, TRUE);

                
        }

}
