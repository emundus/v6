<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die;

/** @var    $this   Akeeba\AdminTools\Admin\View\EmergencyOffline\Html */
?>
<form action="index.php" name="adminForm" id="adminForm" method="post">
	<input type="hidden" name="option" value="com_admintools" />
	<input type="hidden" name="view" value="EmergencyOffline" />
	<input type="hidden" name="task" value="offline" />
	<p>
		<input type="submit" class="akeeba-btn--red--big"
			   value="@lang('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_SETOFFLINE')" />
	</p>
	<input type="hidden" name="@token(true)" value="1" />
</form>

@unless(($this->offline))
	<p class="akeeba-block--info">
		@lang('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_PREAPPLY')
	</p>
	<p class="akeeba-block--warning">
		@lang('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_PREAPPLYMANUAL')
	</p>
	<pre>{{ $this->htaccess }}</pre>
@endunless

@if($this->offline)

	<form action="index.php" name="adminForm" id="adminForm" method="post">
		<input type="hidden" name="option" value="com_admintools" />
		<input type="hidden" name="view" value="EmergencyOffline" />
		<input type="hidden" name="task" value="online" />
		<p>
			<input type="submit" class="akeeba-btn--green--big"
				   value="@lang('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_UNAPPLY')" />
		</p>
		<input type="hidden" name="@token(true)" value="1" />
	</form>
	<p class="akeeba-block--info">@lang('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_PREUNAPPLY')</p>
	<p class="akeeba-block--warning">@lang('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_PREUNAPPLYMANUAL')</p>
@endif
