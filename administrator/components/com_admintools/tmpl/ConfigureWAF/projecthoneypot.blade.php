<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var Akeeba\AdminTools\Admin\View\ConfigureWAF\Html $this */
?>
<div class="akeeba-form-group">
	<label
			for="httpblenable"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLENABLE')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLENABLE_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLENABLE')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'httpblenable', $this->wafconfig['httpblenable'])
</div>

<div class="akeeba-form-group">
	<label for="bbhttpblkey"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_BBHTTPBLKEY')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_BBHTTPBLKEY_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_BBHTTPBLKEY')
	</label>

	<input type="text" size="45" name="bbhttpblkey" id="bbhttpblkey"
		   value="{{{ $this->wafconfig['bbhttpblkey'] }}}" />
</div>

<div class="akeeba-form-group">
	<label
			for="httpblthreshold"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLTHRESHOLD')"
			data-content="{{ str_replace('"', '&quot;', \Joomla\CMS\Language\Text::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLTHRESHOLD_TIP')) }}">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLTHRESHOLD')
	</label>

	<input type="text" size="5" name="httpblthreshold"
		   value="{{{ $this->wafconfig['httpblthreshold'] }}}" />
</div>

<div class="akeeba-form-group">
	<label
			for="httpblmaxage"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLMAXAGE')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLMAXAGE_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLMAXAGE')
	</label>

	<input type="text" size="5" name="httpblmaxage"
		   value="{{{ $this->wafconfig['httpblmaxage'] }}}" />
</div>

<div class="akeeba-form-group">
	<label
			for="httpblblocksuspicious"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLBLOCKSUSPICIOUS')"
			data-content="{{ str_replace('"', '&quot;', \Joomla\CMS\Language\Text::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLBLOCKSUSPICIOUS_TIP')) }}">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLBLOCKSUSPICIOUS')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'httpblblocksuspicious', $this->wafconfig['httpblblocksuspicious'])
</div>
