<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var $this Akeeba\AdminTools\Admin\View\DatabaseTools\Html */
?>
@if(!empty($this->table))
	<h1>@lang('COM_ADMINTOOLS_LBL_DATABASETOOLS_OPTIMIZEDB_INPROGRESS')</h1>
@else
	<h1>@lang('COM_ADMINTOOLS_LBL_DATABASETOOLS_OPTIMIZEDB_COMPLETE')</h1>
@endif

<div class="akeeba-progress">
	<div class="akeeba-progress-fill" style="width:{{ $this->percent }}%;"></div>
	<div class="akeeba-progress-status">
		{{ $this->percent }}%
	</div>
</div>

@if(!empty($this->table))
	<form action="index.php" name="adminForm" id="adminForm">
		<input type="hidden" name="option" value="com_admintools" />
		<input type="hidden" name="view" value="DatabaseTools" />
		<input type="hidden" name="task" value="optimize" />
		<input type="hidden" name="from" value="{{{ $this->table }}}" />
		<input type="hidden" name="tmpl" value="component" />
	</form>
@endif

@if($this->percent == 100)
	<div class="akeeba-block--info" id="admintools-databasetools-autoclose">
		<p>@lang('COM_ADMINTOOLS_LBL_COMMON_AUTOCLOSEIN3S')</p>
	</div>
@endif
