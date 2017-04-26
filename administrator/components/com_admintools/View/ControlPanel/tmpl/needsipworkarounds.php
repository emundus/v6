<?php
/**
 * @package   AdminTools
 * @copyright 2010-2017 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// IP Workarounds are available on Pro version only
if (!defined('ADMINTOOLS_PRO') || !ADMINTOOLS_PRO)
{
	return;
}

/** @var \Akeeba\AdminTools\Admin\Model\ControlPanel $controlPanelModel */
$controlPanelModel    = $this->container->factory->model('ControlPanel')->tmpInstance();
$needsIpWorkarounds   = $controlPanelModel->needsIpWorkarounds();

if (!$needsIpWorkarounds)
{
    return;
}

// Prevent notices if we don't have any incoming return url
if (!isset($returnurl))
{
    $returnurl = '';
}
?>

<div class="alert alert-danger">
	<p>
		<?php
		echo \JText::_('COM_ADMINTOOLS_CPANEL_ERR_PRIVNET_IPS')?>
	</p>
	<a href="index.php?option=com_admintools&view=ControlPanel&task=IpWorkarounds&enable=1&returnurl=<?php echo $returnurl?>" class="btn btn-success">
		<?php echo \JText::_('COM_ADMINTOOLS_CPANEL_ERR_PRIVNET_ENABLE')?>
	</a>
	<a href="index.php?option=com_admintools&view=ControlPanel&task=IpWorkarounds&enable=0&returnurl=<?php echo $returnurl?>" class="btn btn-inverse">
		<?php echo \JText::_('COM_ADMINTOOLS_CPANEL_ERR_PRIVNET_IGNORE')?>
	</a>
</div>
