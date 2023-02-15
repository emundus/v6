<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var $this Akeeba\AdminTools\Admin\View\FixPermissions\Html */

?>
@if (version_compare(JVERSION, '3.999.999', 'lt'))
@jhtml('behavior.modal')
@endif

@if($this->more)
	<h1>@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_INPROGRESS')</h1>
@else
	<h1>@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_DONE')</h1>
@endif

<div class="akeeba-progress">
	<div class="akeeba-progress-fill" style="width:{{ $this->percentage }}%;"></div>
	<div class="akeeba-progress-status">
		{{ $this->percentage }}%
	</div>
</div>

<form action="index.php" name="adminForm" id="adminForm" method="get">
	<input type="hidden" name="option" value="com_admintools" />
	<input type="hidden" name="view" value="FixPermissions" />
	<input type="hidden" name="task" value="run" />
	<input type="hidden" name="tmpl" value="component" />
</form>

@if(!$this->more)
	<div class="akeeba-block--info" id="admintools-fixpermissions-autoclose">
		<p>@lang('COM_ADMINTOOLS_LBL_COMMON_AUTOCLOSEIN3S')</p>
	</div>
@endif
