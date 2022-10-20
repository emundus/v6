<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

$index_doc = 1;

foreach ($mandatory_documents as $attachment) {
    $query = 'SELECT count(id) FROM #__emundus_uploads up
            WHERE up.user_id = ' . $user->id . ' AND up.attachment_id = ' . $attachment->_id . ' AND fnum like ' . $db->Quote($user->fnum);
    $db->setQuery($query);
    $cpt = $db->loadResult();
    $link = '<a id="' . $attachment->_id . '" class="document" href="' . $itemid['link'] . '&Itemid=' . $itemid['id'] . '#a' . $attachment->_id . '">';
    $active = '';
    $need = $cpt == 0 ? 'need_missing' : 'need_ok';
    $class = $need . $active;
    $endlink = '</a>';
}
?>

<div class="mod_emundus_checklist">
    <div class="em-flex-row em-flex-space-between em-pointer" onclick="expandForms()">
        <p class="em-h6"><?php echo JText::_($forms_title) ?></p>
        <span id="mod_emundus_checklist___expand_icon" class="material-icons-outlined">expand_more</span>
    </div>

    <div id="mod_emundus_checklist___content" class="em-mt-24">
        <?php if ($show_forms == 1 && count($forms) > 0) : ?>
            <?php
            $index_doc = count($forms) + 1;
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
                        <div class="em-flex-row">
                            <div class="mod_emundus_checklist___step_count"><?php echo $step ?></div>
                            <a href="<?php echo $form->link ?>"><?php echo $form->title; ?></a>
                        </div>
                        <?php if ($index != (sizeof($forms) - 1) || $show_mandatory_documents == 1) : ?>
                            <div class="mod_emundus_checklist___border_item"></div>
                        <?php endif ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($show_mandatory_documents == 1 && count($mandatory_documents) > 0) : ?>
            <div class="<?php if($itemid['id'] == $menuid) echo 'active'?> mod_emundus_checklist_<?php echo $class; ?> mod_emundus_checklist___form_item">
                <div class="em-flex-row">
                    <div class="mod_emundus_checklist___step_count"><?php echo $index_doc ?></div>
                    <a href="<?php echo $itemid['link'].'&Itemid='.$itemid['id'] ?>">Documents à charger</a>
                </div>
                <div>
                    <?php foreach ($uploads as $upload) : ?>
                    <div class="em-flex-row mod_emundus_checklist___attachment">
                        <span class="material-icons-outlined em-main-500-color em-font-size-16">check_circle</span>
                        <p class="em-font-size-12 em-ml-8"><?php echo $upload->attachment_name ?>
                            <?php if($upload->filesize > 0) :?>
                                <span class="em-ml-4 em-text-neutral-600"><?php echo $upload->filesize  ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
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
