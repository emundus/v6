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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

if (!count($rows))
{
    return;
}

if(count($rows))
{
    $config = EventbookingHelper::getConfig();
	Factory::getLanguage()->load('com_eventbooking', JPATH_ADMINISTRATOR);
 ?>
    <table class="adminlist table table-striped">
        <thead>
            <tr>
                <th class="title" nowrap="nowrap"><?php echo Text::_('EB_FIRST_NAME'); ?></th>
                <th class="title" nowrap="nowrap"><?php echo Text::_('EB_LAST_NAME'); ?></th>
                <th class="title" nowrap="nowrap"><?php echo Text::_('EB_EVENT'); ?></th>
                <th class="title" nowrap="nowrap"><?php echo Text::_('EB_EVENT_DATE'); ?></th>
                <th class="title" nowrap="nowrap"><?php echo Text::_('EB_EMAIL'); ?></th>
                <th class="title center" nowrap="nowrap"><?php echo Text::_('EB_NUMBER_REGISTRANTS'); ?></th>
                <th class="title" nowrap="nowrap"><?php echo Text::_('EB_REGISTRATION_DATE'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach ($rows AS $row)
        {
            $link = Route::_('index.php?option=com_eventbooking&view=registrant&id='.$row->id);
            $eventLink = Route::_('index.php?option=com_eventbooking&view=event&id='.$row->event_id);
        ?>
        <tr>
            <td><a href="<?php echo $link ?>" target="_blank"><?php echo $row->first_name; ?></a></td>
            <td><?php echo $row->last_name; ?></td>
            <td><a href="<?php echo $eventLink ?>" target="_blank"><?php echo $row->title; ?></a></td>
            <td><?php echo $row->event_date; ?></td>
            <td><?php echo $row->email; ?></td>
            <td class="center"><?php echo $row->number_registrants; ?></td>
            <td><?php echo HTMLHelper::_('date', $row->register_date, $config->date_format.' H:i:s'); ?></td>
        </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
<?php
}