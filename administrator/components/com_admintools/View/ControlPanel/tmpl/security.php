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

<h2><?php echo \JText::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_SECURITY'); ?></h2>

<div>

	<?php if (ADMINTOOLS_PRO && $this->needsQuickSetup): ?>
		<a href="index.php?option=com_admintools&view=QuickStart" class="btn cpanel-icon">
			<img
					src="<?php echo $uriBase; ?>/components/com_admintools/media/images/quickstart-32.png"
					class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_QUICKSTART'); ?>"/>
			<span class="title">
				<?php echo \JText::_('COM_ADMINTOOLS_TITLE_QUICKSTART'); ?><br/>
			</span>
		</a>
	<?php endif; ?>

	<?php if ($this->htMakerSupported): ?>
		<a href="index.php?option=com_admintools&view=EmergencyOffline" class="btn cpanel-icon">
			<img class="at-icon" src="<?php echo $uriBase; ?>/components/com_admintools/media/images/eom-32.png"
				 alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_EOM'); ?>"/>
		<span class="title">
			<?php echo \JText::_('COM_ADMINTOOLS_TITLE_EOM'); ?><br/>
		</span>
		</a>
	<?php endif; ?>

	<a href="index.php?option=com_admintools&view=MasterPassword" class="btn cpanel-icon">
		<img src="<?php echo $uriBase; ?>/components/com_admintools/media/images/wafconfig-32.png"
			 class="at-icon"
			 alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_MASTERPW'); ?>"/>
	<span class="title">
		<?php echo \JText::_('COM_ADMINTOOLS_TITLE_MASTERPW'); ?><br/>
	</span>
	</a>

	<?php if ($this->htMakerSupported): ?>
		<a href="index.php?option=com_admintools&view=AdminPassword" class="btn cpanel-icon">
			<img
					src="<?php echo $uriBase; ?>/components/com_admintools/media/images/adminpw-<?php echo $this->adminLocked ? 'locked' : 'unlocked'; ?>-32.png"
					class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_ADMINPW'); ?>"/>
		<span class="title">
			<?php echo \JText::_('COM_ADMINTOOLS_TITLE_ADMINPW'); ?><br/>
		</span>
		</a>
	<?php endif; ?>

	<?php if ($this->isPro): ?>
		<?php if ($this->htMakerSupported): ?>
			<a href="index.php?option=com_admintools&view=HtaccessMaker" class="btn cpanel-icon">
				<img
						src="<?php echo $uriBase; ?>/components/com_admintools/media/images/htmaker-32.png"
						class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_HTMAKER'); ?>"/>
			<span class="title">
				<?php echo \JText::_('COM_ADMINTOOLS_TITLE_HTMAKER'); ?><br/>
			</span>
			</a>
		<?php endif; ?>

		<?php if ($this->nginxMakerSupported): ?>
			<a href="index.php?option=com_admintools&view=NginXConfMaker" class="btn cpanel-icon">
				<img
						src="<?php echo $uriBase; ?>/components/com_admintools/media/images/htmaker-32.png"
						class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_NGINXMAKER'); ?>"/>
			<span class="title">
				<?php echo \JText::_('COM_ADMINTOOLS_TITLE_NGINXMAKER'); ?><br/>
			</span>
			</a>
		<?php endif; ?>

		<?php if ($this->webConfMakerSupported): ?>
			<a href="index.php?option=com_admintools&view=WebConfigMaker" class="btn cpanel-icon">
				<img
						src="<?php echo $uriBase; ?>/components/com_admintools/media/images/htmaker-32.png"
						class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_WCMAKER'); ?>"/>
			<span class="title">
				<?php echo \JText::_('COM_ADMINTOOLS_TITLE_WCMAKER'); ?><br/>
			</span>
			</a>
		<?php endif; ?>

		<a href="index.php?option=com_admintools&view=WebApplicationFirewall" class="btn cpanel-icon">
			<img
					src="<?php echo $uriBase; ?>/components/com_admintools/media/images/waf-32.png"
					class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_WAF'); ?>"/>
		<span class="title">
			<?php echo \JText::_('COM_ADMINTOOLS_TITLE_WAF'); ?><br/>
		</span>
		</a>

		<a href="index.php?option=com_admintools&view=Scans" class="btn cpanel-icon">
			<img
					src="<?php echo $uriBase; ?>/components/com_admintools/media/images/scans-32.png"
					class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_SCANS'); ?>"/>
		<span class="title">
			<?php echo \JText::_('COM_ADMINTOOLS_TITLE_SCANS'); ?><br/>
		</span>
		</a>

		<a href="index.php?option=com_admintools&view=SchedulingInformation" class="btn cpanel-icon">
			<img
					src="<?php echo $uriBase; ?>/components/com_admintools/media/images/scheduling-32.png"
					class="at-icon" alt="<?php echo \JText::_('COM_ADMINTOOLS_TITLE_SCHEDULINGINFORMATION'); ?>"/>
		<span class="title">
			<?php echo \JText::_('COM_ADMINTOOLS_TITLE_SCHEDULINGINFORMATION'); ?><br/>
		</span>
		</a>
	<?php endif; ?>

</div>

<div class="clearfix"></div>