<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

// IP Workarounds are available on Pro version only
if (!defined('ADMINTOOLS_PRO') || !ADMINTOOLS_PRO)
{
	return;
}

// Let's check if we have to display the notice about IP Workarounds
$display = false;
// Prevent notices if we don't have any incoming return url
$returnurl = isset($returnurl) ? $returnurl : '';

/** @var \Akeeba\AdminTools\Admin\Model\ControlPanel $controlPanelModel */
$controlPanelModel = $this->container->factory->model('ControlPanel')->tmpInstance();
$privateNetworks   = $controlPanelModel->needsIpWorkaroundsForPrivNetwork();
$proxyHeader       = $controlPanelModel->needsIpWorkaroundsHeaders();

$display = ($privateNetworks || $proxyHeader);

// No notices detected, let's stop here
if (!$display)
{
    return;
}

?>

<div class="akeeba-block--failure">
    <?php if ($privateNetworks): ?>
        <p>
            <?php echo \JText::_('COM_ADMINTOOLS_CPANEL_ERR_PRIVNET_IPS')?>
        </p>
    <?php endif; ?>

    <?php if($proxyHeader): ?>
        <p>
		    <?php echo \JText::_('COM_ADMINTOOLS_CPANEL_ERR_PROXY_HEADER')?>
        </p>
    <?php endif; ?>
	<a href="index.php?option=com_admintools&view=ControlPanel&task=IpWorkarounds&enable=1&returnurl=<?php echo $returnurl?>" class="akeeba-btn--green">
		<?php echo \JText::_('COM_ADMINTOOLS_CPANEL_ERR_PRIVNET_ENABLE')?>
	</a>
	<a href="index.php?option=com_admintools&view=ControlPanel&task=IpWorkarounds&enable=0&returnurl=<?php echo $returnurl?>" class="akeeba-btn--dark">
		<?php echo \JText::_('COM_ADMINTOOLS_CPANEL_ERR_PRIVNET_IGNORE')?>
	</a>
</div>
