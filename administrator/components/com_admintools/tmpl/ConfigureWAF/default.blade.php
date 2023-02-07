<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die;

/** @var Akeeba\AdminTools\Admin\View\ConfigureWAF\Html $this */

$tabclass = $this->longConfig ? '' : 'akeeba-tabs';

?>
@jhtml('formbehavior.chosen')
<form name="adminForm" id="adminForm" action="index.php" method="post" class="akeeba-form--horizontal">
	<div class="{{ $tabclass }}">
		@if($this->longConfig)
			<h4>@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_BASICSETTINGS')</h4>
		@else
			<label for="base" class="active">
				@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_BASICSETTINGS')
			</label>
		@endif
		<section id="base">
			@include('admin:com_admintools/ConfigureWAF/basic')
		</section>

		@if($this->longConfig)
			<h4>@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_ACTIVEFILTERING')</h4>
		@else
			<label for="activefiltering">
				@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_ACTIVEFILTERING')
			</label>
		@endif
		<section id="activefiltering">
			@include('admin:com_admintools/ConfigureWAF/requestfiltering')
		</section>

		@if($this->longConfig)
			<h4>@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_JHARDENING')</h4>
		@else
			<label for="jhardening">
				@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_JHARDENING')
			</label>
		@endif
		<section id="jhardening">
			@include('admin:com_admintools/ConfigureWAF/hardening')
		</section>

		@if($this->longConfig)
			<h4>@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_FINGERPRINTING')</h4>
		@else
			<label for="fingerprinting">
				@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_FINGERPRINTING')
			</label>
		@endif
		<section id="fingerprinting">
			@include('admin:com_admintools/ConfigureWAF/cloaking')
		</section>

		@if($this->longConfig)
			<h4>@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_PROJECTHONEYPOT')</h4>
		@else
			<label for="projecthoneypot">
				@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_PROJECTHONEYPOT')
			</label>
		@endif
		<section id="projecthoneypot">
			@include('admin:com_admintools/ConfigureWAF/projecthoneypot')
		</section>

		@if($this->longConfig)
			<h4>@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_EXCEPTIONS')</h4>
		@else
			<label for="exceptions">
				@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_EXCEPTIONS')
			</label>
		@endif
		<section id="exceptions">
			@include('admin:com_admintools/ConfigureWAF/exceptions')
		</section>

		@if($this->longConfig)
			<h4>@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSR')</h4>
		@else
			<label for="tsr">
				@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSR')
			</label>
		@endif
		<section id="tsr">
			@include('admin:com_admintools/ConfigureWAF/tsr')
		</section>

		@if($this->longConfig)
			<h4>@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_LOGGINGANDREPORTING')</h4>
		@else
			<label for="logging">
				@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_LOGGINGANDREPORTING')
			</label>
		@endif
		<section id="logging">
			@include('admin:com_admintools/ConfigureWAF/logging')
		</section>

		@if($this->longConfig)
			<h4>@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_CUSTOMMESSAGE_HEADER')</h4>
		@else
			<label for="custom">
				@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_CUSTOMMESSAGE_HEADER')
			</label>
		@endif
		<section id="custom">
			@include('admin:com_admintools/ConfigureWAF/custom')
		</section>
	</div>

	<input type="hidden" name="option" value="com_admintools" />
	<input type="hidden" name="view" value="ConfigureWAF" />
	<input type="hidden" name="task" value="save" />
	<input type="hidden" name="@token(true)" value="1" />
</form>
