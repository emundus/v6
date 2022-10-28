<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2014 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2014 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */


defined('_JEXEC') || die;


/**
 * Class DropfilesViewStatistics
 */
class DropfilesViewStatistics extends JViewLegacy
{
    /**
     * Default date format for statistics view
     *
     * @var string
     */
    public $dateFormat = 'd-m-Y';

    /**
     * Default line color for Total Download
     *
     * @var array
     */
    public $defaultLineColor = array('r' => 255, 'g' => 99, 'b' => 132);

    /**
     * Generate random RGB color for chart line
     *
     * @return array
     */
    public function randomColor()
    {
        return array('r' => rand(50, 222), 'g' => rand(50, 222), 'b' => rand(50, 222));
    }

    /**
     * Display the view
     *
     * @param null|string $tpl Template
     *
     * @return void
     */
    public function display($tpl = null)
    {

        $model = $this->getModel();
        $this->files = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->selectionValues = $this->get('SelectionValues');

        //get download count by date of each file
        $this->dates = array();
        $minDate = date('Y-m-d');
        $maxDate = date('Y-m-d');
        if (count($this->files)) {
            $fids = array();
            foreach ($this->files as $file) {
                $fids[] = $file->id;
            }

            $this->dates = $model->getDownloadCountByDate($fids);
            $date_arr = array_keys($this->dates);
            if (strtotime($date_arr[0]) < strtotime($minDate)) {
                $minDate = $date_arr[0];
            }
            if (strtotime(end($date_arr)) > strtotime($maxDate)) {
                $maxDate = end($date_arr);
            }
        }

        //calculate date range to draw chart
        $date_from = $this->state->get('filter.from');
        $date_to = $this->state->get('filter.to');
        if (empty($date_from) && empty($date_to)) {
            $date_from = date('Y-m-d', strtotime('-1 month', time()));
            $date_to = date('Y-m-d');
        } elseif (empty($date_to)) {
            $date_to = date('Y-m-d', strtotime('+1 day', strtotime($maxDate)));
        } elseif (empty($date_from)) {
            $date_from = date('Y-m-d', strtotime('-1 day', strtotime($minDate)));
        }

        //buil data for chart
        $this->dateFiles = array();
        $begin = new DateTime($date_from);
        $end = new DateTime($date_to);
        $end->modify('+1 day');
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);

        foreach ($period as $dt) {
            $temp = $dt->format('Y-m-d');
            $this->dateFiles[$temp] = array();
            foreach ($this->files as $file) {
                if (isset($this->dates[$temp][$file->id])) {
                    $this->dateFiles[$temp][$file->id] = $this->dates[$temp][$file->id];
                } else {
                    $this->dateFiles[$temp][$file->id] = 0;
                }
            }
        }
        // default global download count
        if ($this->state->get('filter.selection') === ''
            && $this->state->get('filter.search') === ''
            && $this->state->get('filter.from') === ''
            && $this->state->get('filter.to') === ''
        ) {
            $this->files = array();
        }
        $this->allCount = $model->getAllDownloadCount();
        parent::display($tpl);
        $app = JFactory::getApplication();
        if ($app->isClient('administrator')) {
            $this->addToolbar();
        }
    }

    /**
     * Add the page title and toolbar.
     *
     * @return void
     * @since  1.6
     */
    protected function addToolbar()
    {
        $canDo = DropfilesHelper::getActions();

        JToolBarHelper::title(JText::_('COM_DROPFILES_DOWNLOAD_STATISTICS'), 'dropfiles.png');
        if ($canDo->get('core.admin')) {
            $toolbar = JToolBar::getInstance();
            $toolbar->appendButton(
                'Link',
                'arrow-left',
                JText::_('COM_DROPFILES_BACK_TO_MAIN_VIEW'),
                'index.php?option=com_dropfiles'
            );
        }

        JToolBarHelper::divider();
    }
}
