<?php
/**
 * Repeat group delete button
 */
use Joomla\CMS\Language\Text;
defined('JPATH_BASE') or die;

$d = $displayData;
?>
<a class="deleteGroup btn btn-small btn-danger" href="#"  data-bs-toggle="tooltip" title="<?php echo Text::_('COM_FABRIK_DELETE_GROUP');?>">
	<?php echo FabrikHelperHTML::icon('icon-minus', '', 'data-role="fabrik_delete_group"'); ?>
</a>