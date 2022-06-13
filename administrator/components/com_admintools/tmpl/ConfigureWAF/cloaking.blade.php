<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var Akeeba\AdminTools\Admin\View\ConfigureWAF\Html $this */

?>
<div class="akeeba-form-group">
	<label
			for="custgenerator"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CUSTGENERATOR')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CUSTGENERATOR_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CUSTGENERATOR')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'custgenerator', $this->wafconfig['custgenerator'])
</div>
<div class="akeeba-form-group">
	<label for="generator"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_GENERATOR')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_GENERATOR_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_GENERATOR')
	</label>

	<input type="text" size="45" id="generator" name="generator" value="{{{ $this->wafconfig['generator'] }}}">
</div>

<div class="akeeba-form-group">
	<label for="tmpl"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TMPL')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TMPL_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TMPL')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'tmpl', $this->wafconfig['tmpl'])
</div>

<div class="akeeba-form-group">
	<label for="tmplwhitelist"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TMPLWHITELIST')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TMPLWHITELIST_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TMPLWHITELIST')
	</label>

	<input type="text" size="45" name="tmplwhitelist" id="tmplwhitelist"
		   value="{{{ $this->wafconfig['tmplwhitelist'] }}}" />
</div>

<div class="akeeba-form-group">
	<label for="template"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TEMPLATE')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TEMPLATE_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TEMPLATE')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'template', $this->wafconfig['template'])
</div>

<div class="akeeba-form-group">
	<label
			for="allowsitetemplate"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ALLOWSITETEMPLATE')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ALLOWSITETEMPLATE_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ALLOWSITETEMPLATE')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'allowsitetemplate', $this->wafconfig['allowsitetemplate'])
</div>

<div class="akeeba-form-group">
	<label
			for="404shield_enable"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_404SHIELD_ENABLE')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_404SHIELD_ENABLE_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_404SHIELD_ENABLE')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', '404shield_enable', $this->wafconfig['404shield_enable'])
</div>


<div class="akeeba-form-group">
	<label
			for="404shield"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_404SHIELD')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_404SHIELD_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_404SHIELD')
	</label>

	<textarea id="404shield" name="404shield"
			  rows="5">{{{ $this->wafconfig['404shield'] }}}</textarea>
</div>
