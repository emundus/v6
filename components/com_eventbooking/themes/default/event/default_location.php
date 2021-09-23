<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */


defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

?>
<h2><?php echo Text::sprintf('EB_VENUE_INFORMATION', $location->name); ?></h2>

<?php
if ($location->image && file_exists(JPATH_ROOT . '/' . $location->image))
{
?>
	<img src="<?php echo Uri::root(true) . '/' . $location->image; ?>" class="eb-venue-image img-polaroid"/>
<?php
}

if (EventbookingHelper::isValidMessage($location->description))
{
?>
	<div class="eb-location-description"><?php echo HTMLHelper::_('content.prepare', $location->description); ?></div>
<?php
}

