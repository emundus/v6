<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var  \Akeeba\AdminTools\Admin\View\ControlPanel\Html $this For type hinting in the IDE */

defined('_JEXEC') or die;

use FOF30\Date\Date;

$root = realpath(JPATH_ROOT);
$root = trim($root);
$emptyRoot = empty($root);

echo $this->loadAnyTemplate('admin:com_admintools/ControlPanel/needsipworkarounds');

?>

<?php if (isset($this->jwarnings) && !empty($this->jwarnings)): ?>
	<div class="akeeba-block--failure">
		<h3><?php echo \JText::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_JCONFIG'); ?></h3>
		<p><?php echo $this->jwarnings?></p>
	</div>
<?php endif; ?>

<?php /* Stuck database updates warning */?>
<?php if ($this->stuckUpdates):?>
	<div class="akeeba-block--failure">
		<p>
			<?php
			echo \JText::sprintf('COM_ADMINTOOLS_CPANEL_ERR_UPDATE_STUCK',
				$this->container->db->getPrefix(),
				'index.php?option=com_admintools&view=ControlPanel&task=forceUpdateDb'
			)?>
		</p>
	</div>
<?php endif;?>

<?php if (isset($this->frontEndSecretWordIssue) && !empty($this->frontEndSecretWordIssue)): ?>
	<div class="akeeba-block--failure">
		<h3><?php echo \JText::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_FESECRETWORD_HEADER'); ?></h3>
		<p><?php echo \JText::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_FESECRETWORD_INTRO'); ?></p>
		<p><?php echo $this->frontEndSecretWordIssue; ?></p>
		<p>
			<?php echo \JText::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_FESECRETWORD_WHATTODO_JOOMLA'); ?>
			<?php echo JText::sprintf('COM_ADMINTOOLS_ERR_CONTROLPANEL_FESECRETWORD_WHATTODO_COMMON', $this->newSecretWord); ?>
		</p>
		<p>
			<a class="akeeba-btn--green akeeba-btn--big"
			   href="index.php?option=com_admintools&view=ControlPanel&task=resetSecretWord&<?php echo $this->container->platform->getToken(true); ?>=1">
				<span class="akion-refresh"></span>
				<?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_BTN_FESECRETWORD_RESET'); ?>
			</a>
		</p>
	</div>
<?php endif; ?>

<?php
// Obsolete PHP version check
if (version_compare(PHP_VERSION, '5.5.0', 'lt')):
	JLoader::import('joomla.utilities.date');
	$akeebaCommonDatePHP = new Date('2015-09-03 00:00:00', 'GMT');
	$akeebaCommonDateObsolescence = new Date('2016-06-03 00:00:00', 'GMT');
	?>
	<div id="phpVersionCheck" class="akeeba-block--warning">
		<h3><?php echo \JText::_('AKEEBA_COMMON_PHPVERSIONTOOOLD_WARNING_TITLE'); ?></h3>
		<p>
			<?php echo JText::sprintf(
				'AKEEBA_COMMON_PHPVERSIONTOOOLD_WARNING_BODY',
				PHP_VERSION,
				$akeebaCommonDatePHP->format(JText::_('DATE_FORMAT_LC1')),
				$akeebaCommonDateObsolescence->format(JText::_('DATE_FORMAT_LC1')),
				'5.6'
			);
			?>
		</p>
	</div>
<?php endif; ?>

<?php if ($this->oldVersion): ?>
	<div class="akeeba-block--warning">
		<a class="close" data-dismiss="alert" href="#">×</a>
		<strong><?php echo \JText::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_OLDVERSION'); ?></strong>
	</div>
<?php endif; ?>

<?php if ($emptyRoot): ?>
	<div class="akeeba-block--failure">
		<a class="close" data-dismiss="alert" href="#">×</a>
		<?php echo \JText::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_EMPTYROOT'); ?>
	</div>
<?php endif; ?>

<?php if ($this->needsdlid): ?>
	<div class="akeeba-block--success">
		<h3>
			<?php echo \JText::_('COM_ADMINTOOLS_MSG_CONTROLPANEL_MUSTENTERDLID'); ?>
		</h3>
		<p>
			<?php echo JText::sprintf('COM_ADMINTOOLS_LBL_CONTROLPANEL_NEEDSDLID','https://www.akeebabackup.com/instructions/1436-admin-tools-download-id.html'); ?>
		</p>
		<form name="dlidform" action="index.php" method="post" class="akeeba-form--inline">
			<input type="hidden" name="option" value="com_admintools" />
			<input type="hidden" name="view" value="ControlPanel" />
			<input type="hidden" name="task" value="applydlid" />
			<input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1" />
	<span>
		<?php echo \JText::_('COM_ADMINTOOLS_MSG_CONTROLPANEL_PASTEDLID'); ?>
	</span>
			<input type="text" name="dlid" placeholder="<?php echo \JText::_('COM_ADMINTOOLS_LBL_JCONFIG_DOWNLOADID'); ?>" class="akeeba-input--wide">
			<button type="submit" class="akeeba-btn--green">
				<span class="akion-checkmark-round"></span>
				<?php echo \JText::_('COM_ADMINTOOLS_MSG_CONTROLPANEL_APPLYDLID'); ?>
			</button>
		</form>
	</div>
<?php endif; ?>

<div id="updateNotice"></div>

<?php if ($this->isPro && !$this->hasplugin): ?>
	<div class="akeeba-block--info">
		<h3><?php echo \JText::_('COM_ADMINTOOLS_LBL_GEOGRAPHICBLOCKING_GEOIPPLUGINSTATUS'); ?></h3>

		<p><?php echo \JText::_('COM_ADMINTOOLS_LBL_GEOGRAPHICBLOCKING_GEOIPPLUGINMISSING'); ?></p>

		<a class="akeeba-btn--primary--small" href="https://www.akeebabackup.com/download/akgeoip.html" target="_blank">
			<span class="akion-ios-download-outline"></span>
			<?php echo \JText::_('COM_ADMINTOOLS_LBL_GEOGRAPHICBLOCKING_DOWNLOADGEOIPPLUGIN'); ?>
		</a>
	</div>
<?php elseif ($this->isPro && $this->pluginNeedsUpdate): ?>
	<div class="akeeba-block--info">
		<h3><?php echo \JText::_('COM_ADMINTOOLS_LBL_GEOGRAPHICBLOCKING_GEOIPPLUGINEXISTS'); ?></h3>

		<p><?php echo \JText::_('COM_ADMINTOOLS_LBL_GEOGRAPHICBLOCKING_GEOIPPLUGINCANUPDATE'); ?></p>

		<a class="akeeba-btn--dark--small"
		   href="index.php?option=com_admintools&view=ControlPanel&task=updategeoip&<?php echo $this->container->platform->getToken(true); ?>=1">
			<span class="akion-refresh"></span>
			<?php echo \JText::_('COM_ADMINTOOLS_LBL_GEOGRAPHICBLOCKING_UPDATEGEOIPDATABASE'); ?>
		</a>
	</div>
<?php endif; ?>
