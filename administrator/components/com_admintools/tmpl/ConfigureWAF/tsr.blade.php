<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var Akeeba\AdminTools\Admin\View\ConfigureWAF\Html $this */

use Akeeba\AdminTools\Admin\Helper\Select;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') || die;

?>
<div class="akeeba-form-group">
	<label for="tsrenable"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSRENABLE')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSRENABLE_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSRENABLE')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'tsrenable', $this->wafconfig['tsrenable'])
</div>

<div class="akeeba-form-group">
	<label
			for="emailafteripautoban"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_EMAILAFTERIPAUTOBAN')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_EMAILAFTERIPAUTOBAN_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_EMAILAFTERIPAUTOBAN')
	</label>

	<input type="text" size="50" name="emailafteripautoban"
		   value="{{{ $this->wafconfig['emailafteripautoban'] }}}" />
</div>

<div class="akeeba-form-group">
	<label for="tsrstrikes"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSRSTRIKES')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSRSTRIKES_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSRSTRIKES')
	</label>

	<div class="akeeba-form--inline">
		<input type="text" size="5" name="tsrstrikes"
			   value="{{{ $this->wafconfig['tsrstrikes'] }}}" />
		<span>@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSRNUMFREQ')</span>
		<input type="text" size="5" name="tsrnumfreq"
			   value="{{{ $this->wafconfig['tsrnumfreq'] }}}" />
		{{ Select::trsfreqlist('tsrfrequency', [], $this->wafconfig['tsrfrequency']) }}
	</div>
</div>

<div class="akeeba-form-group">
	<label for="tsrbannum"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSRBANNUM')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSRBANNUM_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSRBANNUM')
	</label>

	<div class="akeeba-form--inline">
		<input class="input-mini" type="text" size="5" name="tsrbannum"
			   value="{{{ $this->wafconfig['tsrbannum'] }}}" />
		&nbsp;
		{{ Select::trsfreqlist('tsrbanfrequency', [], $this->wafconfig['tsrbanfrequency']) }}

	</div>
</div>

<div class="akeeba-form-group">
	<label for="tsrpermaban"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_PERMABAN')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_PERMABAN_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_PERMABAN')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'permaban', $this->wafconfig['permaban'])
</div>

<div class="akeeba-form-group">
	<label for="permabannum"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_PERMABANNUM')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_PERMABANNUM_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_PERMABANNUM')
	</label>

	<div>
		<input class="input-mini" type="text" size="5" name="permabannum"
			   value="{{{ $this->wafconfig['permabannum'] }}}" />
		<span>@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_PERMABANNUM_2')</span>
	</div>
</div>

<div class="akeeba-form-group">
	<label
			for="spammermessage"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_SPAMMERMESSAGE')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_SPAMMERMESSAGE_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_SPAMMERMESSAGE')
	</label>

	<input type="text" name="spammermessage" value="{{ htmlentities($this->wafconfig['spammermessage']) }}" />
</div>
