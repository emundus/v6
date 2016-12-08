<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

/** @var  \Akeeba\AdminTools\Admin\View\ControlPanel\Html $this For type hinting in the IDE */

defined('_JEXEC') or die;

$uriBase = rtrim(JUri::base(), '/');

?>
<h2>
	<?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_HEADER_QUICKSETUP'); ?>
</h2>

<p class="small alert alert-warning">
	<?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_HEADER_QUICKSETUP_HELP'); ?>
</p>

<div>
	<a href="index.php?option=com_admintools&view=QuickStart" class="btn cpanel-icon">
		<img
				src="<?php echo $uriBase; ?>/components/com_admintools/media/images/quickstart-32.png"
				class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_QUICKSTART'); ?>"/>
	<span class="title">
		<?php echo \JText::_('COM_ADMINTOOLS_TITLE_QUICKSTART'); ?><br/>
	</span>
	</a>
</div>

<div class="clearfix"></div>