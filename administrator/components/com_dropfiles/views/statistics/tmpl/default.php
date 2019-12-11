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
$doc->addScript('https://www.gstatic.com/charts/loader.js');
$app = JFactory::getApplication();
//JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select.chosen');
JHtml::_('behavior.calendar');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$isNotFound = false;
?>
<div class="download-statistics">
    <form action="<?php echo JRoute::_('index.php?option=com_dropfiles&view=statistics'); ?>" class="dropfilesparams"
          id="adminForm" name="adminForm" method="post">
        <input type="hidden" value="com_dropfiles" name="option">
        <input type="hidden" value="statistics" name="view">
        <div class="row-fluid">
            <div class="selection input-append">
                <?php
                echo JHtml::_(
                    'select.genericlist',
                    array(
                        '' => JText::_('COM_DROPFILES_TOTAL_DOWNLOADS'),
                        'category' => JText::_('COM_DROPFILES_CATEGORY'),
                        'files' => JText::_('COM_DROPFILES_FILES'),
                    ),
                    'selection',
                    array(
                        'list.attr' => 'class="inputbox"',
                        'list.select' => $this->state->get('filter.selection')
                    )
                );
                ?>
            </div>
            <div class="selection_value">
                <?php if ($this->state->get('filter.selection') !== '') { ?>
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
                <?php } ?>

            </div>
            <div class=" from-date">
                <span class="lbl-date"><?php echo JText::_('COM_DROPFILES_FROM'); ?> </span>
                <div class="input-append">
                    <input type="text" name="fdate" id="fdate" value="<?php echo $this->state->get('filter.from'); ?>"
                           maxlength="45" class="input-medium">
                    <button type="button" class="btn" id="fdate_img"><span class="icon-calendar"></span></button>
                </div>
            </div>

            <div class=" to-date">
                <span class="lbl-date"><?php echo JText::_('COM_DROPFILES_TO'); ?></span>
                <div class="input-append">
                    <input type="text" name="tdate" id="tdate" value="<?php echo $this->state->get('filter.to'); ?>"
                           maxlength="45" class="input-medium">
                    <button type="button" class="btn" id="tdate_img"><span class="icon-calendar"></span></button>
                </div>
            </div>

            <button class="btn dropfiles-search-btn" type="submit">
                <span class="icon-search"></span>
                <?php echo JText::_('COM_DROPFILES_APPLY_FILTER'); ?>
            </button>
        </div>


        <div id="chart_div"></div>

        <div class="row-fluid">
            <?php if ($this->state->get('filter.selection') !== '') { ?>
                <div class="span6">
                    <div class="input-append">
                        <input class="" id="query" type="text" name="query"
                               value="<?php echo $this->state->get('filter.search'); ?>">
                        <button class="btn" type="submit" style="margin-right:5px"><span class="icon-search"></span>
                        </button>
                        <button class="btn btn-reset" type="reset"
                                style="background-color:#dfdddd !important; color: #4a4a4a !important">
                            <?php echo JText::_('COM_DROPFILES_RESET'); ?>
                        </button>
                    </div>
                </div>
                <div class="span6 text-right">
                    <label for="file_per_page"><?php echo JText::_('COM_DROPFILES_FILE_PER_PAGE'); ?>
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
                                'list.attr' => 'id="file_per_page" onchange="this.form.submit();" style="width:50px;"',
                                'list.select' => $this->state->get('list.limit')
                            )
                        );
                        ?>
                    </label>

                </div>
            <?php } ?>
        </div>
        <?php if (count($this->files)) { ?>
            <table class="table">
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
                        <td class=""><?php echo $file->title; ?></td>
                        <td class=""><?php echo $file->cattitle; ?></td>
                        <td class=""><?php echo $file->count_hits; ?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        <?php } else { ?>
            <?php if ($this->state->get('filter.selection') === ''
                && $this->state->get('filter.search') === ''
                && $this->state->get('filter.from') === ''
                && $this->state->get('filter.to') === ''
            ) { ?>
            <?php } else {
                $isNotFound = true;
                ?>
                <h3><?php echo JText::_('COM_DROPFILES_NOT_FOUND'); ?></h3>
            <?php } ?>
        <?php } ?>
        <?php if (count($this->files)) { ?>
            <?php if ($this->state->get('filter.selection') !== '') { ?>
                <div class="" style="text-align: right">
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

<script type="text/javascript">

    google.charts.load('current', {packages: ['annotationchart'], 'language': '<?php echo $currentLanguage->getTag(); ?>'});
    <?php if (!$isNotFound) { ?>
    google.charts.setOnLoadCallback(drawchart);
    <?php } ?>
    function drawchart() {
        var data = new google.visualization.DataTable();

        data.addColumn('date', 'Date');
        <?php if (count($this->files)) { ?>
            <?php foreach ($this->files as $file) { ?>
        data.addColumn('number', '<?php echo $file->title;?>');
            <?php } ?>
            <?php foreach ($this->dateFiles as $date => $columns) {
                    $date = new DateTime($date);
                ?>
        data.addRow([new Date(<?php echo $date->format('Y');?>,
                <?php echo((int)$date->format('m') - 1);?> ,
                <?php echo $date->format('d');?>),
                <?php echo implode(',', $columns);?>]);
            <?php } ?>
        <?php } else { ?>
        data.addColumn('number', '<?php echo JText::_('COM_DROPFILES_GLOBAL_DOWNLOAD'); ?>');
            <?php
            foreach ($this->allCount as $item) {
                    $date = new DateTime($item->date);
                ?>
        data.addRow([new Date(<?php echo $date->format('Y');?>,
                <?php echo((int)$date->format('m') - 1);?> ,
                <?php echo $date->format('d');?>),
                <?php echo $item->count;?>]);
            <?php } ?>
        <?php } ?>
        var options = {
            hAxis: {
                title: '<?php echo JText::_('COM_DROPFILES_DATE'); ?>'
            },
            vAxis: {
                title: '<?php echo JText::_('COM_DROPFILES_DOWNLOAD_COUNT'); ?>'
            },
            height: 400,
            backgroundColor: '#F7F9FA'
        };

        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        chart.draw(data, options);
    }
</script>
