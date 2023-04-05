<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

$d    = $displayData;

echo HTMLHelper::_('select.genericlist', $d->rows, $d->name, 'class="' . $d->class . '" ' . $d->size . ' maxlength="19"', 'value', 'text',
	$d->default, $d->htmlId . '0');