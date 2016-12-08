<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\AdminTools\Admin\View\CheckTempAndLogDirectories\Html */

defined('_JEXEC') or die;

?>
<h1 id="check-header"><?php echo \JText::_('COM_ADMINTOOLS_LBL_CHECKTEMPANDLOGDIRECTORIES_CHECKINPROGRESS'); ?></h1>

<div class="progress progress-striped active">
	<div class="bar"></div>
</div>

<div id="message" class="alert" style="display:none"></div>

<div id="autoclose" class="alert alert-info" style="display:none">
	<p><?php echo \JText::_('COM_ADMINTOOLS_LBL_COMMON_AUTOCLOSEIN3S'); ?></p>
</div>