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
jimport('joomla.filter.output');

/**
 * Class DropfilesViewFrontcategories
 */
class DropfilesViewFrontcategories extends JViewLegacy
{
    /**
     * Items
     *
     * @var null
     */
    protected $items = null;

    /**
     * Display the view
     *
     * @param null|string $tpl Template
     *
     * @return mixed False on error, null otherwise.
     * @throws Exception Throw when application can not start
     * @since  version
     */
    public function display($tpl = null)
    {
        // Initialise variables
        $items = $this->get('Items');

        $modelCat = $this->getModel('frontcategory');
        $item = $modelCat->getCategory();

        $content = new stdClass();
        $content->categories = $items;

        $id = (int)JFactory::getApplication()->input->get('id');
        $top = (int)JFactory::getApplication()->input->get('top');
        if ($id === $top) {
            $item->parent_id = 0;
        }

        $content->category = $item;
        if ($content->category) {
            $content->category->alias = JFilterOutput::stringURLSafe($content->category->title);
        }
        echo json_encode($content);
        JFactory::getApplication()->close();
    }
}
