<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

$d = $displayData;

//Force the "Please select" value to integer; user element extends dbjoin which has '' as default value
$d->options[0]->value = (int)$d->options[0]->value;

echo HTMLHelper::_('select.genericlist', $d->options, $d->name, $d->attributes, 'value', 'text', $d->default, $d->id);
