<?php
/**
 * @package     Joomla.Site
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
echo $description;
JFactory::getDocument()->addStyleSheet('./media/mod_emundusApp/static/css/app.8c9886c7a2f41905848b769f2df6dda4.css');
?>
<?php if ($show_add_application && ($position_add_application == 0 || $position_add_application == 2)) : ?>
    <add></add>
    <hr>
<?php endif; ?>
<?php if (!empty($applications)) : ?>
    <div class="<?= $moduleclass_sfx ?>">
    
    <dossiers dossiers='<?= json_encode($applications); ?>' forms='<?= json_encode($forms); ?>' attachements='<?= json_encode($attachments); ?>' firstpage='<?= json_encode($first_page); ?>'></dossiers>

    
</div>
<?php else :
    echo JText::_('NO_FILE');
    echo '<hr>';
endif; ?>

<?php if ($show_add_application && $position_add_application > 0 && $applicant_can_renew) : ?>
    <add2></add2>
<?php endif; ?>

<?php foreach ($applications as $application) : ?>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            jQuery("#<?= $application->fnum; ?>").circliful({
                animation: 1,
                animationStep: 5,
                foregroundBorderWidth: 15,
                backgroundBorderWidth: 15,
                percent: <?= (int) ($forms[$application->fnum] + $attachments[$application->fnum]) / 2; ?>,
                textStyle: 'font-size: 12px;',
                textColor: '#000',
                foregroundColor: '<?= $show_progress_color; ?>'
            });
        });
    </script>
<?php endforeach; ?>

<?php if (!empty($filled_poll_id) && !empty($poll_url) && $filled_poll_id == 0 && $poll_url != "") : ?>
    <div class="modal fade" id="em-modal-form" style="z-index:99999" tabindex="-1" role="dialog" aria-labelledby="em-modal-form" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <h4 class="modal-title" id="em-modal-form-title"><?= JText::_('LOADING'); ?></h4>
                    <img src="media/com_emundus/images/icones/loader-line.gif">
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var poll_url = "<?= $poll_url; ?>";
        if ($poll_url !== "") {
            jQuery(".modal-body").html('<iframe src="' + poll_url + '" style="width:' + window.getWidth() * 0.8 + 'px; height:' + window.getHeight() * 0.8 + 'px; border:none"></iframe>');
            setTimeout(function () {
                jQuery('#em-modal-form').modal({backdrop: true, keyboard: true}, 'toggle');
            }, 1000);
        }
    </script>

<?php endif; ?>

<script type="text/javascript">
    function deletefile(fnum) {
        if (confirm("<?= JText::_('CONFIRM_DELETE_FILE'); ?>")) {
            document.location.href = "index.php?option=com_emundus&task=deletefile&fnum=" + fnum+"&redirect=<?php echo base64_encode(JUri::getInstance()->getPath()); ?>";
        }
    }
</script>
<script>
    jQuery(function () {
        jQuery('[data-toggle="tooltip"]').tooltip()
    })
</script>
<script type=text/javascript src="./media/mod_emundusApp/static/js/manifest.2ae2e69a05c33dfc65f8.js"></script>
<script type=text/javascript src="./media/mod_emundusApp/static/js/vendor.b5b6d32a7814f65c0724.js"></script>
<script type=text/javascript src="./media/mod_emundusApp/static/js/app.020434f9c8b8dbba67f8.js"></script>

