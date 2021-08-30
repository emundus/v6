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

?>
<table class="adminlist table table-striped">
	<tr>
		<td width="20%">
			<?php echo Text::_('EB_SELECT_EXISTING_SPEAKERS'); ?>
		</td>
		<td>
			<?php echo EventbookingHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $existingSpeakers, 'existing_speaker_ids[]', 'class="advancedSelect input-xlarge" multiple', 'id', 'name', $selectedSpeakerIds)); ?>
		</td>
	</tr>
</table>

<?php
foreach ($form->getFieldset() as $field)
{
	echo $field->input;
}