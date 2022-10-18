<?php
/**
 * @package     Joomla.Site
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;

$config = JFactory::getConfig();
$site_offset = $config->get('offset');

$dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
$dateTime = $dateTime->setTimezone(new DateTimeZone($site_offset));
$now = $dateTime->format('Y-m-d H:i:s');


?>
<div class="mod_emundus_applications___header">
    <p class="em-h3 em-mb-8"><?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_HELLO') . $user->firstname ?></p>
    <span class="mod_emundus_applications___header_desc"><?php echo $description; ?></span>

    <?php if ($show_add_application && ($position_add_application == 0 || $position_add_application == 2) && $applicant_can_renew) : ?>
        <a id="add-application" class="btn btn-success em-w-auto em-mt-32" href="<?= $cc_list_url; ?>">
            <span class="icon-plus-sign"> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></span>
        </a>
        <hr>
    <?php endif; ?>
</div>

<div class="mod_emundus_applications___content em-mt-32">
    <?php if (!empty($applications)) : ?>
        <div class="<?= $moduleclass_sfx ?>">
            <?php foreach ($applications as $application) : ?>


                <?php
                $is_admission = in_array($application->status, $admission_status);
                $state = $application->published;
                $confirm_url = (($absolute_urls === 1)?'/':'').'index.php?option=com_emundus&task=openfile&fnum=' . $application->fnum . '&confirm=1';
                $first_page_url = (($absolute_urls === 1)?'/':'').'index.php?option=com_emundus&task=openfile&fnum=' . $application->fnum;
                if ($state == '1' || $show_remove_files == 1 && $state == '-1' || $show_archive_files == 1 && $state == '0' ) : ?>
                    <?php
                    if ($file_tags != '') {

                        $post = array(
                            'APPLICANT_ID'  => $user->id,
                            'DEADLINE'      => strftime("%A %d %B %Y %H:%M", strtotime($application->end_date)),
                            'CAMPAIGN_LABEL' => $application->label,
                            'CAMPAIGN_YEAR'  => $application->year,
                            'CAMPAIGN_START' => $application->start_date,
                            'CAMPAIGN_END'  => $application->end_date,
                            'CAMPAIGN_CODE' => $application->training,
                            'FNUM'          => $application->fnum
                        );

                        $tags = $m_email->setTags($user->id, $post, $application->fnum, '', $file_tags);
                        $file_tags_display = preg_replace($tags['patterns'], $tags['replacements'], $file_tags);
                        $file_tags_display = $m_email->setTagsFabrik($file_tags_display, array($application->fnum));
                    }

                    ?>
                    <div class="row em-border-neutral-300 mod_emundus_applications___content_app" id="row<?= $application->fnum; ?>">
                        <div>
                            <?php
                            $color = '#1C6EF2';
                            $background = '#F0F6FD';
                            if(!empty($application->tag_color)){
                                $color = $application->tag_color;
                                switch ($application->tag_color) {
                                    case '#20835F':
                                        $background = '#DFF5E9';
                                        break;
                                    case '#DB333E':
                                        $background = '#FFEEEE';
                                        break;
                                    case '#FFC633':
                                        $background = '#FFFBDB';
                                        break;
                                }
                            }
                            ?>
                            <p class="mod_emundus_applications___programme_tag" style="color: <?php echo $color ?>;background-color:<?php echo $background ?>">
                                <?php  echo $application->programme; ?>
                            </p>
                            <a href="<?= JRoute::_($first_page_url); ?>" class="em-h6">
                                <?= ($is_admission &&  $add_admission_prefix)?JText::_('COM_EMUNDUS_INSCRIPTION').' - '.$application->label:$application->label; ?>
                            </a>
                        </div>

                        <div>
                            <?php
                            $displayInterval = false;
                            $interval = date_create($now)->diff(date_create($application->end_date));
                            if($interval->d == 0){
                                $displayInterval = true;
                            }
                            ?>
                            <div class="mod_emundus_applications___date em-mt-8">
                                <?php if (!$displayInterval) : ?>
                                    <span class="material-icons em-text-neutral-600 em-font-size-16 em-mr-8">schedule</span>
                                    <p class="em-text-neutral-600 em-font-size-16"> <?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_END_DATE'); ?> </p>
                                    <span class="em-camp-end em-text-neutral-600"> <?php echo JFactory::getDate(new JDate($application->end_date, $site_offset))->format('d/m/Y H:i'); ?></span>
                                <?php else : ?>
                                    <span class="material-icons em-text-neutral-600 em-font-size-16 em-red-500-color">schedule</span>
                                    <p class="em-red-500-color"><?php echo JText::_('MOD_EMUNDUS_APPLICATIONS_LAST_DAY'); ?>
                                        <?php if ($interval->h > 0) {
                                            echo $interval->h.'h'.$interval->i ;
                                        } else {
                                            echo $interval->i . 'm';
                                        }?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr/>

                        <div class="mod_emundus_applications___informations">
                            <div>
                                <label class="em-text-neutral-600">Candidat</label>
                                <p><?php echo $user->name ?></p>
                            </div>

                            <div>
                                <?php if(empty($visible_status)) : ?>
                                    <label class="em-text-neutral-600"><?= JText::_('MOD_EMUNDUS_APPLICATIONS_STATUS'); ?> :</label>
                                    <div class="mod_emundus_applications___status_<?= $application->class; ?> em-flex-row">
                                        <span class="mod_emundus_applications___circle em-mr-8 label-<?= $application->class; ?>"></span>
                                        <span><?= $application->value; ?></span>
                                    </div>
                                <?php elseif (in_array($application->status,$visible_status)) :?>
                                    <label class="em-text-neutral-600"><?= JText::_('MOD_EMUNDUS_APPLICATIONS_STATUS'); ?> :</label>
                                    <span class="label label-<?= $application->class; ?>"><?= $application->value; ?></span>
                                <?php endif; ?>
                                <?php if(!empty($application->order_status)): ?>
                                    <br>
                                    <label class="em-text-neutral-600"><?= JText::_('ORDER_STATUS'); ?> :</label>
                                    <span class="label" style="background-color: <?= $application->order_color; ?>"><?= JText::_(strtoupper($application->order_status)); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($show_state_files == 1) :?>
                            <div class="">
                                <div class="">
                                    <strong><?= JText::_('MOD_EMUNDUS_STATE'); ?></strong>
                                    <?php if ($state == 1) :?>
                                        <span class="label alert-success" role="alert"> <?= JText::_('MOD_EMUNDUS_PUBLISH'); ?></span>
                                    <?php elseif ($state == 0) :?>
                                        <span class="label alert-secondary" role="alert"> <?= JText::_('MOD_EMUNDUS_ARCHIVE'); ?></span>
                                    <?php else :?>
                                        <span class="label alert-danger" role="alert"><?= JText::_('MOD_EMUNDUS_DELETE'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="em-mt-16">
                            <span class="em-tags-display">
                                <?= $file_tags_display; ?>
                            </span>
                        </div>
                    </div>
                    <hr>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php else :
        echo JText::_('MOD_EMUNDUS_APPLICATIONS_NO_FILE');
        echo '<hr>';
    endif; ?>
</div>


<?php if ($show_add_application && $position_add_application > 0 && $applicant_can_renew) : ?>
    <a class="btn btn-success" href="<?= $cc_list_url; ?>"><span class="icon-plus-sign"> <?= JText::_('MOD_EMUNDUS_APPLICATIONS_ADD_APPLICATION_FILE'); ?></span></a>
<?php endif; ?>

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
        Swal.fire({
            title: "<?= JText::_('MOD_EMUNDUS_APPLICATIONS_CONFIRM_DELETE_FILE'); ?>",
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#dc3545",
            reverseButtons: true,
            confirmButtonText: "<?php echo JText::_('JYES');?>",
            cancelButtonText: "<?php echo JText::_('JNO');?>"
        }).then((confirm) => {
            if (confirm.value) {
                document.location.href = "index.php?option=com_emundus&task=deletefile&fnum=" + fnum+"&redirect=<?php echo base64_encode(JUri::getInstance()->getPath()); ?>";
            }
        });
    }
</script>
<script>
    jQuery(function () {
        jQuery('[data-toggle="tooltip"]').tooltip()
    })
</script>
