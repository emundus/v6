<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\AdminTools\Admin\Model\ControlPanel;

// IP Workarounds are available on Pro version only
if (!defined('ADMINTOOLS_PRO') || !ADMINTOOLS_PRO)
{
	return;
}

// Let's check if we have to display the notice about IP Workarounds
$display = false;
// Prevent notices if we don't have any incoming return url
$returnurl = $returnurl ?? '';

/** @var ControlPanel $controlPanelModel */
$controlPanelModel = $this->container->factory->model('ControlPanel')->tmpInstance();
$privateNetworks   = $controlPanelModel->needsIpWorkaroundsForPrivNetwork();
$proxyHeader       = $controlPanelModel->needsIpWorkaroundsHeaders();
$display           = ($privateNetworks || $proxyHeader);

// No notices detected, let's stop here
if (!$display)
{
	return;
}

?>
<div class="akeeba-block--failure">
	@if ($privateNetworks)
		<p>@lang('COM_ADMINTOOLS_CPANEL_ERR_PRIVNET_IPS')</p>
	@endif

	@if ($proxyHeader)
		<p>@lang('COM_ADMINTOOLS_CPANEL_ERR_PROXY_HEADER')</p>
	@endif
	<a href="index.php?option=com_admintools&view=ControlPanel&task=IpWorkarounds&enable=1&returnurl={{ $returnurl }}"
	   class="akeeba-btn--green">
		@lang('COM_ADMINTOOLS_CPANEL_ERR_PRIVNET_ENABLE')
	</a>
	<a href="index.php?option=com_admintools&view=ControlPanel&task=IpWorkarounds&enable=0&returnurl={{ $returnurl }}"
	   class="akeeba-btn--dark">
		@lang('COM_ADMINTOOLS_CPANEL_ERR_PRIVNET_IGNORE')
	</a>
</div>
