<?php
/**
 * @package   gantry
 * @subpackage core
 * @version   4.1.31 April 11, 2016
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

jimport('joomla.application.component.controller');

// Import file dependencies
//require_once (JPATH_ADMINISTRATOR.'/'.'components'.'/'.'com_templates'.'/'.'helpers'.'/'.'template.php');

/**
 * @package        Joomla
 * @subpackage    RokGantry
 */
class GantryController extends GantryLegacyJController
{
    /**
     * @var        string    The default view.
     * @since    1.6
     */
    protected $default_view = 'template';

    public function ajax()
    {
        /** @var $gantry Gantry */
		global $gantry;


        // comment out the following 2 lines for debugging
        //$request = @$_SERVER['HTTP_X_REQUESTED_WITH'];
        //if ((!isset($request) || strtolower($request) != 'xmlhttprequest') && (isset($modelname) && $modelname != "diagnostics")) die("Direct access not allowed.");

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

        $model = $gantry->getAjaxModel(JFactory::getApplication()->input->get('model','','string'), true);
        if ($model === false) die();
        include_once($model);

        /*
            - USAGE EXAMPLE -

            new Ajax({
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
    }
}
