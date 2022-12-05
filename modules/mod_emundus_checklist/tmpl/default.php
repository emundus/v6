<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

$index_form = 1;
$index_doc = 1;

foreach ($forms as $index => $form){
    if ($form->id == $menuid) {
        $index_form = $index + 1;
        break;
    }
}
?>

<div class="mod_emundus_checklist">
    <div class="em-flex-row em-flex-space-between em-pointer" onclick="expandForms()">
        <div class="em-flex-row">
            <p class="em-h6"><?php echo JText::_($forms_title) ?></p>
            <span class="em-ml-12 mod_emundus_checklist___count"><?php echo $index_form . '/' . count($forms) ?></span>
        </div>
        <span id="mod_emundus_checklist___expand_icon" class="material-icons-outlined">expand_more</span>
    </div>

    <div id="mod_emundus_checklist___content" class="em-mt-24">
        <?php if ($show_forms == 1 && count($forms) > 0) : ?>
            <?php
            $index_doc = !empty($mandatory_documents) && $show_mandatory_documents ? count($forms) + 1 : count($forms);
            $index_opt_doc = !empty($optional_documents) && $show_optional_documents ? $index_doc + 1 : $index_doc;
            $index_payment = !empty($checkout_url) ? $index_opt_doc + 1 : $index_opt_doc;
            ?>
            <div>
                <?php foreach ($forms as $index => $form) : ?>
                    <?php
                    $query = 'SELECT count(*) FROM '.$form->db_table_name.' WHERE user = '.$user->id. ' AND fnum like '.$db->Quote($user->fnum);
                    $db->setQuery( $query );
                    $cpt = $db->loadResult();
                    $class = $cpt==0?'need_missing':'need_ok';
                    $step = $index+1;
                    ?>
                    <div id="mlf<?php echo $form->id; ?>" class="<?php if($form->id == $menuid) echo 'active'?> mod_emundus_checklist_<?php echo $class; ?> mod_emundus_checklist___form_item">
                        <div class="mod_emundus_checklist___grid">
                            <div class="mod_emundus_checklist___step_count"><?php echo $step ?></div>
                            <a href="<?php echo $form->link ?>"><?php echo JText::_($form->title); ?></a>
                        </div>
                        <?php if ($index != (sizeof($forms) - 1) || ($show_mandatory_documents == 1 && !empty($mandatory_documents)) || ($show_optional_documents == 1 && !empty($optional_documents))) : ?>
                            <div class="mod_emundus_checklist___border_item"></div>
                        <?php endif ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($show_mandatory_documents == 1 && count($mandatory_documents) > 0) : ?>
            <div class="<?php if($itemid['id'] == $menuid) echo 'active'?> mod_emundus_checklist_<?php echo $class; ?> mod_emundus_checklist___form_item">
                <div class="mod_emundus_checklist___grid">
                    <div class="mod_emundus_checklist___step_count"><?php echo $index_doc ?></div>
                    <a href="<?php echo $itemid['link'].'&Itemid='.$itemid['id'] ?>"><?php echo JText::_($mandatory_documents_title) ?></a>
                </div>
                <div style="margin-top: -8px">
                    <?php foreach ($uploads as $upload) : ?>
                        <div class="em-flex-row mod_emundus_checklist___attachment">
                            <span class="material-icons-outlined em-main-500-color" style="font-size: 16px">check_circle</span>
                            <a class="em-font-size-12 em-ml-8 mod_emundus_checklist___attachment_links"  href="<?php echo $itemid['link'].'&Itemid='.$itemid['id'].'#a'.$upload->attachment_id ?>">
                                <?php echo $upload->attachment_name ?>
                                <?php if($upload->filesize > 0) :?>
                                    <span class="em-ml-4 em-text-neutral-600"><?php echo $upload->filesize  ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($show_optional_documents == 1 && count($optional_documents) > 0) : ?>
            <div class="<?php if($itemid['id'] == $menuid) echo 'active'?> mod_emundus_checklist_<?php echo $class; ?> mod_emundus_checklist___form_item em-mt-32">
                <div class="mod_emundus_checklist___grid">
                    <div class="mod_emundus_checklist___step_count"><?php echo $index_opt_doc ?></div>
                    <a href="<?php echo $itemid['link'].'&Itemid='.$itemid['id'] ?>#attachment_list_opt"><?php echo JText::_($optional_documents_title) ?></a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($checkout_url)) : ?>
            <div class="mod_emundus_checklist_<?php echo $class; ?> mod_emundus_checklist___form_item em-mt-32">
                <div class="mod_emundus_checklist___grid">
                    <div class="mod_emundus_checklist___step_count"><?php echo $index_payment ?></div>
                    <p><?php echo JText::_('MOD_EMUNDUS_CHECKLIST_PAYMENT') ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$uri =& JFactory::getURI();
$url = explode('&',$uri->toString());
$details_view = array_search('view=details',$url);
?>

<div class="em-ml-32 em-mt-24">
    <?php if ($show_send && $details_view === false && $is_confirm_url === false) :?>
        <a class="btn btn-success btn-xs em-w-100"
            <?php if (((int)($attachments_progress) >= 100 && (int)($forms_progress) >= 100 && in_array($application->status, $status_for_send) && (!$is_dead_line_passed || ($is_dead_line_passed && $can_edit_after_deadline))) || in_array($user->id, $applicants)) :?>
                href="<?php echo $confirm_form_url; ?>" style="opacity: 1"
            <?php else: ?>
                style="opacity: 0.6; cursor: not-allowed"
            <?php endif; ?>
            <?php if ($application_fee && !$paid) :?>
                title="<?php echo JText::_('MOD_EMUNDUS_CHECKLIST_PROCESS_TO_PAYMENT'); ?>"
            <?php else :?>
                title="<?php echo JText::_('MOD_EMUNDUS_CHECKLIST_SEND_APPLICATION'); ?>"
            <?php endif ?>
        >
            <?php if ($application_fee && !$paid) :?>
                <?php echo JText::_('MOD_EMUNDUS_CHECKLIST_PROCESS_TO_PAYMENT'); ?>
            <?php else :?>
                <?php echo JText::_('MOD_EMUNDUS_CHECKLIST_SEND_APPLICATION'); ?>
            <?php endif ?>
        </a>
    <?php endif; ?>
</div>

<script>
    function expandForms(){
        let content = document.getElementById('mod_emundus_checklist___content');
        let icon = document.getElementById('mod_emundus_checklist___expand_icon');

        if(typeof content !== 'undefined'){
            if(!content.classList.contains('mod_emundus_checklist___content_closed')){
                content.classList.add('mod_emundus_checklist___content_closed');
                icon.style.transform = 'rotate(-90deg)';
            } else {
                content.classList.remove('mod_emundus_checklist___content_closed');
                icon.style.transform = 'rotate(0deg)';
            }
        }

    }
</script>
