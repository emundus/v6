<?php
/**
 * Layout: List group by headings
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.3.4
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$d = $displayData;
$imgProps = array('alt' => FText::_('COM_FABRIK_TOGGLE'), 'data-role' => 'toggle', 'data-expand-icon' => 'icon-arrow-down', 'data-collapse-icon' => 'icon-arrow-right');
?>

<?php if ($d->emptyDataMessage != '') : ?>
<a href="#" class="toggle">
<?php else: ?>
<a href="#" class="toggle fabrikTip" title="<?php echo $d->emptyDataMessage ?>" opts='{"trigger": "hover"}'>
<?php endif; ?>
<?php echo FabrikHelperHTML::image('arrow-down', 'list', $d->tmpl, $imgProps); ?>
<span class="groupTitle text-green-500 font-semibold	">
<?php if(empty(strip_tags($d->title))) : ?>
Non catégorisé
<?php else : ?>
<?php echo strip_tags($d->title); ?>
<?php endif; ?>
<?php $d->group_by_show_count = isset($d->group_by_show_count) ? $d->group_by_show_count : '1'; ?>
</span>
</a>
<?php if (!empty($d->extra)) : ?>
<div class="groupExtra">
    <?php echo $d->extra; ?>
</div>
<?php endif; ?>
