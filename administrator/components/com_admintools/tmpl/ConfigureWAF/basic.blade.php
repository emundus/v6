<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var Akeeba\AdminTools\Admin\View\ConfigureWAF\Html $this */

use FOF40\Date\Date;

$serverTZName = $this->container->platform->getConfig()->get('offset', 'UTC');

try
{
	$timezone = new DateTimeZone($serverTZName);
}
catch (Exception $e)
{
	$timezone = new DateTimeZone('UTC');
}

$date = new Date('now');
$date->setTimezone($timezone);
$timezoneName = $date->format('T', true);

?>
<div class="akeeba-form-group">
	<label for="ipworkarounds"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_CONFIGUREWAF_OPT_IPWORKAROUNDS')"
		   data-content="@lang('COM_ADMINTOOLS_CONFIGUREWAF_IPWORKAROUNDS_TIP')"
	>
		@lang('COM_ADMINTOOLS_CONFIGUREWAF_OPT_IPWORKAROUNDS')
	</label>

	{{ \Akeeba\AdminTools\Admin\Helper\Select::ipworkarounds('ipworkarounds', '', $this->wafconfig['ipworkarounds']) }}
</div>

<div class="akeeba-form-group">
	<label for="ipwl"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_IPWL')"
		   data-content="@sprintf('COM_ADMINTOOLS_CONFIGUREWAF_IPWL_TIP', 'index.php?option=com_admintools&view=WhitelistedAddresses')"
	>
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_IPWL')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'ipwl', $this->wafconfig['ipwl'])
</div>

<div class="akeeba-form-group">
	<label for="ipbl"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_IPBL')"
		   data-content="@sprintf('COM_ADMINTOOLS_CONFIGUREWAF_IPBL_TIP', 'index.php?option=com_admintools&view=BlacklistedAddresses')"
	>
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_IPBL')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'ipbl', $this->wafconfig['ipbl'])
</div>

<div class="akeeba-form-group">
	<label for="adminpw"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINPW')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINPW_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINPW')
	</label>

	<input type="text" size="20" name="adminpw" value="{{{ $this->wafconfig['adminpw'] }}}" />
</div>

<div class="akeeba-form-group">
	<label for="selfprotect"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SELFPROTECT')"
		   data-content="@sprintf('COM_ADMINTOOLS_CONFIGUREWAF_SELFPROTECT_TIP', 'plugins/system/admintools')"
	>
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SELFPROTECT')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'selfprotect', $this->wafconfig['selfprotect'])
</div>

<div class="akeeba-form-group">
	<label for="awayschedule_from"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_AWAYSCHEDULE')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_AWAYSCHEDULE_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_AWAYSCHEDULE')
	</label>

	<div class="akeeba-form--inline">
		@sprintf('COM_ADMINTOOLS_LBL_CONFIGUREWAF_AWAYSCHEDULE_FROM', $timezoneName)
		<input type="text" name="awayschedule_from" id="awayschedule_from" class="input-mini"
			   value="{{ $this->wafconfig['awayschedule_from'] }}" />
		@sprintf('COM_ADMINTOOLS_LBL_CONFIGUREWAF_AWAYSCHEDULE_TO', $timezoneName)
		<input type="text" name="awayschedule_to" id="awayschedule_to" class="input-mini"
			   value="{{{ $this->wafconfig['awayschedule_to'] }}}" />

		<div class="akeeba-block--info" style="margin-top: 10px">
			@sprintf('COM_ADMINTOOLS_LBL_CONFIGUREWAF_AWAYSCHEDULE_TIMEZONE', $date->format('H:i T', true), $serverTZName)
		</div>
	</div>
</div>

<div class="akeeba-block--warning">
	<h3>
		@lang('COM_ADMINTOOLS_CONFIGUREWAF_CUSTOMADMIN_NOTICE_HEAD')
	</h3>
	<p>
		@lang('COM_ADMINTOOLS_CONFIGUREWAF_CUSTOMADMIN_NOTICE_TEXT')
	</p>
	<?php
	$sefRewriteDisabled = !$this->container->platform->getConfig()->get('sef') || !$this->container->platform->getConfig()->get('sef_rewrite');
	?>

	<div class="akeeba-form-group">
		<label for="adminlogindir"
			   rel="akeeba-sticky-tooltip"
			   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINLOGINFOLDER')"
			   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINLOGINFOLDER_TIP')">
			@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINLOGINFOLDER')
		</label>

		<div>
			<input type="text" {{ $sefRewriteDisabled ? 'disabled="true" ' : '' }}size="20" name="adminlogindir"
				   value="{{{ $this->wafconfig['adminlogindir'] }}}" />
			@if($sefRewriteDisabled)
			<div class="akeeba-block--warning" style="margin: 10px 0 0">
				@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINLOGINFOLDER_ALERT')
			</div>
			@endif
		</div>
	</div>
</div>
