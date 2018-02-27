<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var  \Akeeba\AdminTools\Admin\View\ControlPanel\Html $this */

// Protect from unauthorized access
defined('_JEXEC') or die;

$template = $this->container->template;

$graphDayFrom = gmdate('Y-m-d', time() - 30 * 24 * 3600);
?>

<div class="akeeba-panel--default">
    <header class="akeeba-block-header">
        <h3><?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_EXCEPTIONS'); ?></h3>
    </header>

    <div class="akeeba-form--inline">
        <div class="akeeba-form-group">
            <label><?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_FROMDATE'); ?></label>

            <?php echo \JHtml::_('calendar', $graphDayFrom, 'admintools_graph_datepicker', 'admintools_graph_datepicker'); ?>
        </div>

        <div class="akeeba-form-group--actions">
            <button class="akeeba-btn--dark" id="admintools_graph_reload" onclick="return false;">
                <?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_RELOADGRAPHS'); ?>
            </button>
        </div>
    </div>

    <div id="admintoolsExceptionsLineChart">
        <span id="akthrobber" class="akion-load-a"></span>

        <p id="admintoolsExceptionsLineChartNoData" style="display:none">
            <?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_STATS_NODATA'); ?>
        </p>
    </div>

    <div class="clearfix"></div>

    <h3><?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_EXCEPTSTATS'); ?></h3>
    <div id="admintoolsExceptionsPieChart">
        <span id="akthrobber2" class="akion-load-a"></span>

        <p id="admintoolsExceptionsPieChartNoData" style="display:none">
            <?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_STATS_NODATA'); ?>
        </p>
    </div>
</div>
