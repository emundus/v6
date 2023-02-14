<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;
?>
<form name="adminForm" id="adminForm" action="index.php" method="post" class="akeeba-form--horizontal"
	  enctype="multipart/form-data">
	<div class="akeeba-panel--primary">
		<header class="akeeba-block-header">
			<h3>@lang('COM_ADMINTOOLS_TITLE_IMPORT_SETTINGS')</h3>
		</header>

		<div class="akeeba-form-group">
			<label>@lang('COM_ADMINTOOLS_IMPORTANDEXPORT_FILE')</label>

			<input type="file" name="importfile" value="" />
		</div>
	</div>

	<input type="hidden" name="option" value="com_admintools" />
	<input type="hidden" name="view" value="ImportAndExport" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="@token(true)" value="1" />
</form>
