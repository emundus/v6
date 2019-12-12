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
 * Class ModDropfilesSearchHelper
 */
class ModDropfilesSearchHelper
{
    /**
     * Load resource
     *
     * @return void
     * @since  version
     */
    public function loadResource()
    {
        //language
        JLoader::register('DropfilesBase', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php');
        DropfilesBase::loadLanguage();

        JHtml::_('jquery.framework');
        JHtml::_('formbehavior.chosen', '.chzn-select');
        $doc = JFactory::getDocument();
        $doc->addStyleSheet(JUri::base() . 'components/com_dropfiles/assets/css/ui-lightness/jquery-ui-1.9.2.custom.min.css');
        $doc->addStyleSheet(JUri::base() . 'components/com_dropfiles/assets/css/jquery.tagit.css');
        $doc->addStyleSheet(JUri::base() . 'components/com_dropfiles/assets/css/jquery.datetimepicker.css');
        $doc->addStyleSheet(JURI::root() . 'components/com_dropfiles/assets/css/search_filter.css');

        $doc->addScript(JUri::base() . 'components/com_dropfiles/assets/js/jquery-ui-1.9.2.custom.min.js');
        $doc->addScript(JUri::base() . 'components/com_dropfiles/assets/js/jquery.tagit.js');
        $doc->addScript(JUri::base() . 'components/com_dropfiles/assets/js/jquery.datetimepicker.js');
        $doc->addScript(JURI::root() . 'components/com_dropfiles/assets/js/search_filter.js');
    }

    /**
     * Get inputs form
     *
     * @return array
     */
    public function getInputs()
    {
        $filters = array();
        $app = JFactory::getApplication();
        $q = $app->input->getString('q', null);
        if (!empty($q)) {
            $filters['q'] = $q;
        }
        $catid = $app->input->getUInt('catid', null);
        if (!empty($catid)) {
            $filters['catid'] = $catid;
        }

        $cat_type = $app->input->getString('cattype', null);
        if (!empty($cat_type)) {
            $filters['cattype'] = $cat_type;
        }

        $ftags = $app->input->get('ftags', null, 'array');
        if (is_array($ftags)) {
            $ftags = array_unique($ftags);
            $ftags = implode(',', $ftags);
        } else {
            $ftags = $app->input->get('ftags', '', 'string');
        }
        if (!empty($ftags)) {
            $filters['ftags'] = $ftags;
        }
        $cfrom = $app->input->getString('cfrom', null);
        if (!empty($cfrom)) {
            $filters['cfrom'] = $cfrom;
        }
        $cto = $app->input->getString('cto', null);
        if (!empty($cto)) {
            $filters['cto'] = $cto;
        }
        $ufrom = $app->input->getString('ufrom', null);
        if (!empty($ufrom)) {
            $filters['ufrom'] = $ufrom;
        }
        $uto = $app->input->getString('uto', null);
        if (!empty($uto)) {
            $filters['uto'] = $uto;
        }

        return $filters;
    }

    /**
     * Get all categories
     *
     * @return array|mixed
     */
    public function getCategories()
    {
        if (!class_exists('DropfilesModelFrontsearch')) {
            JLoader::import('frontsearch', JPATH_BASE . '/components/com_dropfiles/models');
        }
        $model = new DropfilesModelFrontsearch();
        $result = $model->getAllCategories();
        return $result;
    }
}
