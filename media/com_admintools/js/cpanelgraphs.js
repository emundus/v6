/**
 * @package        akeebasubs
 * @copyright    Copyright (c)2010-2016 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license        GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

/**
 * Setup (required for Joomla! 3)
 */
if (typeof(akeeba) == 'undefined')
{
	var akeeba = {};
}
if (typeof(akeeba.jQuery) == 'undefined')
{
	akeeba.jQuery = jQuery.noConflict();
}

var admintools_cpanel_graph_from = "";
var admintools_cpanel_graph_to = "";

var admintools_cpanel_graph_exceptPoints = [];
var admintools_cpanel_graph_subsPoints = [];
var admintools_cpanel_graph_typePoints = [];

var admintools_cpanel_graph_plot1 = null;
var admintools_cpanel_graph_plot2 = null;

function admintools_cpanel_graphs_load()
{
	// Get the From date
	admintools_cpanel_graph_from = document.getElementById('admintools_graph_datepicker').value;

	// Calculate the To date
	var thatDay = new Date(admintools_cpanel_graph_from);
	thatDay = new Date(thatDay.getTime() + 30 * 86400000);
	admintools_cpanel_graph_to = thatDay.getUTCFullYear() + '-' + (thatDay.getUTCMonth() + 1) + '-' + thatDay.getUTCDate();

	// Clear the data arrays
	admintools_cpanel_graph_exceptPoints = [];
	admintools_cpanel_graph_subsPoints = [];
	admintools_cpanel_graph_typePoints = [];

	// Remove the charts and show the spinners
	(function ($)
	{
		$('#aklevelschart').empty();
		$('#aklevelschart').hide();
		admintools_cpanel_graph_plot2 = null;
		$('#aksaleschart').empty();
		$('#aksaleschart').hide();
		admintools_cpanel_graph_plot1 = null;

		$('#akthrobber').show();
		$('#akthrobber2').show();
	})(akeeba.jQuery);

	admintools_load_exceptions();
}

function admintools_load_exceptions()
{
	(function ($)
	{
		var url = "index.php?option=com_admintools&view=logs&datefrom=" + admintools_cpanel_graph_from + "&dateto=" + admintools_cpanel_graph_to + "&groupbydate=1&savestate=0&format=json&limit=0&limitstart=0";
		$.getJSON(url, function (data)
		{
			$.each(data, function (index, item)
			{
				admintools_cpanel_graph_exceptPoints.push([item.date, parseInt(item.exceptions * 100) * 1 / 100]);
				//admintools_cpanel_graph_subsPoints.push([item.date, item.exceptions * 1]);
				admintools_cpanel_graph_subsPoints.push([]);
			});
			$('#akthrobber').hide();
			$('#aksaleschart').show();
			if (admintools_cpanel_graph_exceptPoints.length == 0)
			{
				$('#aksaleschart-nodata').show();
				return;
			}
			admintools_render_exceptions();
			admintools_load_types();
		});
	})(akeeba.jQuery);
}

function admintools_load_types()
{
	(function ($)
	{
		var url = "index.php?option=com_admintools&view=logs&datefrom=" + admintools_cpanel_graph_from + "&dateto=" + admintools_cpanel_graph_to + "&groupbydate=0&groupbytype=1&savestate=0&format=json&limit=0&limitstart=0";
		$.getJSON(url, function (data)
		{
			$.each(data, function (index, item)
			{
				admintools_cpanel_graph_typePoints.push([item.reason, parseInt(item.exceptions * 100) * 1 / 100]);
			});
			$('#akthrobber2').hide();
			$('#aklevelschart').show();
			if (admintools_cpanel_graph_typePoints.length == 0)
			{
				$('#aklevelschart-nodata').show();
				return;
			}
			admintools_render_types();
		});
	})(akeeba.jQuery);
}

function admintools_render_exceptions()
{
	(function ($)
	{
		$.jqplot.config.enablePlugins = true;
		admintools_cpanel_graph_plot1 = $.jqplot('aksaleschart', [
			admintools_cpanel_graph_subsPoints, admintools_cpanel_graph_exceptPoints
		], {
			show:         true,
			axes:         {
				xaxis:  {renderer: $.jqplot.DateAxisRenderer, tickInterval: '1 week'},
				yaxis:  {min: 0, tickOptions: {formatString: '%.2f'}},
				y2axis: {min: 0, tickOptions: {formatString: '%u'}}
			},
			series:       [
				{
					yaxis:           'y2axis',
					lineWidth:       1,
					renderer:        $.jqplot.BarRenderer,
					rendererOptions: {barPadding: 0, barMargin: 0, barWidth: 5, shadowDepth: 0, varyBarColor: 0},
					markerOptions:   {
						style: 'none'
					},
					color:           '#aae0aa'
				},
				{
					lineWidth:       3,
					markerOptions:   {
						style: 'filledCircle',
						size:  8
					},
					renderer:        $.jqplot.hermiteSplineRenderer,
					rendererOptions: {steps: 60, tension: 0.6}
				}
			],
			highlighter:  {
				show:       true,
				sizeAdjust: 7.5
			},
			axesDefaults: {useSeriesColor: true}
		}).replot();
	})(akeeba.jQuery);
}

function admintools_render_types()
{
	(function ($)
	{
		$.jqplot.config.enablePlugins = true;
		admintools_cpanel_graph_plot2 = $.jqplot('aklevelschart', [admintools_cpanel_graph_typePoints], {
			show:           true,
			highlighter:    {
				show: false
			},
			seriesDefaults: {
				renderer:        jQuery.jqplot.PieRenderer,
				rendererOptions: {
					showDataLabels: true,
					dataLabels:     'value'
				},
				markerOptions:   {
					style: 'none'
				}
			},
			legend:         {show: true, location: 'e'}
		}).replot();
	})(akeeba.jQuery);
}