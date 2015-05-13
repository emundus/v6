<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

F0FTemplateUtils::addCSS('media://com_admintools/css/jquery.jqplot.min.css?' . ADMINTOOLS_VERSION);

AkeebaStrapper::addJSfile('media://com_admintools/js/excanvas.min.js?' . ADMINTOOLS_VERSION);
AkeebaStrapper::addJSfile('media://com_admintools/js/jquery.jqplot.min.js?' . ADMINTOOLS_VERSION);
AkeebaStrapper::addJSfile('media://com_admintools/js/jqplot.highlighter.min.js?' . ADMINTOOLS_VERSION);
AkeebaStrapper::addJSfile('media://com_admintools/js/jqplot.dateAxisRenderer.min.js?' . ADMINTOOLS_VERSION);
AkeebaStrapper::addJSfile('media://com_admintools/js/jqplot.barRenderer.min.js?' . ADMINTOOLS_VERSION);
AkeebaStrapper::addJSfile('media://com_admintools/js/jqplot.pieRenderer.min.js?' . ADMINTOOLS_VERSION);
AkeebaStrapper::addJSfile('media://com_admintools/js/jqplot.hermite.js?' . ADMINTOOLS_VERSION);
AkeebaStrapper::addJSfile('media://com_admintools/js/cpanelgraphs.js?' . ADMINTOOLS_VERSION);

$graphDayFrom = gmdate('Y-m-d', time() - 30 * 24 * 3600);
?>
<h3><?php echo JText::_('COM_ADMINTOOLS_DASHBOARD_EXCEPTIONS') ?></h3>
<p>
	<?php echo JText::_('COM_ADMINTOOLS_DASHBOARD_FROMDATE') ?>
	<?php echo JHTML::_('calendar', $graphDayFrom, 'admintools_graph_datepicker', 'admintools_graph_datepicker'); ?>
	&nbsp;
	<button class="btn btn-mini" id="admintools_graph_reload" onclick="return false">
		<?php echo JText::_('COM_ADMINTOOLS_DASHBOARD_RELOADGRAPHS') ?>
	</button>
</p>
<div id="aksaleschart">
	<img src="<?php echo F0FTemplateUtils::parsePath('media://com_admintools/images/throbber.gif') ?>" id="akthrobber"/>

	<p id="aksaleschart-nodata" style="display:none">
		<?php echo JText::_('COM_ADMINTOOLS_DASHBOARD_STATS_NODATA') ?>
	</p>
</div>

<div style="clear: both;">&nbsp;</div>

<h3><?php echo JText::_('COM_ADMINTOOLS_DASHBOARD_EXCEPTSTATS') ?></h3>
<div id="aklevelschart">
	<img src="<?php echo F0FTemplateUtils::parsePath('media://com_admintools/images/throbber.gif') ?>"
		 id="akthrobber2"/>

	<p id="aklevelschart-nodata" style="display:none">
		<?php echo JText::_('COM_ADMINTOOLS_DASHBOARD_STATS_NODATA') ?>
	</p>
</div>

<script type="text/javascript">

	admintools_cpanel_graph_from = "<?php echo $graphDayFrom ?>";

	(function ($)
	{
		$(document).ready(function ()
		{
			admintools_cpanel_graphs_load();

			$('#admintools_graph_reload').click(function (e)
			{
				admintools_cpanel_graphs_load();
			})
		});
	})(akeeba.jQuery);
</script>