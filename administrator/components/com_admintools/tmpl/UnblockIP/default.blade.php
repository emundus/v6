<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die;

/** @var    $this   Akeeba\AdminTools\Admin\View\UnblockIP\Html */
?>
<form action="index.php" name="adminForm" id="adminForm" method="post" class="akeeba-form--horizontal">
	<p class="akeeba-block--info">
		@lang('COM_ADMINTOOLS_LBL_UNBLOCKIP_INFO')
	</p>
	<div>
		<div class="akeeba-form-group">
			<label>@lang('COM_ADMINTOOLS_LBL_UNBLOCKIP_CHOOSE_IP')</label>
			<input type="text" value="" name="ip" />
		</div>

		<div class="akeeba-form-group--pull-right">
			<div>
				<input type="submit" class="akeeba-btn--primary--big"
					   value="@lang('COM_ADMINTOOLS_LBL_UNBLOCKIP_IP')" />
			</div>
		</div>
	</div>

	<input type="hidden" name="option" value="com_admintools" />
	<input type="hidden" name="view" value="UnblockIP" />
	<input type="hidden" name="task" value="unblock" />
	<input type="hidden" name="@token(true)" value="1" />
</form>
