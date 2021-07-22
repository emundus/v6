<?php
/**
 * @package   gantry
 * @subpackage core
 * @version   4.1.43 April  1, 2020
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2020 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

jimport('joomla.application.component.controller');


/**
 * @package        Joomla
 * @subpackage    RokGantry
 */
class GantryController extends GantryLegacyJController
{
    public function ajax()
    {
        /** @var $gantry Gantry */
		global $gantry;

        // load and inititialize gantry class
        $gantry_path = JPATH_SITE . '/libraries/gantry/gantry.php';
        if (file_exists($gantry_path))
        {
            require_once($gantry_path);
        }
        else
        {
            echo "error " . JText::_('Unable to find Gantry library.  Please make sure you have it installed.');
            die;
        }

        $model = $gantry->getAjaxModel(JFactory::getApplication()->input->getString('model'),false);
        if ($model === false) die();
        include_once($model);

        /*
            - USAGE EXAMPLE -

            new Request({
				url: 'http://url/template/administrator/index.php?option=com_admin&tmpl=gantry-ajax-admin',
                onSuccess: function(response) {console.log(response);}
            }).request({
                'model': 'example', // <- mandatory, see "ajax-models" folder
                'template': 'template_folder', // <- mandatory, the name of the gantry template folder (rt_dominion_j15)
                'example': 'example1', // <-- from here are all custom query posts you can use
                'name': 'w00fz',
                'message': 'Hello World!'
            });
        */

        // Clear the cache gantry cache after each call
        $cache = GantryCache::getInstance();
        $cache->clearGroupCache();
    }
}
