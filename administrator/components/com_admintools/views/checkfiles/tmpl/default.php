<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;
?>

<p>
	<?php echo JText::_('COM_ADMINTOOLS_CHECKFILE_INFO') ?>
</p>

<div class="alert alert-info" id="akeebaFileCheckMessage">
	<?php echo JText::_('COM_ADMINTOOLS_CHECKFILE_MESSAGE') ?>
</div>

<hr/>

<h3>
	<?php echo JText::_('COM_ADMINTOOLS_CHECKFILE_FOLDERS') ?>
</h3>
<ul id="akeebaFolders"></ul>

<hr/>

<h3>
	<?php echo JText::_('COM_ADMINTOOLS_CHECKFILE_FILES') ?>
</h3>
<ul id="akeebaFiles"></ul>

<script type="text/javascript">
	(function ($)
	{
		$(document).ready(function ()
		{
			runAkeebaCheckFilesStep();
		});
	})(akeeba.jQuery);

	var akeebaCheckFilesIndex = 0;

	function runAkeebaCheckFilesStep()
	{
		(function ($){
		var url = 'index.php?option=com_admintools&view=checkfile&task=step&tmpl=component&idx=';
		url += akeebaCheckFilesIndex;
		$.ajax(url, {
			success: function (msg, textStatus, jqXHR)
			{
				// Get rid of junk before and after data
				var match = msg.match(/###([\s\S]*?)###/);
				data = match[1];

				if (!data)
				{
					$('#akeebaFileCheckMessage').hide('fast');
					return;
				}

				data = eval("(function(){return " + data + ";})()");

				if (!data)
				{
					$('#akeebaFileCheckMessage').hide('fast');
					return;
				}

				akeebaCheckFilesIndex = data.idx;

				if (data.folders.length)
				{
					$(data.folders).each(function(idx, folder){
						el = $(document.createElement('li'));
						el.text(folder);
						el.appendTo($('#akeebaFolders'));
					});
				}

				if (data.files.length)
				{
					$(data.files).each(function(idx, file){
						el = $(document.createElement('li'));
						el.text(file);
						el.appendTo($('#akeebaFiles'));
					});
				}

				if (!data.done)
				{
					setTimeout('runAkeebaCheckFilesStep();', 100);
				}
				else
				{
					$('#akeebaFileCheckMessage').hide('fast');
				}
			}
		});
	})(akeeba.jQuery);
	}
</script>
