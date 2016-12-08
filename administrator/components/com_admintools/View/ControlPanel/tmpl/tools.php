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
	<?php echo \JText::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_TOOLS'); ?>
</h2>

<div>

	<?php if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN'): ?>
		<a href="index.php?option=com_admintools&view=ConfigureFixPermissions" class="btn cpanel-icon">
			<img
					src="<?php echo $uriBase; ?>/components/com_admintools/media/images/fixpermsconfig-32.png"
					class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_FIXPERMSCONFIG'); ?>"/>
		<span class="title">
			<?php echo \JText::_('COM_ADMINTOOLS_TITLE_FIXPERMSCONFIG'); ?><br/>
		</span>
		</a>

		<?php if ($this->enable_fixperms): ?>
			<a href="index.php?option=com_admintools&view=FixPermissions&tmpl=component" class="btn cpanel-icon modal"
			   rel="{handler: 'iframe', size: {x: 600, y: 250}}">
				<img
						src="<?php echo $uriBase; ?>/components/com_admintools/media/images/fixperms-32.png"
						class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_FIXPERMS'); ?>"/>
			<span class="title">
				<?php echo \JText::_('COM_ADMINTOOLS_TITLE_FIXPERMS'); ?><br/>
			</span>
			</a>
		<?php endif; ?>
	<?php endif; ?>

	<a href="index.php?option=com_admintools&view=SEOAndLinkTools" class="btn cpanel-icon">
		<img
				src="<?php echo $uriBase; ?>/components/com_admintools/media/images/seoandlink-32.png"
				class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_SEOANDLINK'); ?>"/>
	<span class="title">
		<?php echo \JText::_('COM_ADMINTOOLS_TITLE_SEOANDLINK'); ?><br/>
	</span>
	</a>

	<?php if ($this->enable_cleantmp): ?>
		<a href="index.php?option=com_admintools&view=CleanTempDirectory&tmpl=component" class="modal btn cpanel-icon"
		   rel="{handler: 'iframe', size: {x: 600, y: 250}}">
			<img
					src="<?php echo $uriBase; ?>/components/com_admintools/media/images/cleantmp-32.png"
					class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_CLEANTMP'); ?>"/>
		<span class="title">
			<?php echo \JText::_('COM_ADMINTOOLS_TITLE_CLEANTMP'); ?><br/>
		</span>
		</a>
	<?php endif; ?>

	<?php if ($this->enable_tmplogcheck): ?>
		<a href="index.php?option=com_admintools&view=CheckTempAndLogDirectories&tmpl=component" class="modal btn cpanel-icon"
		   rel="{handler: 'iframe', size: {x: 600, y: 250}}">
			<img
					src="<?php echo $uriBase; ?>/components/com_admintools/media/images/scans-32.png"
					class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_CLEANTMP'); ?>"/>
		<span class="title">
			<?php echo \JText::_('COM_ADMINTOOLS_TITLE_TMPLOGCHECK'); ?><br/>
		</span>
		</a>
	<?php endif; ?>

	<?php if ($this->enable_dbchcol && $this->isMySQL): ?>
		<a href="index.php?option=com_admintools&view=ChangeDBCollation" class="btn cpanel-icon">
			<img
					src="<?php echo $uriBase; ?>/components/com_admintools/media/images/dbchcol-32.png"
					class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_CHANGEDBCOLLATION'); ?>"/>
		<span class="title">
			<?php echo \JText::_('COM_ADMINTOOLS_CHANGEDBCOLLATION'); ?><br/>
		</span>
		</a>
	<?php endif; ?>

	<?php if ($this->enable_dbtools && $this->isMySQL): ?>
		<a href="index.php?option=com_admintools&view=DatabaseTools&task=optimize&tmpl=component" class="modal btn cpanel-icon"
		   rel="{handler: 'iframe', size: {x: 600, y: 250}}">
			<img
					src="<?php echo $uriBase; ?>/components/com_admintools/media/images/dbtools-optimize-32.png"
					class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_LBL_DATABASETOOLS_OPTIMIZEDB'); ?>"/>
		<span class="title">
			<?php echo \JText::_('COM_ADMINTOOLS_LBL_DATABASETOOLS_OPTIMIZEDB'); ?><br/>
		</span>
		</a>
	<?php endif; ?>

	<?php if ($this->enable_cleantmp && $this->isMySQL): ?>
		<a href="index.php?option=com_admintools&view=DatabaseTools&task=purgesessions" id="optimize" class="btn cpanel-icon">
			<img
					src="<?php echo $uriBase; ?>/components/com_admintools/media/images/dbtools-32.png"
					class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_LBL_DATABASETOOLS_PURGESESSIONS'); ?>"/>
		<span class="title">
			<?php echo \JText::_('COM_ADMINTOOLS_LBL_DATABASETOOLS_PURGESESSIONS'); ?><br/>
		</span>
		</a>
	<?php endif; ?>

	<a href="index.php?option=com_admintools&view=Redirections" class="btn cpanel-icon">
		<img
				src="<?php echo $uriBase; ?>/components/com_admintools/media/images/redirs-32.png"
				class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_REDIRS'); ?>"/>
	<span class="title">
		<?php echo \JText::_('COM_ADMINTOOLS_TITLE_REDIRS'); ?><br/>
	</span>
	</a>

	<?php if ($this->isPro): ?>
		<a href="index.php?option=com_plugins&task=plugin.edit&extension_id=<?php echo (int) $this->pluginid; ?>" target="_blank" class="btn cpanel-icon">
			<img
					src="<?php echo $uriBase; ?>/components/com_admintools/media/images/scheduling-32.png"
					class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_SCHEDULING'); ?>"/>
		<span class="title">
			<?php echo \JText::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_SCHEDULING'); ?><br/>
		</span>
		</a>

		<a href="index.php?option=com_admintools&view=ImportAndExport&task=export" class="btn cpanel-icon">
			<img
					src="<?php echo $uriBase; ?>/components/com_admintools/media/images/export-32.png"
					class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_EXPORT_SETTINGS'); ?>"/>
		<span class="title">
			<?php echo \JText::_('COM_ADMINTOOLS_TITLE_EXPORT_SETTINGS'); ?><br/>
		</span>
		</a>

		<a href="index.php?option=com_admintools&view=ImportAndExport&task=import" class="btn cpanel-icon">
			<img
					src="<?php echo $uriBase; ?>/components/com_admintools/media/images/import-32.png"
					class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_IMPORT_SETTINGS'); ?>"/>
		<span class="title">
			<?php echo \JText::_('COM_ADMINTOOLS_TITLE_IMPORT_SETTINGS'); ?><br/>
		</span>
		</a>
	<?php endif; ?>
</div>

<div class="clearfix"></div>