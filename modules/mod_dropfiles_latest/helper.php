<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') || die;

/**
 * Class ModDropfilesLatestHelper
 */
class ModDropfilesLatestHelper
{

    /**
     * Load resource
     *
     * @return void
     */
    public function loadResource()
    {
        //language
        JLoader::register('DropfilesBase', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php');
        JLoader::register('DropfilesFilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/files.php');
        DropfilesBase::loadLanguage();

        JHtml::_('jquery.framework');
        JHtml::_('formbehavior.chosen', '.chzn-select');
        $doc = JFactory::getDocument();
        $jquery_custom = JUri::base() . 'components/com_dropfiles/assets/css/ui-lightness';
        $jquery_custom .= '/jquery-ui-1.9.2.custom.min.css';
        $doc->addStyleSheet($jquery_custom);
        $doc->addStyleSheet(JUri::base() . 'components/com_dropfiles/assets/css/jquery.tagit.css');
        $doc->addStyleSheet(JUri::base() . 'components/com_dropfiles/assets/css/dropfiles-latest.css');
        $doc->addStyleSheet(JUri::base() . 'components/com_dropfiles/assets/css/material-design-iconic-font.min.css');
        $doc->addStyleSheet(JUri::base() . 'components/com_dropfiles/assets/css/front_ver5.4.css');


        $doc->addScript(JUri::base() . 'components/com_dropfiles/assets/js/jquery-ui-1.9.2.custom.min.js');
        $doc->addScript(JUri::base() . 'components/com_dropfiles/assets/js/jquery.tagit.js');

        $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/jquery.colorbox-min.js');
        $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/colorbox.init.js');
        $doc->addStyleSheet(JURI::base('true') . '/components/com_dropfiles/assets/css/colorbox.css');
        $doc->addScriptDeclaration('dropfilesBaseUrl="' . JURI::base() . '";');
    }
}
