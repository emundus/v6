<?php // no direct access
defined('_JEXEC') or die('Restricted access');

$config = JFactory::getConfig();
$site_offset = $config->get('offset');

$dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
$dateTime = $dateTime->setTimezone(new DateTimeZone($site_offset));
$now = $dateTime->format('Y-m-d H:i:s');
?>

<style>
    .mod_emundus_flow___circle{
        height: 8px;
        width: 8px;
        border-radius: 50%;
    }
    .mod_emundus_flow___print{
        height: 38px;
        display: flex;
        align-items: center;
    }
    .btn-primary.mod_emundus_flow___print{
        background: transparent;
    }
    .mod_emundus_flow___infos{
        flex-wrap: wrap;
        grid-gap: 24px;
    }

    .mod_emundus_flow___intro .em-h2{
        display: -webkit-box;
        overflow: hidden;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
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
        .mod_emundus_flow___infos{
            grid-gap: 0;
        }
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

<div class="em-mt-48" style="padding: 0 20px">
    <div class="em-flex-row em-flex-space-between  em-flex-row em-mt-8 mod_emundus_flow___intro">
        <div class="em-flex-row">
            <h2 class="em-h2 em-mb-0-important"><?php echo JText::_($user->campaign_name) ?></h2>
            <?php
            $color = '#1C6EF2';
            $background = '#C8E1FE';
            if(!empty($current_application->tag_color)){
                $color = $current_application->tag_color;
                switch ($current_application->tag_color) {
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
        </div>
        <div class="em-flex-row em-flex-row-justify-end">
            <?php if ($show_back_button == 1) : ?>
            <a href="<?php echo $home_link ?>" title="<?php echo JText::_('MOD_EMUNDUS_FLOW_SAVE_AND_EXIT') ?>">
                <button class="btn btn-primary em-mr-16" style="height: 41px"><?php echo JText::_('MOD_EMUNDUS_FLOW_SAVE_AND_EXIT') ?></button>
            </a>
            <?php endif; ?>
            <a href="/component/emundus/?task=pdf&amp;fnum=<?= $current_application->fnum ?>" target="_blank" title="Imprimer">
                <button class="btn btn-primary mod_emundus_flow___print">
                    <span class="material-icons-outlined" style="font-size: 16px">print</span>
                </button>
            </a>
        </div>
    </div>
    <?php if ($show_deadline == 1 || $show_status == 1) :?>
    <div class="em-flex-row em-mt-8 mod_emundus_flow___infos">
        <?php if ($show_deadline == 1) : ?>
        <div class="em-flex-row">
            <p class="em-text-neutral-600 em-font-size-16"> <?php echo JText::_('MOD_EMUNDUS_FLOW_END_DATE'); ?></p>
            <span class="em-ml-6" style="white-space: nowrap"><?php echo JFactory::getDate(new JDate($deadline, $site_offset))->format('d/m/Y H:i'); ?></span>
        </div>
        <?php endif; ?>

        <?php if ($show_programme==1) : ?>
            <p class="em-programme-tag em-ml-16" style="color: <?php echo $color ?>;background-color:<?php echo $background ?>;margin: unset">
                <?php  echo $current_application->prog_label; ?>
            </p>
        <?php endif; ?>

        <?php if($show_status == 1) : ?>
        <div class="em-flex-row">
            <p class="em-text-neutral-600"><?= JText::_('MOD_EMUNDUS_FLOW_STATUS'); ?></p>
            <div class="mod_emundus_flow___status_<?= $current_application->class; ?> em-flex-row">
                <span class="mod_emundus_flow___circle em-mr-8 em-ml-6 label-<?= $current_application->class; ?>-500"></span>
                <span><?= $current_application->value ?></span>
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

    <div class="em-flex-row em-mt-8 mod_emundus_flow___infos">
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
