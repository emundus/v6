<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

$index_form = 1;
$index_doc = 1;

foreach ($forms as $index => $form){
    if($form->id == $menuid){
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
                        <div class="mod_emundus_checklist___grid">
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
                    <a href="<?php echo $itemid['link'].'&Itemid='.$itemid['id'] ?>"><?php echo JText::_($mandatory_documents_title) ?></a>
                </div>
                <div>
                    <?php foreach ($uploads as $upload) : ?>
                    <div class="em-flex-row mod_emundus_checklist___attachment">
                        <span class="material-icons-outlined em-main-500-color em-font-size-16">check_circle</span>
                        <a class="em-font-size-12 em-ml-8"  href="<?php echo $itemid['link'].'&Itemid='.$itemid['id'].'#a'.$upload->attachment_id ?>">
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
