<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (! key_exists('label', $displayData) || ! key_exists('value', $displayData))
{
	return;
}

$label = $displayData['label'];
$value = $displayData['value'];
if (! $value)
{
	return;
}

$class = '';
if (isset($displayData['class']))
{
	$class = $displayData['class'];
}
?>
<dl class="dl-horizontal <?php echo $class;?>">
	<dt class="event-label"><?php echo htmlentities($label);?>: </dt>
	<dd class="event-content"><?php echo $value?></dd>
</dl>
