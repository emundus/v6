<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\AdminTools\Admin\View\WebApplicationFirewall\Html;
use Joomla\CMS\Language\Text;

defined('_JEXEC') || die;

/** @var  Html $this */

$returnUrl = base64_encode('index.php?option=com_admintools&view=' . $this->getName());

?>
@if(!$this->pluginExists)
	<p class="akeeba-block--failure small">
		@lang('COM_ADMINTOOLS_ERR_CONFIGUREWAF_NOPLUGINEXISTS')
	</p>
@elseif (!$this->pluginActive)
	<p class="akeeba-block--failure small">
		@lang('COM_ADMINTOOLS_ERR_CONFIGUREWAF_NOPLUGINACTIVE')
		<br />
		<a href="index.php?option=com_plugins&client=site&filter_type=system&search=admin%20tools">
			@lang('COM_ADMINTOOLS_ERR_CONFIGUREWAF_NOPLUGINACTIVE_DOIT')
		</a>
	</p>
@elseif ($this->isMainPhpDisabled && !empty($this->mainPhpRenamedTo))
	<p class="akeeba-block--failure small">
		@sprintf('COM_ADMINTOOLS_ERR_CONFIGUREWAF_MAINPHPRENAMED_KNOWN', $this->mainPhpRenamedTo)
		<br />
		<a href="index.php?option=com_admintools&view=ControlPanel&task=renameMainPhp&@token()=1&returnurl={{ urlencode($returnUrl) }}">
			@lang('COM_ADMINTOOLS_ERR_CONFIGUREWAF_MAINPHPRENAMED_DOIT')
		</a>
	</p>
@elseif ($this->isMainPhpDisabled)
	<p class="akeeba-block--failure small">
		@lang('COM_ADMINTOOLS_ERR_CONFIGUREWAF_MAINPHPRENAMED_UNKNOWN')
	</p>
@elseif (!$this->pluginLoaded && !$this->isRescueMode)
	<p class="akeeba-block--failure small">
		@lang('COM_ADMINTOOLS_ERR_CONFIGUREWAF_PLUGINNOTLOADED')
	</p>
@endif
