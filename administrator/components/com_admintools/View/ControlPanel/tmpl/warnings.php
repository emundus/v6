<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\AdminTools\Admin\View\ControlPanel\Html;
use Joomla\CMS\Language\Text;

/** @var  Html $this For type hinting in the IDE */

defined('_JEXEC') or die;

$root      = realpath(JPATH_ROOT);
$root      = trim($root);
$emptyRoot = empty($root);

echo $this->loadAnyTemplate('admin:com_admintools/ControlPanel/needsipworkarounds');

?>

<?php if (isset($this->jwarnings) && !empty($this->jwarnings)): ?>
	<div class="akeeba-block--failure">
		<h3><?php echo Text::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_JCONFIG'); ?></h3>
		<p><?php echo $this->jwarnings ?></p>
	</div>
<?php endif; ?>

<?php /* Stuck database updates warning */ ?>
<?php if ($this->stuckUpdates): ?>
	<div class="akeeba-block--failure">
		<p>
			<?php
			echo Text::sprintf('COM_ADMINTOOLS_CPANEL_ERR_UPDATE_STUCK',
				$this->container->db->getPrefix(),
				'index.php?option=com_admintools&view=ControlPanel&task=forceUpdateDb'
			) ?>
		</p>
	</div>
<?php endif; ?>

<?php if (isset($this->frontEndSecretWordIssue) && !empty($this->frontEndSecretWordIssue)): ?>
	<div class="akeeba-block--failure">
		<h3><?php echo Text::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_FESECRETWORD_HEADER'); ?></h3>
		<p><?php echo Text::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_FESECRETWORD_INTRO'); ?></p>
		<p><?php echo $this->frontEndSecretWordIssue; ?></p>
		<p>
			<?php echo Text::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_FESECRETWORD_WHATTODO_JOOMLA'); ?>
			<?php echo Text::sprintf('COM_ADMINTOOLS_ERR_CONTROLPANEL_FESECRETWORD_WHATTODO_COMMON', $this->newSecretWord); ?>
		</p>
		<p>
			<a class="akeeba-btn--green akeeba-btn--big"
			   href="index.php?option=com_admintools&view=ControlPanel&task=resetSecretWord&<?php echo $this->container->platform->getToken(true); ?>=1">
				<span class="akion-refresh"></span>
				<?php echo Text::_('COM_ADMINTOOLS_CONTROLPANEL_BTN_FESECRETWORD_RESET'); ?>
			</a>
		</p>
	</div>
<?php endif; ?>

<?php
// Obsolete PHP version check
echo $this->loadAnyTemplate('admin:com_admintools/ControlPanel/phpversion_warning', [
	'softwareName'  => 'Admin Tools',
	'minPHPVersion' => '7.1.0',
]);
?>

<?php if ($this->oldVersion): ?>
	<div class="akeeba-block--warning">
		<a class="close" data-dismiss="alert" href="#">×</a>
		<strong><?php echo Text::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_OLDVERSION'); ?></strong>
	</div>
<?php endif; ?>

<?php if ($emptyRoot): ?>
	<div class="akeeba-block--failure">
		<a class="close" data-dismiss="alert" href="#">×</a>
		<?php echo Text::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_EMPTYROOT'); ?>
	</div>
<?php endif; ?>

<?php if ($this->needsdlid): ?>
	<div class="akeeba-block--success">
		<h3>
			<?php echo Text::_('COM_ADMINTOOLS_MSG_CONTROLPANEL_MUSTENTERDLID'); ?>
		</h3>
		<p>
			<?php echo Text::sprintf('COM_ADMINTOOLS_LBL_CONTROLPANEL_NEEDSDLID', 'https://www.akeeba.com/download/official/add-on-dlid.html'); ?>
		</p>
		<form name="dlidform" action="index.php" method="post" class="akeeba-form--inline">
			<input type="hidden" name="option" value="com_admintools" />
			<input type="hidden" name="view" value="ControlPanel" />
			<input type="hidden" name="task" value="applydlid" />
			<input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1" />
			<span>
		<?php echo Text::_('COM_ADMINTOOLS_MSG_CONTROLPANEL_PASTEDLID'); ?>
	</span>
			<input type="text" name="dlid"
				   placeholder="<?php echo Text::_('COM_ADMINTOOLS_LBL_JCONFIG_DOWNLOADID'); ?>"
				   class="akeeba-input--wide">
			<button type="submit" class="akeeba-btn--green">
				<span class="akion-checkmark-round"></span>
				<?php echo Text::_('COM_ADMINTOOLS_MSG_CONTROLPANEL_APPLYDLID'); ?>
			</button>
		</form>
	</div>
<?php endif; ?>

<div id="updateNotice"></div>

<?php if ($this->serverConfigEdited): ?>
	<div class="akeeba-block--warning">
		<p><?php echo Text::_('COM_ADMINTOOLS_CPANEL_SERVERCONFIGWARN'); ?></p>

		<a href="index.php?option=com_admintools&view=ControlPanel&task=regenerateServerConfig"
		   class="akeeba-btn--green">
			<?php echo Text::_('COM_ADMINTOOLS_CPANEL_SERVERCONFIGWARN_REGENERATE') ?>
		</a>
		<a href="index.php?option=com_admintools&view=ControlPanel&task=ignoreServerConfigWarn"
		   class="akeeba-btn--dark">
			<?php echo Text::_('COM_ADMINTOOLS_CPANEL_SERVERCONFIGWARN_IGNORE') ?>
		</a>
	</div>
<?php endif; ?>
