<?php
/**
 * Repeat group add button
 */
use Joomla\CMS\Language\Text;
defined('JPATH_BASE') or die;

$d = $displayData;
?>
<a class="addGroup btn btn-small btn-success" href="#" data-bs-toggle="tooltip" title="<?php echo Text::_('COM_FABRIK_ADD_GROUP');?>">
	<?php echo  FabrikHelperHTML::icon('icon-plus', '', 'data-role="fabrik_duplicate_group"');?>
</a>

