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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
$iconCalendar      = $bootstrapHelper->getClassMapping('icon-calendar');

$dateFields = [
	'event_date',
	'event_end_date',
	'registration_start_date',
	'cut_off_date',
];

foreach ($dateFields as $dateField)
{
	if ($this->item->{$dateField} == $this->nullDate)
	{
		$this->item->{$dateField} = '';
	}	
}
?>
<div class="<?php echo $controlGroupClass;?>">
	<div class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('EB_TITLE') ; ?></div>
	<div class="<?php echo $controlsClass; ?>">
		<input type="text" name="title" value="<?php echo $this->escape($this->item->title); ?>" class="input-xlarge form-control" size="70" />
	</div>
</div>
<?php
if ($this->config->get('fes_show_alias', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('EB_ALIAS') ; ?></div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="text" name="alias" value="<?php echo $this->item->alias; ?>" class="input-xlarge form-control" size="70" />
        </div>
    </div>
<?php
}
?>
<div class="<?php echo $controlGroupClass;?>">
	<div class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('EB_MAIN_EVENT_CATEGORY') ; ?></div>
	<div class="<?php echo $controlsClass; ?>">
		<?php echo $this->lists['main_category_id'] ; ?>
	</div>
</div>

<?php
if ($this->config->get('fes_show_additional_categories', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('EB_ADDITIONAL_CATEGORIES') ; ?></div>
        <div class="<?php echo $controlsClass; ?>">
            <?php echo $this->lists['category_id'] ; ?>
            <?php echo '      ' . Text::_('EB_SELECT_MULTIPLE_CATEGORIES'); ?>
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_thumb_image', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('EB_THUMB_IMAGE') ; ?></div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="file" class="form-control" name="thumb_image" size="60" />
			<?php
			if ($this->item->thumb && file_exists(JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $this->item->thumb))
			{
				$baseUri = Uri::base(true);

				if ($this->item->image && file_exists(JPATH_ROOT . '/' . $this->item->image))
				{
					$largeImageUri = $baseUri . '/' . $this->item->image;
				}
                elseif (file_exists(JPATH_ROOT . '/media/com_eventbooking/images/' . $this->item->thumb))
				{
					$largeImageUri = $baseUri . '/media/com_eventbooking/images/' . $this->item->thumb;
				}
				else
				{
					$largeImageUri = $baseUri . '/media/com_eventbooking/images/thumbs/' . $this->item->thumb;
				}
				?>
                <a href="<?php echo $largeImageUri; ?>" class="modal"><img src="<?php echo $baseUri . '/media/com_eventbooking/images/thumbs/' . $this->item->thumb; ?>" class="img_preview" /></a>
                <input type="checkbox" name="del_thumb" value="1" /><?php echo Text::_('EB_DELETE_CURRENT_THUMB'); ?>
				<?php
			}
			?>
        </div>
    </div>
<?php
}
?>
<div class="<?php echo $controlGroupClass;?>">
	<div class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('EB_LOCATION') ; ?></div>
	<div class="<?php echo $controlsClass; ?>">
		<?php
		echo $this->lists['location_id'];

		if (Factory::getUser()->authorise('eventbooking.addlocation', 'com_eventbooking'))
		{
		?>
			<button type="button" class="btn btn-small btn-success eb-colorbox-addlocation" href="<?php echo Route::_('index.php?option=com_eventbooking&view=location&layout=popup&tmpl=component&Itemid='.$this->Itemid)?>"><span class="icon-new icon-white"></span><?php echo Text::_('EB_ADD_NEW_LOCATION') ; ?></button>
		<?php
		}
		?>
	</div>
</div>
<div class="<?php echo $controlGroupClass;?>">
	<div class="<?php echo $controlLabelClass; ?>">
		<?php echo Text::_('EB_EVENT_START_DATE'); ?>
	</div>
	<div class="<?php echo $controlsClass; ?>">
		<?php echo str_replace('icon-calendar', $iconCalendar, HTMLHelper::_('calendar', $this->item->event_date, 'event_date', 'event_date', $this->datePickerFormat)); ?>
		<?php echo $this->lists['event_date_hour'].' '.$this->lists['event_date_minute']; ?>
	</div>
</div>
<?php
if ($this->config->get('fes_show_event_end_date', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('EB_EVENT_END_DATE'); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
			<?php echo str_replace('icon-calendar', $iconCalendar, HTMLHelper::_('calendar', $this->item->event_end_date, 'event_end_date', 'event_end_date', $this->datePickerFormat)); ?>
			<?php echo $this->lists['event_end_date_hour'].' '.$this->lists['event_end_date_minute'] ; ?>
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_registration_start_date', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('EB_REGISTRATION_START_DATE'); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
			<?php echo str_replace('icon-calendar', $iconCalendar, HTMLHelper::_('calendar', $this->item->registration_start_date, 'registration_start_date', 'registration_start_date', $this->datePickerFormat)); ?>
			<?php echo $this->lists['registration_start_hour'].' '.$this->lists['registration_start_minute'] ; ?>
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_cut_off_date', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
	        <?php echo EventbookingHelperHtml::getFieldLabel('cut_off_date', Text::_( 'EB_CUT_OFF_DATE' ), Text::_('EB_CUT_OFF_DATE_EXPLAIN')); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
			<?php echo str_replace('icon-calendar', $iconCalendar, HTMLHelper::_('calendar', $this->item->cut_off_date, 'cut_off_date', 'cut_off_date', $this->datePickerFormat)); ?>
			<?php echo $this->lists['cut_off_hour'] . ' ' . $this->lists['cut_off_minute']; ?>
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_price', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('EB_PRICE'); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="text" name="individual_price" id="individual_price" class="input-mini form-control" size="10" value="<?php echo $this->item->individual_price; ?>" />
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_price_text', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
	        <?php echo EventbookingHelperHtml::getFieldLabel('price_text', Text::_( 'EB_PRICE_TEXT' ), Text::_('EB_PRICE_TEXT_EXPLAIN')); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="text" name="price_text" id="price_text" class="input-xlarge form-control" value="<?php echo $this->escape($this->item->price_text); ?>" />
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_tax_rate', 1))
{
?>
	<div class="<?php echo $controlGroupClass;?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('EB_TAX_RATE'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<input type="text" name="tax_rate" id="tax_rate" class="input-small form-control" size="10" value="<?php echo $this->item->tax_rate; ?>" />
		</div>
	</div>
<?php
}

if ($this->config->get('fes_show_capacity', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
	        <?php echo EventbookingHelperHtml::getFieldLabel('event_capacity', Text::_( 'EB_EVENT_CAPACITY' ), Text::_('EB_CAPACITY_EXPLAIN')); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="text" name="event_capacity" id="event_capacity" class="input-mini form-control" size="10" value="<?php echo $this->item->event_capacity; ?>" />
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_waiting_list_capacity', 0))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
			<?php echo EventbookingHelperHtml::getFieldLabel('waiting_list_capacity', Text::_( 'EB_WAITING_LIST_CAPACITY' )); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="text" name="waiting_list_capacity" id="waiting_list_capacity" class="input-mini form-control" size="10" value="<?php echo $this->item->waiting_list_capacity; ?>" />
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_registration_type', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('EB_REGISTRATION_TYPE'); ?></div>
        <div class="<?php echo $controlsClass; ?>">
			<?php echo $this->lists['registration_type'] ; ?>
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_custom_registration_handle_url', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
	        <?php echo EventbookingHelperHtml::getFieldLabel('registration_handle_url', Text::_( 'EB_CUSTOM_REGISTRATION_HANDLE_URL' ), Text::_('EB_CUSTOM_REGISTRATION_HANDLE_URL_EXPLAIN')); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="text" name="registration_handle_url" id="registration_handle_url"
                   class="input-xxlarge form-control" size="10" value="<?php echo $this->item->registration_handle_url; ?>" />
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_attachment', 0))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
			<?php echo EventbookingHelperHtml::getFieldLabel('attachment', Text::_( 'EB_ATTACHMENT' ), Text::_('EB_ATTACHMENT_EXPLAIN')); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="file" name="attachment" />
            <?php

            if (Factory::getUser()->authorise('core.admin', 'com_eventbooking'))
            {
	            echo $this->lists['available_attachment'];
            }

            if ($this->item->attachment)
            {
	            Text::_('EB_CURRENT_ATTACHMENT');

	            $attachmentRootLink = Uri::root(true) . '/' . ($this->config->attachments_path ?: 'media/com_eventbooking') . '/';

	            $attachments = explode('|', $item->attachment);

	            for ($i = 0, $n = count($attachments); $i < $n; $i++)
	            {
		            $attachment = $attachments[$i];

		            if ($i > 0)
		            {
			            echo '<br />';
		            }
		            ?>
                    <a href="<?php echo $attachmentRootLink . $attachment; ?>" target="_blank"><?php echo $attachment; ?></a>
		            <?php
	            }
	        ?>
                <input type="checkbox" name="del_attachment" value="1"/><?php echo Text::_('EB_DELETE_CURRENT_ATTACHMENT'); ?>
	        <?php
            }
            ?>
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_max_group_number', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
			<?php echo EventbookingHelperHtml::getFieldLabel('max_group_number', Text::_( 'EB_MAX_NUMBER_REGISTRANTS' ), Text::_('EB_MAX_NUMBER_REGISTRANTS_EXPLAIN')); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="text" name="max_group_number" id="max_group_number" class="input-mini form-control" size="10" value="<?php echo $this->item->max_group_number; ?>" />
        </div>
    </div>
<?php
}

if (PluginHelper::isEnabled('system', 'ebreminder'))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
			<?php echo  Text::_('EB_SEND_FIRST_REMINDER'); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="text" class="input-mini" name="send_first_reminder" value="<?php echo $this->item->send_first_reminder; ?>" size="5" /><span><?php echo ' ' . $this->lists['first_reminder_frequency'] . ' ' . $this->lists['send_first_reminder_time']; ?></span><?php echo Text::_('EB_EVENT_STARTED'); ?>
        </div>
    </div>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
			<?php echo  Text::_('EB_SEND_SECOND_REMINDER'); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="text" class="input-mini" name="send_second_reminder" value="<?php echo $this->item->send_second_reminder; ?>" size="5" /><span><?php echo ' ' . $this->lists['second_reminder_frequency'] . ' ' . $this->lists['send_second_reminder_time']; ?></span><?php echo Text::_('EB_EVENT_STARTED'); ?>
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_published', 1) && EventbookingHelperAcl::canChangeEventStatus($this->item->id))
{
?>
	<div class="<?php echo $controlGroupClass;?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('EB_PUBLISHED'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<?php
				if (isset($this->lists['published']))
				{
					echo $this->lists['published'];
				}
				else
				{
					echo EventbookingHelperHtml::getBooleanInput('published', $this->item->published);
				}
			?>
		</div>
	</div>
<?php
}

if ($this->config->get('fes_show_short_description', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
			<?php echo  Text::_('EB_SHORT_DESCRIPTION'); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
			<?php echo $editor->display( 'short_description',  $this->item->short_description , '100%', '180', '90', '6' ) ; ?>
        </div>
    </div>
<?php
}

if ($this->config->get('fes_show_description', 1))
{
?>
    <div class="<?php echo $controlGroupClass;?>">
        <div class="<?php echo $controlLabelClass; ?>">
			<?php echo  Text::_('EB_DESCRIPTION'); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
			<?php echo $editor->display( 'description',  $this->item->description , '100%', '250', '90', '10' ) ; ?>
        </div>
    </div>
<?php
}

if ($this->showCaptcha)
{
	if ($this->captchaPlugin == 'recaptcha_invisible')
	{
		$style = ' style="display:none;"';
	}
	else
	{
		$style = '';
	}
?>
	<div class="<?php echo $controlGroupClass;?>"<?php echo $style; ?>>
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo  Text::_('EB_CAPTCHA'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<?php echo $this->captcha; ?>
		</div>
	</div>
<?php
}
