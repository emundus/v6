<?php
/**
 * Email form layout
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;

$d = $displayData;

?>
<a class="btn btn-default" data-fabrik-print href="<?php echo $d->link;?>">
	<?php echo FabrikHelperHTML::icon('icon-print', Text::_('COM_FABRIK_PRINT'));?>
</a>