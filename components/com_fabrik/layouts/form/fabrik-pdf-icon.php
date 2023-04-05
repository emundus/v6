<?php
/**
 * Email form layout
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;

$d = $displayData;
?>
<a href="<?php echo $d->pdfURL; ?>" data-role="open-form-pdf" class="btn btn-default">
	<?php echo FabrikHelperHTML::icon('icon-file', Text::_('COM_FABRIK_PDF'));?>
</a>&nbsp;