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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

Factory::getDocument()->addScript(Uri::root(true) . '/media/com_eventbooking/assets/js/ajaxupload.min.js');
?>
<input type="button" value="<?php echo Text::_('EB_SELECT_FILE'); ?>" id="button-file-<?php echo $name; ?>" class="btn btn-primary" />
<span class="eb-uploaded-file" id="uploaded-file-<?php echo $name; ?>">
<?php
    if ($value && file_exists(JPATH_ROOT . '/media/com_eventbooking/files/' . $value))
    {
    ?>
        <a href="<?php echo Route::_('index.php?option=com_eventbooking&task=controller.download_file&file_name=' . $value); ?>"><i class="fa fa-download"></i><strong><?php echo $value; ?></strong></a>
    <?php
    }
?>
</span>
<input type="hidden" id="<?php echo $name; ?>" name="<?php echo $name; ?>"  value="<?php echo $value; ?>" />
<script type="text/javascript">
    new AjaxUpload('#button-file-<?php echo $name; ?>', {
        action: siteUrl + 'index.php?option=com_eventbooking&task=upload_file',
        name: 'file',
        autoSubmit: true,
        responseType: 'json',
        onSubmit: function (file, extension) {
            jQuery('#button-file-<?php echo $name; ?>').after('<span class="wait">&nbsp;<img src="<?php echo Uri::root(true);?>/media/com_eventbooking/ajax-loadding-animation.gif" alt="" /></span>');
            jQuery('#button-file-<?php echo $name; ?>').attr('disabled', true);
        },
        onComplete: function (file, json) {
            jQuery('#button-file-<?php echo $name; ?>').attr('disabled', false);
            jQuery('.error').remove();
            if (json['success']) {
                jQuery('#uploaded-file-<?php echo $name; ?>').html(file);
                jQuery('input[name="<?php echo $name; ?>"]').attr('value', json['file']);
            }
            if (json['error']) {
                jQuery('#button-file-<?php echo $name; ?>').after('<span class="error">' + json['error'] + '</span>');
            }

            jQuery('.wait').remove();
        }
    });
</script>