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

// No direct access.
defined('_JEXEC') || die;
JHtml::_('jquery.framework');
$doc = JFactory::getDocument();

$doc->addStyleSheet(JUri::root() . 'components/com_dropfiles/assets/css/jquery.datetimepicker.css');
$doc->addStyleSheet(JUri::base() . 'components/com_dropfiles/assets/css/statistics.css');

$doc->addScript(JUri::root() . 'components/com_dropfiles/assets/js/jquery.datetimepicker.js');
$doc->addScript(JUri::base() . 'components/com_dropfiles/assets/js/statistics.js');
$doc->addScript(JUri::base() . 'components/com_dropfiles/assets/js/chart.min.js');
$app = JFactory::getApplication();
//JHtml::_('behavior.multiselect');
if (DropfilesBase::isJoomla40()) {
    $doc->addScript(JURI::root() . 'components/com_dropfiles/assets/js/chosen.jquery.min.js');
    $doc->addStyleSheet(JURI::root() . 'components/com_dropfiles/assets/css/chosen.css');
    $doc->addScript(JURI::root() . 'components/com_dropfiles/assets/js/jquery.minicolors.min.js');
    $doc->addStyleSheet(JURI::root() . 'components/com_dropfiles/assets/css/jquery.minicolors.css');
} else {
    JHtml::_('formbehavior.chosen', 'select.chosen');
    JHtml::_('behavior.calendar');
}

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$isNotFound = false;
$params = JComponentHelper::getParams('com_dropfiles');
$modelStatistics = JModelLegacy::getInstance('Statistics', 'dropfilesModel');

