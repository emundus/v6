<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('ticket_types_collect_members_information', Text::_('EB_COLLECT_MEMBERS_INFORMATION'), Text::_('EB_COLLECT_MEMBERS_INFORMATION_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('ticket_types_collect_members_information', $collectMembersInformation); ?>
	</div>
</div>
<div class="row-fluid eb-ticket-types-container">
	<?php
	foreach ($form->getFieldset() as $field)
	{
		echo $field->input;
	}
	?>
</div>
