<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die;

/**
 * This class extends JModel about some general methods used in all models of Falang
 * @package		Falang
 * @subpackage	JFModel
 */
class JFModel extends ListModel {

    /**
     * returns the default language of the frontend
     * @return object	instance of the default language
     */
    function getDefaultLanguage() {
        $params = ComponentHelper::getParams('com_languages');
        return $params->get("site", 'en-GB');
    }
}

