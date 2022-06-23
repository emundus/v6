<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die;

/** @var $this  Akeeba\AdminTools\Admin\View\WebApplicationFirewall\Html */

?>
@include('admin:com_admintools/ControlPanel/plugin_warning')

<div class="akeeba-block--info">
	@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_HTACCESSTIP')
</div>

<div class="akeeba-grid akeeba-panel">
	<a href="index.php?option=com_admintools&view=ConfigureWAF" class="akeeba-action--grey">
		<span class="akion-flash"></span>
		@lang('COM_ADMINTOOLS_TITLE_WAFCONFIG')
	</a>

	<a href="index.php?option=com_admintools&view=ExceptionsFromWAFs" class="akeeba-action--teal">
		<span class="akion-funnel"></span>
		@lang('COM_ADMINTOOLS_TITLE_WAFEXCEPTIONS')
	</a>

	<a href="index.php?option=com_admintools&view=WAFBlacklistedRequests" class="akeeba-action--red">
		<span class="akion-locked"></span>
		@lang('COM_ADMINTOOLS_TITLE_WAFBLACKLISTS')
	</a>

	<a href="index.php?option=com_admintools&view=WhitelistedAddresses" class="akeeba-action--teal">
		<span class="akion-ios-paper"></span>
		@lang('COM_ADMINTOOLS_TITLE_IPWL')
	</a>

	<a href="index.php?option=com_admintools&view=BlacklistedAddresses" class="akeeba-action--red">
		<span class="akion-ios-paper-outline"></span>
		@lang('COM_ADMINTOOLS_TITLE_IPBL')
	</a>

	<a href="index.php?option=com_admintools&view=BadWords" class="akeeba-action--red">
		<span class="akion-flag"></span>
		@lang('COM_ADMINTOOLS_TITLE_BADWORDS')
	</a>

	<a href="index.php?option=com_admintools&view=SecurityExceptions" class="akeeba-action--grey">
		<span class="akion-stats-bars"></span>
		@lang('COM_ADMINTOOLS_TITLE_LOG')
	</a>

	<a href="index.php?option=com_admintools&view=AutoBannedAddresses" class="akeeba-action--red">
		<span class="akion-close-circled"></span>
		@lang('COM_ADMINTOOLS_TITLE_IPAUTOBAN')
	</a>

	<a href="index.php?option=com_admintools&view=IPAutoBanHistories" class="akeeba-action--red">
		<span class="akion-close-circled"></span>
		@lang('COM_ADMINTOOLS_TITLE_IPAUTOBANHISTORY')
	</a>

	<a href="index.php?option=com_admintools&view=UnblockIP" class="akeeba-action--orange">
		<span class="akion-unlocked"></span>
		@lang('COM_ADMINTOOLS_TITLE_UNBLOCKIP')
	</a>

	<a href="index.php?option=com_admintools&view=WAFEmailTemplates" class="akeeba-action--grey">
		<span class="akion-email"></span>
		@lang('COM_ADMINTOOLS_TITLE_WAFEMAILTEMPLATES')
	</a>
</div>
