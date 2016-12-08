<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

/** @var  \Akeeba\AdminTools\Admin\View\ControlPanel\Html $this */

// Protect from unauthorized access
defined('_JEXEC') or die;

$template = $this->container->template;

$graphDayFrom = gmdate('Y-m-d', time() - 30 * 24 * 3600);
?>

<h3>
	<?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_EXCEPTIONS'); ?>
</h3>

<div class="form-inline">
	<label>
		<?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_FROMDATE'); ?>
	</label>

	<?php echo \JHtml::_('calendar', $graphDayFrom, 'admintools_graph_datepicker', 'admintools_graph_datepicker'); ?>

	<button class="btn" id="admintools_graph_reload" onclick="return false;">
		<?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_RELOADGRAPHS'); ?>
	</button>
</div>

<div id="admintoolsExceptionsLineChart">
	<img src="<?php echo $template->parsePath('admin://components/com_admintools/media/images/throbber.gif'); ?>"
		 id="akthrobber"/>

	<p id="admintoolsExceptionsLineChartNoData" style="display:none">
		<?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_STATS_NODATA'); ?>
	</p>
</div>

<div class="clearfix"></div>

<h3><?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_EXCEPTSTATS'); ?></h3>
<div id="admintoolsExceptionsPieChart">
	<img src="<?php echo $template->parsePath('admin://components/com_admintools/media/images/throbber.gif'); ?>"
		 id="akthrobber2"/>

	<p id="admintoolsExceptionsPieChartNoData" style="display:none">
		<?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_STATS_NODATA'); ?>
	</p>
</div>
