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
 * Class DropfilesViewFrontsearch
 */
class DropfilesViewFrontsearch extends JViewLegacy
{
    /**
     * Display the view
     *
     * @param string $tpl Template
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $path_dropfilesgoogle = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
        JLoader::register('DropfilesGoogle', $path_dropfilesgoogle);
        JModelLegacy::addIncludePath(
            JPATH_ROOT . '/administrator/components/com_dropfiles/models/',
            'DropfilesModelCategories'
        );
        $categoriesModel = JModelLegacy::getInstance('Categories', 'dropfilesModel');
        $this->allTagsFiles = $categoriesModel->getAllTagsFiles();
        $model = $this->getModel();
        $this->categories = $model->getAllCategories();
        $filters = array();
        $app = JFactory::getApplication();
        $q = $app->input->getString('q', null);
        if (!empty($q)) {
            $filters['q'] = $q;
        }
        $catid = $app->input->getString('catid', null);
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

        $this->filters = $filters;
        $this->doSearch = false;
        $this->params = JComponentHelper::getParams('com_dropfiles');
        $app = JFactory::getApplication();
        $this->ordering = $app->input->getString('ordering', '');
        $this->dir = $app->input->getString('dir', 'asc');
        if (!empty($filters)) {
            $this->doSearch = true;
            $this->files = $model->searchFile($filters);
            if (count($this->files)) {
                $total = count($this->files);
                $limit = $app->input->getInt('limit', (int)$this->params->get('search_limit', 20));
                $limitStart = $app->input->getInt('limitstart', 0);
                if ($limitStart > $total) {
                    $limitStart = $total;
                }
                $this->pagination = new JPagination($total, $limitStart, $limit);
                $this->pagination->setAdditionalUrlParam('view', 'frontsearch');
                foreach ($filters as $key => $value) {
                    $this->pagination->setAdditionalUrlParam($key, $value);
                }
                $this->files = array_slice($this->files, $this->pagination->limitstart, $this->pagination->limit);
            }
        }

        parent::display('results');
    }
}
