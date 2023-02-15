<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var $this Akeeba\AdminTools\Admin\View\CleanTempDirectory\Html */

?>
@if (version_compare(JVERSION, '3.999.999', 'lt'))
	@jhtml('behavior.modal')
@endif

@if($this->more)
	<h1>@lang('COM_ADMINTOOLS_LBL_CLEANTEMPDIRECTORY_CLEANTMPINPROGRESS')</h1>
@else
	<h1>@lang('COM_ADMINTOOLS_LBL_CLEANTEMPDIRECTORY_CLEANTMPDONE')</h1>
@endif

	<div class="akeeba-progress">
        <div class="akeeba-progress-fill" style="width:{{ (int)$this->percentage }}%;"></div>
        <div class="akeeba-progress-status">
			{{ (int)$this->percentage }}%
        </div>
    </div>

	<form action="index.php" name="adminForm" id="adminForm">
		<input type="hidden" name="option" value="com_admintools"/>
		<input type="hidden" name="view" value="CleanTempDirectory"/>
		<input type="hidden" name="task" value="run"/>
		<input type="hidden" name="tmpl" value="component"/>
	</form>

@unless($this->more)
	<div class="akeeba-block--info" id="admintools-cleantmp-autoclose">
		<p>@lang('COM_ADMINTOOLS_LBL_COMMON_AUTOCLOSEIN3S')</p>
	</div>
@endif
