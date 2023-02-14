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
			for="neverblockips"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_NEVERBLOCKIPS')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_NEVERBLOCKIPS_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_NEVERBLOCKIPS')
	</label>

	<input type="text" size="50" name="neverblockips" id="neverblockips"
		   value="{{{ $this->wafconfig['neverblockips'] }}}" />
</div>

<div class="akeeba-form-group">
	<label
			for="whitelist_domains"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_WHITELIST_DOMAINS')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_WHITELIST_DOMAINS_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_WHITELIST_DOMAINS')
	</label>

	<input type="text" name="whitelist_domains" id="whitelist_domains"
		   value="{{{ $this->wafconfig['whitelist_domains'] }}}">
</div>