?>
<div id="dropfiles-statistics" class="download-statistics">
    <h2><?php echo JText::_('COM_DROPFILES_DOWNLOAD_STATISTICS'); ?></h2>
    <form action="<?php echo JRoute::_('index.php?option=com_dropfiles&view=statistics'); ?>" class="dropfilesparams dropfiles-statistics--form"
          id="adminForm" name="adminForm" method="post">
        <input type="hidden" value="com_dropfiles" name="option">
        <input type="hidden" value="statistics" name="view">
        <div class="row-fluid dropfiles-statistics--form-row">
            <div class="selection input-append dropfiles-statistics--type dropfiles-statistics--form-col">
                <?php
                if ((int) $params->get('track_user_download', 0) === 1) {
                    echo JHtml::_(
                        'select.genericlist',
                        array(
                            '' => JText::_('COM_DROPFILES_TOTAL_DOWNLOADS'),
                            'category' => JText::_('COM_DROPFILES_CATEGORY'),
                            'files' => JText::_('COM_DROPFILES_FILES'),
                            'users' => JText::_('COM_DROPFILES_TRACK_USER_DOWNLOAD'),
                        ),
                        'selection',
                        array(
                            'list.attr' => 'class="inputbox dropfilesinput"',
                            'list.select' => $this->state->get('filter.selection')
                        )
                    );
                } else {
                    echo JHtml::_(
                        'select.genericlist',
                        array(
                            '' => JText::_('COM_DROPFILES_TOTAL_DOWNLOADS'),
                            'category' => JText::_('COM_DROPFILES_CATEGORY'),
                            'files' => JText::_('COM_DROPFILES_FILES'),
                        ),
                        'selection',
                        array(
                            'list.attr' => 'class="inputbox dropfilesinput"',
                            'list.select' => $this->state->get('filter.selection')
                        )
                    );
                }
                ?>
            </div>
            <?php if ($this->state->get('filter.selection') !== '' && $this->state->get('filter.selection') !== null) { ?>
                <div class="selection_value dropfiles-statistics--form-col dropfiles-statistics--additional">
                    <?php
                    $selection_value = count($this->selectionValues) ? $this->selectionValues : array();
                    $select = $app->input->get('selection_value', array(), 'array');
                    echo JHtml::_(
                        'select.genericlist',
                        $selection_value,
                        'selection_value[]',
                        array(
                            'list.attr' => 'class="inputbox chosen" multiple="true"',
                            'list.select' => $select
                        )
                    );
                    ?>
                </div>
            <?php } ?>
            <div class="dropfiles-statistics--date-filter dropfiles-statistics--form-col">
                <div class=" from-date">
                    <span class="lbl-date"><?php echo JText::_('COM_DROPFILES_FROM'); ?> </span>
                    <div class="input-append">
                        <input type="text" name="fdate" id="fdate" value="<?php echo $this->state->get('filter.from'); ?>"
                               maxlength="45" class="input-medium">
                        <button type="button" class="btn" id="fdate_img"><span data-id="fdate"  class="fa icon-calendar icon-date"></span></button>
                    </div>
                </div>
                <div class=" to-date">
                    <span class="lbl-date"><?php echo JText::_('COM_DROPFILES_TO'); ?></span>
                    <div class="input-append">
                        <input type="text" name="tdate" id="tdate" value="<?php echo $this->state->get('filter.to'); ?>"
                               maxlength="45" class="input-medium">
                        <button type="button" class="btn" id="tdate_img"><span data-id="tdate" class="fa icon-calendar icon-date"></span></button>
                    </div>
                </div>
            </div>
            <div class="dropfiles-statistics--submit dropfiles-statistics--form-col">
                <button class="btn dropfiles-search-btn ju-button orange-button" type="submit">
                    <?php echo JText::_('COM_DROPFILES_APPLY_FILTER'); ?>
                </button>
            </div>
        </div>


        <div id="chart_div_container" class="dropfiles-statistics--chart">
            <h3><?php echo JText::_('COM_DROPFILES_DOWNLOAD_COUNT'); ?></h3>
            <canvas id="chart_div" style="min-height: 50vh;height:500px; width:100%"></canvas>
        </div>

        <div class="row-fluid dropfiles-statistics--form-row">
            <?php if ($this->state->get('filter.selection') !== '') { ?>
                <div class="span6 dropfiles-statistics--form-col dropfiles-statistics--search">
                    <div class="input-append">
                        <input class="dropfilesinput " id="query" type="text" name="query" placeholder="<?php echo JText::_('COM_DROPFILES_CONFIG_SEARCH_LABEL'); ?>"
                               value="<?php echo $this->state->get('filter.search'); ?>">
                        <button class="btn statistics-search-btn" type="submit" style="margin-right:5px"><span class="fa icon-search"></span>
                        </button>
                    </div>
                </div>
                <div class="dropfiles-statistics--form-col dropfiles-statistics--reset">
                    <button class="btn btn-reset ju-button orange-outline-button" type="reset">
                        <?php echo JText::_('COM_DROPFILES_RESET'); ?>
                    </button>
                </div>
                <div class="span6 text-right dropfiles-statistics--form-col dropfiles-statistics--limit">
                    <label for="file_per_page"><?php echo JText::_('COM_DROPFILES_FILE_PER_PAGE'); ?></label>
                    <?php
                    echo JHtml::_(
                        'select.genericlist',
                        array(
                            '5' => 5,
                            '10' => 10,
                            '15' => 15,
                            '20' => 20,
                            '25' => 25,
                            '30' => 30,
                            '0' => 'All',
                        ),
                        'limit',
                        array(
                            'list.attr' => 'id="file_per_page" onchange="this.form.submit();" style="width:150px;" class="dropfilesinput"',
                            'list.select' => $this->state->get('list.limit')
                        )
                    );
                    ?>

                </div>
            <?php } ?>
        </div>
        <?php if (count($this->files)) { ?>
            <div class="dropfiles-statistics--form-row">
                <div class="dropfiles-statistics--table">
                    <table class="table ju-table">
                        <thead>
                        <tr>
                            <th class="">
                                <?php
                                echo JHtml::_('grid.sort', 'COM_DROPFILES_FILE_TITLE', 'a.title', $listDirn, $listOrder);
                                ?>
                            </th>
                            <th class="">
                                <?php
                                echo JHtml::_('grid.sort', 'COM_DROPFILES_CATEGORY', 'c.title', $listDirn, $listOrder);
                                ?>
                            </th>
                            <?php $choose = $app->input->get('selection_value', array(), 'array');?>
                            <?php if ($this->state->get('filter.selection') === 'users' && !empty($choose) && (int) $params->get('track_user_download', 0) === 1) : ?>
                                <th class="">
                                    <?php echo JHtml::_('grid.sort', 'COM_DROPFILES_DOWNLOAD_BY', 'ch.related_users', $listDirn, $listOrder);?>
                                </th>
                            <?php endif;?>
                            <th class="">
                                <?php
                                echo JHtml::_('grid.sort', 'COM_DROPFILES_DOWNLOAD_COUNT', 'count_hits', $listDirn, $listOrder);
                                ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($this->files as $file) {
                            ?>
                            <tr class="file">
                                <td class="first"><?php echo $file->title; ?></td>
                                <td class=""><?php echo $file->cattitle; ?></td>
                                <?php if ($this->state->get('filter.selection') === 'users' && !empty($choose) && (int) $params->get('track_user_download', 0) === 1) : ?>
                                    <td class="">
                                        <?php
                                        $userobj = $modelStatistics->getUserDownload($file->user_download);
                                        echo $userobj->name;
                                        ?>
                                    </td>
                                <?php endif;?>
                                <td class="last"><?php echo $file->count_hits; ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } else { ?>
            <?php if ($this->state->get('filter.selection') === ''
                && $this->state->get('filter.search') === ''
                && $this->state->get('filter.from') === ''
                && $this->state->get('filter.to') === ''
            ) { ?>
            <?php } else {
                $isNotFound = true;
                ?>
                <h3><?php echo JText::_('COM_DROPFILES_DOWNLOAD_STATISTICS_NOT_FOUND'); ?></h3>
            <?php } ?>
        <?php } ?>
        <?php if (count($this->files)) { ?>
            <?php if ($this->state->get('filter.selection') !== '') { ?>
                <div class="dropfiles-statistics--pagination" style="text-align: right">
                    <?php echo $this->pagination->getListFooter(); ?>
                </div>
            <?php } ?>
        <?php } ?>
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
    </form>

</div>
<?php
//$this->files
$currentLanguage = JFactory::getLanguage();
?>
<?php
$lables = array();
$datas = array();
if (count($this->files)) {
    foreach ($this->files as $file) {
        //store all name files download
        $row = array();
        $row['label'] = $file->title;
        $row['color'] = $this->randomColor();
        foreach ($this->dateFiles as $date => $columns) {
            $date = new DateTime($date);
            $row['datas'][] = '{x:\'' . $date->format($this->dateFormat) . '\', y: \'' . $columns[$file->id] . '\'}';
        }
        $datas[] = $row;
    }
    foreach ($this->dateFiles as $date => $columns) {
        $date = new DateTime($date);
        $date->format($this->dateFormat);
        array_push($lables, $date->format($this->dateFormat));
    }
} else {
    $datas[0]['label'] = JText::_('COM_DROPFILES_DOWNLOAD_STATISTICS_TOTAL');
    $datas[0]['color'] = $this->defaultLineColor; // Default color for Total Download
    $datas[0]['datas'] = array();
    foreach ($this->allCount as $item) {
        $date = new DateTime($item->date);
        array_push($lables, $date->format($this->dateFormat));
        $datas[0]['datas'][] = $item->count;
    }
}

?>
<script type="text/javascript">
    var lables = <?php echo json_encode($lables);?>;
    var dataSet = [
        <?php foreach ($datas as $data) : ?>
        {
            label: '<?php echo $data['label']; ?>',
            data: [<?php echo implode(',', $data['datas']); ?>],
            backgroundColor: 'rgba(<?php echo $data['color']['r']; ?>, <?php echo $data['color']['g']; ?>, <?php echo $data['color']['b']; ?>, 0.2)',
            borderColor: 'rgba(<?php echo $data['color']['r']; ?>, <?php echo $data['color']['g']; ?>, <?php echo $data['color']['b']; ?>, 1)',
            borderWidth: 1
        },
        <?php endforeach; ?>
    ];
    var ctx = document.getElementById('chart_div');
    var dropfilesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: lables,
            datasets: dataSet,
        },
        options: {
            responsive: true,
            legend: {
                position: 'bottom'
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        maxTicksLimit: 25,
                        min: 0,
                        //stepSize: 1
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'Download Count'
                    }
                }],
                xAxes: [{
                    ticks: {
                        maxTicksLimit: 25,
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'Date'
                    }
                }]
            },
            layout: {
                padding: {
                    left: 25,
                    right: 25,
                    top: 25,
                    bottom: 25
                }
            },
            elements: {
                line: {
                    tension: 0, // disables bezier curves,
                    fill: false
                }
            },
            tooltips: {
                mode: 'index',
                intersect: false,
            }
        }
    });

</script>