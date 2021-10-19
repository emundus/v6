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
 * Class DropfilesViewFile
 */
class DropfilesViewFile extends JViewLegacy
{
    /**
     * State
     *
     * @var string
     */
    protected $state;


    /**
     *  Method display a file
     *
     * @param null|string $tpl Template
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();
        //load the parameters form
        $this->form = $model->getForm();
        $this->type = JFactory::getApplication()->input->getCmd('type', 'default');

        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('DISTINCT a.id AS value, a.title AS text')
            ->from('#__tags AS a')
            ->join('LEFT', $db->qn('#__tags') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt');


        $query->where($db->qn('a.lft') . ' > 0');

        $query->where('a.published = 1');

        $query->order('a.lft ASC');

        // Get the options.
        $db->setQuery($query);

        $tags = $db->loadObjectList();
        $allTagsFiles = array();
        if ($tags) {
            foreach ($tags as $tag) {
                $allTagsFiles[] = '' . htmlentities(stripslashes($tag->text), ENT_QUOTES, 'UTF-8');
            }
            $this->allTagsFiles = '["' . implode('","', $allTagsFiles) . '"]';
        } else {
            $this->allTagsFiles = '[]';
        }


        parent::display($tpl);
    }
}
