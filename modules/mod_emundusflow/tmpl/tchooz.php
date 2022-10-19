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
</style>

<div class="em-mt-48" style="padding: 0 20px">
    <div class="em-flex-row">
        <p class="em-h4"><?php echo JText::_($user->campaign_name) ?></p>
    </div>
    <div class="em-flex-row em-mt-8">
        <div class="em-flex-row">
            <p class="em-text-neutral-600 em-font-size-16 em-mr-8"> <?php echo JText::_('MOD_EMUNDUS_FLOW_END_DATE'); ?></p>
            <span><?php echo JFactory::getDate(new JDate($deadline, $site_offset))->format('d/m/Y H:i'); ?></span>
        </div>

        <div class="em-flex-row em-ml-24">
            <p class="em-text-neutral-600"><?= JText::_('MOD_EMUNDUS_FLOW_STATUS'); ?></p>
            <div class="mod_emundus_flow___status_<?= $current_application->class; ?> em-flex-row">
                <span class="mod_emundus_flow___circle em-mr-8 label-<?= $current_application->class; ?>"></span>
                <span><?= $current_application->value ?></span>
            </div>
        </div>
    </div>

</div>
