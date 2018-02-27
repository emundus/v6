<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

/** @var  \Akeeba\AdminTools\Admin\View\WebApplicationFirewall\Html  $this */

$token = $this->getContainer()->platform->getToken();
$returnUrl = base64_encode('index.php?option=com_admintools&view=' . $this->getName());

?>
<?php if (!$this->pluginExists): ?>
    <p class="akeeba-block--failure small">
        <a class="close" data-dismiss="alert" href="#">×</a>
        <?php echo \JText::_('COM_ADMINTOOLS_ERR_CONFIGUREWAF_NOPLUGINEXISTS'); ?>
    </p>
<?php elseif (!$this->pluginActive): ?>
    <p class="akeeba-block--failure small">
        <a class="close" data-dismiss="alert" href="#">×</a>
        <?php echo \JText::_('COM_ADMINTOOLS_ERR_CONFIGUREWAF_NOPLUGINACTIVE'); ?>
        <br/>
        <a href="index.php?option=com_plugins&client=site&filter_type=system&search=admin%20tools">
            <?php echo \JText::_('COM_ADMINTOOLS_ERR_CONFIGUREWAF_NOPLUGINACTIVE_DOIT'); ?>
        </a>
    </p>
<?php elseif ($this->isMainPhpDisabled && !empty($this->mainPhpRenamedTo)): ?>
    <p class="akeeba-block--failure small">
        <a class="close" data-dismiss="alert" href="#">×</a>
		<?php echo \JText::sprintf('COM_ADMINTOOLS_ERR_CONFIGUREWAF_MAINPHPRENAMED_KNOWN', $this->mainPhpRenamedTo); ?>
        <br/>
        <a href="index.php?option=com_admintools&view=ControlPanel&task=renameMainPhp&<?php echo $token ?>=1&returnurl=<?php echo urlencode($returnUrl); ?>">
			<?php echo \JText::_('COM_ADMINTOOLS_ERR_CONFIGUREWAF_MAINPHPRENAMED_DOIT'); ?>
        </a>
    </p>
<?php elseif ($this->isMainPhpDisabled): ?>
    <p class="akeeba-block--failure small">
        <a class="close" data-dismiss="alert" href="#">×</a>
		<?php echo \JText::_('COM_ADMINTOOLS_ERR_CONFIGUREWAF_MAINPHPRENAMED_UNKNOWN'); ?>
    </p>
<?php elseif (!$this->pluginLoaded && !$this->isRescueMode): ?>
    <p class="akeeba-block--failure small">
        <a class="close" data-dismiss="alert" href="#">×</a>
		<?php echo \JText::_('COM_ADMINTOOLS_ERR_CONFIGUREWAF_PLUGINNOTLOADED'); ?>
    </p>
<?php endif; ?>
