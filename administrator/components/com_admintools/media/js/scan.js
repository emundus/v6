/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

/** @var The AJAX proxy URL */
var admintools_scan_ajax_url_start = "";
var admintools_scan_ajax_url_step = "";

/** @var The callback function to call on error */
var admintools_scan_error_callback = scan_dummy_error_handler;

var admintools_scan_msg_ago = '';

var admintools_scan_timerid = -1;
var admintools_scan_responseago = 0;

/**
 * An extremely simple error handler, dumping error messages to screen
 * @param error The error message string
 */
function scan_dummy_error_handler(error)
{
	alert(error);
	window.location = 'index.php?option=com_admintools&view=scans';
}

function doScanAjax(url, successCallback, errorCallback)
{
	(function ($)
	{
		var structure =
			{
				type:    "GET",
				url:     url,
				cache:   false,
				timeout: 600000,
				success: function (msg)
				{
					// Initialize
					var junk = null;
					var message = "";

					// Get rid of junk before the data
					var valid_pos = msg.indexOf('###');

					if (valid_pos == -1)
					{
						// Valid data not found in the response
						msg = 'Invalid AJAX data: ' + msg;
						if (errorCallback == null)
						{
							if (admintools_scan_error_callback != null)
							{
								admintools_scan_error_callback(msg);
							}
						}
						else
						{
							errorCallback(msg);
						}
						return;
					}
					else if (valid_pos != 0)
					{
						// Data is prefixed with junk
						message = msg.substr(valid_pos);
					}
					else
					{
						message = msg;
					}
					message = message.substr(3); // Remove triple hash in the beginning

					// Get of rid of junk after the data
					valid_pos = message.lastIndexOf('###');
					message = message.substr(0, valid_pos); // Remove triple hash in the end

					try
					{
						var data = JSON.parse(message);
					}
					catch (err)
					{
						msg = err.message + "\n<br/>\n<pre>\n" + message + "\n</pre>";

						if (errorCallback == null)
						{
							if (admintools_scan_error_callback != null)
							{
								admintools_scan_error_callback(msg);
							}
						}
						else
						{
							errorCallback(msg);
						}
						return;
					}

					// Call the callback function
					successCallback(data);
				},
				error:   function (Request, textStatus, errorThrown)
				{
					var message = '<strong>AJAX Loading Error</strong><br/>HTTP Status: ' + Request.status + ' (' +
						Request.statusText + ')<br/>';
					message = message + 'Internal status: ' + textStatus + '<br/>';
					message = message + 'XHR ReadyState: ' + Request.readyState + '<br/>';
					message = message + 'Raw server response:<br/>' + Request.responseText;

					if (errorCallback == null)
					{
						if (admintools_scan_error_callback != null)
						{
							admintools_scan_error_callback(message);
						}
					}
					else
					{
						errorCallback(message);
					}
				}
			};

		$.ajax(structure);
	})(akeeba.jQuery);
}

function startScan()
{
	document.getElementById('admintools-scan-dim').style.display = 'block';
	doScanAjax(admintools_scan_ajax_url_start, function (data)
	{
		processScanStep(data);
	})
}

function processScanStep(data)
{
	stop_scan_timer();

	if (data.status == false)
	{
		// handle failure
		admintools_scan_error_callback(data.error);
	}
	else
	{
		if (data.done)
		{
			window.location = 'index.php?option=com_admintools&view=scans';
		}
		else
		{
			start_scan_timer();
			doScanAjax(admintools_scan_ajax_url_step, function (data)
			{
				processScanStep(data);
			})
		}
	}
}

function start_scan_timer()
{
	if (admintools_scan_timerid >= 0)
	{
		window.clearInterval(admintools_scan_timerid);
	}

	admintools_scan_responseago = 0;
	set_scan_timermsg();
	admintools_scan_timerid = window.setInterval('step_scan_timer()', 1000);
}

function step_scan_timer()
{
	admintools_scan_responseago++;
	set_scan_timermsg();
}

function stop_scan_timer()
{
	if (admintools_scan_timerid >= 0)
	{
		window.clearInterval(admintools_scan_timerid);
	}
}

function set_scan_timermsg()
{
	var myText = admintools_scan_msg_ago;
	document.id('admintools-lastupdate-text').innerHTML = myText.replace('%s', admintools_scan_responseago);
}