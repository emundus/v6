<?php // no direct access
defined('_JEXEC') or die('Restricted access');

$config = JFactory::getConfig();
$site_offset = $config->get('offset');

$dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
$dateTime = $dateTime->setTimezone(new DateTimeZone($site_offset));
$now = $dateTime->format('Y-m-d H:i:s');
?>

<style>
    .mod_emundus_flow___print .material-icons-outlined{
        color: var(--em-primary-color);
    }

    .btn.btn-primary.mod_emundus_flow___print:hover,
    .btn.btn-primary.mod_emundus_flow___print:active,
    .btn.btn-primary.mod_emundus_flow___print:focus{
        color: var(--neutral-0) !important;
        background: var(--em-primary-color) !important;
        border: 1px solid var(--em-primary-color) !important;
    }

    .btn.btn-primary.mod_emundus_flow___print:hover .material-icons-outlined,
    .btn.btn-primary.mod_emundus_flow___print:active .material-icons-outlined
    .btn.btn-primary.mod_emundus_flow___print:focus  .material-icons-outlined {
       color: var(--neutral-0) !important;
    }

    .mod_emundus_flow___print{
        display: flex !important;
        align-items: center;
    }
    .btn-primary.mod_emundus_flow___print{
        background: transparent;
    }

    .mod_emundus_flow___infos{
        flex-wrap: wrap;
        grid-gap: 12px;
        max-width: 75%;
    }

    .mod_emundus_flow___intro .btn.btn-primary {
        font-size: var(--em-applicant-font-size) !important;
        letter-spacing: normal !important;
        line-height: normal !important;
    }

    .mod_emundus_flow___intro {
        display: grid;
        align-items: flex-start;
        gap: 32px;
        grid-template-columns: 67% 30%;
    }

    .em-programme-tag {
        overflow: visible;
    }

    @media all and (max-width: 767px) {
        .mod_emundus_flow___intro{
           flex-direction: column;
            justify-content: flex-start;
            align-items: flex-start;
            row-gap: 8px !important;
            display: flex !important;
        }
        .mod_emundus_flow___infos div:first-child{
            margin-bottom: 6px;
        }
    }
</style>

<div style="padding: 0 20px">
    <div class="flex justify-between mt-2 mod_emundus_flow___intro">
        <div class="flex items-center">
            <h2 class="em-mb-0-important"><?php echo $campaign_name ?></h2>
            <?php
            $color = '#0A53CC';
            $background = '#C8E1FE';
            if(!empty($current_application->tag_color)){
                $color = $current_application->tag_color;
                switch ($current_application->tag_color) {
                    case '#106949':
                        $background = '#DFF5E9';
                        break;
                    case '#C31924':
                        $background = '#FFEEEE';
                        break;
                    case '#FFC633':
                        $background = '#FFFBDB';
                        break;
                }
            }
            ?>
        </div>
        <div class="flex items-center justify-end">
            <?php if ($show_back_button == 1) : ?>
            <a href="<?php echo $home_link ?>" title="<?php echo JText::_('MOD_EMUNDUS_FLOW_SAVE_AND_EXIT') ?>">
                <button class="btn btn-primary mr-4"><?php echo JText::_('MOD_EMUNDUS_FLOW_SAVE_AND_EXIT') ?></button>
            </a>
            <?php endif; ?>
            <a href="/component/emundus/?task=pdf&amp;fnum=<?= $current_application->fnum ?>" target="_blank" title="<?php echo JText::_('PRINT') ?>">
                <button class="btn btn-primary mod_emundus_flow___print">
                    <span class="material-icons-outlined" style="font-size: 16px">print</span>
                </button>
            </a>
        </div>
    </div>
    <?php if ($show_deadline == 1 || $show_status == 1) :?>
    <div class="flex flex-col mt-2 mod_emundus_flow___infos">
        <?php if ($show_deadline == 1) : ?>
        <div class="flex items-center">
            <p class="em-text-neutral-600 em-font-size-16"> <?php echo JText::_('MOD_EMUNDUS_FLOW_END_DATE'); ?></p>
            <span class="ml-1.5" style="white-space: nowrap"><?php echo JFactory::getDate(new JDate($deadline, $site_offset))->format('d/m/Y H:i'); ?></span>
        </div>
        <?php endif; ?>

        <?php if ($show_programme==1) : ?>
        <div class="flex items-center">
            <p class="em-text-neutral-600 mr-2"><?= JText::_('MOD_EMUNDUS_FLOW_PROGRAMME'); ?> : </p>
            <p class="em-programme-tag" style="color: <?php echo $color ?>;margin: unset;padding: 0">
                <?php  echo $current_application->prog_label; ?>
            </p>
        </div>
        <?php endif; ?>

        <?php if($show_status == 1) : ?>
        <div class="flex items-center">
            <p class="em-text-neutral-600 mr-2"><?= JText::_('MOD_EMUNDUS_FLOW_STATUS'); ?> : </p>
            <div class="mod_emundus_flow___status_<?= $current_application->class; ?> flex">
                <span class="label label-<?= $current_application->class; ?>"><?= $current_application->value ?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>


    <?php

        $m_email = new EmundusModelEmails();

        $emundusUser = JFactory::getSession()->get('emundusUser');

        $post = array(
            'APPLICANT_ID'   => $user->id,
            'DEADLINE'       => strftime("%A %d %B %Y %H:%M", strtotime($emundusUser->end_date)),
            'CAMPAIGN_LABEL' => $emundusUser->label,
            'CAMPAIGN_YEAR'  => $emundusUser->year,
            'CAMPAIGN_START' => $emundusUser->start_date,
            'CAMPAIGN_END'   => $emundusUser->end_date,
            'CAMPAIGN_CODE'  => $emundusUser->training,
            'FNUM'           => $emundusUser->fnum
        );

        $tags = $m_email->setTags($user->id, $post, $emundusUser->fnum, '', $file_tags);
        $file_tags_display = preg_replace($tags['patterns'], $tags['replacements'], $file_tags);
        $file_tags_display = $m_email->setTagsFabrik($file_tags_display, array($emundusUser->fnum));

    ?>

    <div class="em-mt-8">
        <?php if (!empty($file_tags_display)) :
            echo $file_tags_display;
         endif; ?>
    </div>

</div>

<script>
    function saveAndExit(){
        document.getElementsByClassName('fabrikForm')[0].submit();
    }
</script>
