<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

ToolbarHelper::title(Text::_('EB_IMPORT_REGISTRANTS_TITLE'));
ToolbarHelper::custom('registrant.import', 'upload', 'upload', 'EB_IMPORT_REGISTRANTS', false);
ToolbarHelper::cancel('registrant.cancel');
?>
<form action="index.php?option=com_eventbooking&view=registrant&layout=import" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<table class="admintable adminform">
		<tr>
			<td class="key">
				<?php echo Text::_('EB_CSV_FILE'); ?>
			</td>
			<td>
				<input type="file" name="input_file" id="input_file" size="50" />
			</td>
			<td>
				<?php echo Text::_('EB_CSV_REGISTRANTS_FILE_EXPLAIN'); ?>
			</td>
		</tr>
	</table>
	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>