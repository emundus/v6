<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var Akeeba\AdminTools\Admin\View\ConfigureWAF\Html $this */

defined('_JEXEC') || die;

?>
<div class="akeeba-form-group">
	<label for="sqlishield"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SQLISHIELD')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SQLISHIELD_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SQLISHIELD')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'sqlishield', $this->wafconfig['sqlishield'])
</div>

<div class="akeeba-form-group">
	<label for="muashield"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_MUASHIELD')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_MUASHIELD_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_MUASHIELD')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'muashield', $this->wafconfig['muashield'])
</div>

<div class="akeeba-form-group">
	<label for="rfishield"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_RFISHIELD')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_RFISHIELD_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_RFISHIELD')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'rfishield', $this->wafconfig['rfishield'])
</div>

<div class="akeeba-form-group">
	<label for="phpshield"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_PHPSHIELD')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_PHPSHIELD_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_PHPSHIELD')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'phpshield', $this->wafconfig['phpshield'])
</div>

<div class="akeeba-form-group">
	<label for="dfishield"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DFISHIELD')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DFISHIELD_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DFISHIELD')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'dfishield', $this->wafconfig['dfishield'])
</div>

<div class="akeeba-form-group">
	<label for="uploadshield"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_UPLOADSHIELD')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_UPLOADSHIELD_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_UPLOADSHIELD')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'uploadshield', $this->wafconfig['uploadshield'])
</div>

<div class="akeeba-form-group">
	<label for="sessionshield"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SESSIONSHIELD')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SESSIONSHIELD_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SESSIONSHIELD')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'sessionshield', $this->wafconfig['sessionshield'])
</div>

<div class="akeeba-form-group">
	<label for="antispam"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ANTISPAM')"
		   data-content="@sprintf('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ANTISPAM_TIP', 'index.php?option=com_admintools&view=BadWords')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ANTISPAM')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'antispam', $this->wafconfig['antispam'])
</div>

<div class="akeeba-form-group">
	<label for="tmpl"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ALLOWED_DOMAINS')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ALLOWED_DOMAINS_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ALLOWED_DOMAINS')
	</label>

	<input type="text" size="45" name="allowed_domains" id="allowed_domains"
		   value="{{{ $this->wafconfig['allowed_domains'] }}}" />
</div>
