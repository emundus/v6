<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string   $text      The label text
 * @var   string   $for       The id of the input this label is for
 * @var   boolean  $required  True if a required field
 * @var   array    $classes   A list of classes
 *
 * Override to get the description from xml file and add it as tooltip (like in J!3)
 * Add CSS class FabrikAdminLabel for future styling
 * @var   string   $description      The description text
 */

$classes = array_filter((array) $classes);
$id      = $for . '-lbl';
if (!empty($description))
{
	if ($text && $text !== $description)
	{
		HTMLHelper::_('bootstrap.tooltip','.hasTooltip');
		$classes[] = 'hasTooltip';
		$classes[] = 'FabrikAdminLabel';

		$title     = ' data-bs-html= "true" title="' .  htmlspecialchars($description) . '"';

		if (!$position && Factory::getLanguage()->isRtl())
		{
			$position = ' data-bs-placement="left" ';
		}
	}
	else
	{
		HTMLHelper::_('bootstrap.tooltip','.hasTooltip');
		$classes[] = 'hasTooltip';
		$classes[] = 'FabrikAdminLabel';
		$title     = ' title="' . HTMLHelper::_('tooltipText', trim($text, ':'), $description, 0) . '"';
	}
}
else
{
	$title     = ' title=" "';
}
if ($required)
{
	$classes[] = 'required';
}

?>
<label id="<?php echo $id; ?>" for="<?php echo $for; ?>"<?php if (!empty($classes)) { echo ' class="' . implode(' ', $classes) . '"';} ?><?php echo $title; ?>>
	<?php echo $text; ?>
	<?php if ($required) : ?><span class="star" aria-hidden="true">&#160;*</span><?php endif;
	if (!empty($description)) : ?><span class="star" aria-hidden="true">&#160;&#9432;</span><?php endif; ?>
</label>
