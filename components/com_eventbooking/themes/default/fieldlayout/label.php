<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

$config     = EventbookingHelper::getConfig();
$class      = '';
$useTooltip = false;

if ($config->get('display_field_description', 'use_tooltip') == 'use_tooltip' && !empty($description))
{
	HTMLHelper::_('bootstrap.tooltip');
	Factory::getDocument()->addStyleDeclaration(".hasTip{display:block !important}");
	$useTooltip = true;
	$class = 'hasTooltip hasTip';
}
?>
<label id="<?php echo $name; ?>-lbl" for="<?php echo $name; ?>"<?php if ($class) echo ' class="' . $class . '"' ?> <?php if ($useTooltip) echo ' title="' . HTMLHelper::tooltipText(trim($title, ':'), $description, 0) . '"'; ?>>
	<?php
	echo $title;

	if ($row->required)
	{
	?>
		<span class="star">&#160;*</span>
	<?php
	}
	?>
</label>