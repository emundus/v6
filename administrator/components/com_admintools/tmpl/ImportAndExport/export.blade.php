<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;
?>
<div id="emailtemplateWarning" class="akeeba-block--warning" style="display: none">
	@lang('COM_ADMINTOOLS_IMPORTANDEXPORT_EMAILTEMPLATE_WARN')
</div>

<form name="adminForm" id="adminForm" action="index.php" method="post" class="akeeba-form--horizontal">
	<div class="akeeba-panel--primary">
		<header class="akeeba-block-header">
			<h3>@lang('COM_ADMINTOOLS_IMPORTANDEXPORT_FINE_TUNING')</h3>
		</header>

		<div class="akeeba-form-group">
			<label>@lang('COM_ADMINTOOLS_IMPORTANDEXPORT_WAFCONFIG')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'exportdata[wafconfig]', 1)
		</div>

		<div class="akeeba-form-group">
			<label>@lang('COM_ADMINTOOLS_IMPORTANDEXPORT_WAFBLACKLIST')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'exportdata[wafblacklist]', 1)
		</div>

		<div class="akeeba-form-group">
			<label>@lang('COM_ADMINTOOLS_IMPORTANDEXPORT_WAFEXCEPTIONS')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'exportdata[wafexceptions]', 1)
		</div>

		<div class="akeeba-form-group">
			<label>@lang('COM_ADMINTOOLS_IMPORTANDEXPORT_IPBLACKLIST')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'exportdata[ipblacklist]', 1)
		</div>

		<div class="akeeba-form-group">
			<label>@lang('COM_ADMINTOOLS_IMPORTANDEXPORT_IPWHITELIST')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'exportdata[ipwhitelist]', 1)
		</div>

		<div class="akeeba-form-group">
			<label>@lang('COM_ADMINTOOLS_IMPORTANDEXPORT_BADWORDS')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'exportdata[badwords]', 1)
		</div>

		<div class="akeeba-form-group">
			<label>@lang('COM_ADMINTOOLS_IMPORTANDEXPORT_EMAILTEMPLATES')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'exportdata[emailtemplates]', 0)
		</div>
	</div>

	<input type="hidden" name="option" value="com_admintools" />
	<input type="hidden" name="view" value="ImportAndExport" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="@token(true)" value="1" />
</form>
