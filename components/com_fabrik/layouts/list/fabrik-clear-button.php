<?php
/**
 * List clear filters button layout
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;

$d = $displayData;
$title = '<span>' . Text::_('COM_FABRIK_CLEAR') . '</span>';
$opts = array('alt' => Text::_('COM_FABRIK_CLEAR'), 'class' => 'fabrikTip', 'opts' => '{"notice":true}', 'title' => $title);
$img = FabrikHelperHTML::image('filter_delete.png', 'list', $d->tmpl, $opts);

?>
<a href="#" class="clearFilters"><?php echo $img; ?></a>