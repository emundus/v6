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
	<label for="custom403msg"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_CUSTOMMESSAGE_LABEL')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_CUSTOMMESSAGE_DESC')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_CUSTOMMESSAGE_LABEL')
	</label>

	<input type="text" name="custom403msg" value="{{ htmlentities($this->wafconfig['custom403msg']) }}"
		   title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_CUSTOMMESSAGE_DESC')" />
</div>

<div class="akeeba-form-group">
	<label for="use403view"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_USE403VIEW')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_USE403VIEW_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_USE403VIEW')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'use403view', $this->wafconfig['use403view'])
</div>

<div class="akeeba-form-group">
	<label for="troubleshooteremail"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TROUBLESHOOTEREMAIL')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TROUBLESHOOTEREMAIL_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TROUBLESHOOTEREMAIL')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'troubleshooteremail', $this->wafconfig['troubleshooteremail'])
</div>
