<?php
defined('_JEXEC') or die('Restricted access');

JHTML::stylesheet('media/com_emundus/css/emundus.css' );
JHTML::stylesheet('media/com_emundus/css/emundus_programme.css' );
$config = JFactory::getConfig();
$site_offset = $config->get('offset');
?>


    <?php if (empty($this->campaign)) { ?>
            <div class="alert alert-warning"><?php echo JText::_('NO_RESULT_FOUND') ?></div>
    <?php } else { ?>
            <h1 class="title em-program-title"><?php echo $this->campaign['label'];?></h1>
                <div <?php if (!empty($this->com_emundus_programme_progdesc_class)) { echo "class=\"".$this->com_emundus_programme_progdesc_class."\""; } ?>>
                    <p> <?php if (!empty($this->com_emundus_programme_showprogramme)) { echo $this->campaign['notes']; }?> </p>

                    <?php if($this->com_emundus_programme_showlink) :?>
                        <a class="btn btn-primary <?php echo !empty($this->com_emundus_programme_showlink_class) ? $this->com_emundus_programme_showlink_class : "";?>" target="_blank" href="<?php echo $this->campaign['link'] ;?>"><?php echo JText::_('MORE_INFO');?></a>
                    <?php endif; ?>

                </div>
                 <div <?php if (!empty($this->com_emundus_programme_campdesc_class)) { echo "class=\"".$this->com_emundus_programme_campdesc_class."\""; } ?>>
                    <p> <?php if ($this->com_emundus_programme_showcampaign) {  echo $this->campaign['description']; } ?></p>
                </div>

            <fieldset class="apply-now-small">
                <legend><?php echo JText::_('CAMPAIGN_PERIOD'); ?></legend>
                <strong><i class="icon-clock"></i> <?php echo JText::_('CAMPAIGN_START_DATE'); ?></strong>

                <?php echo JFactory::getDate(new JDate(strtotime($this->campaign['start_date']),$site_offset))->format(JText::_('DATE_FORMAT_LC2'));?>
                <br>
                <strong><i class="icon-clock"></i> <?php echo JText::_('CAMPAIGN_END_DATE'); ?></strong>
                <?php echo JFactory::getDate(new JDate(strtotime($this->campaign['end_date']),$site_offset))->format(JText::_('DATE_FORMAT_LC2'));?>
            </fieldset>
        <br>
            <fieldset class="em_period">
                <a class="btn goback-btn"   role="button" href="index.php" data-toggle="sc-modal" ><?= JText::_('GO_BACK');?></a>
                <a class="btn save-btn"  role="button" href="<?=$this->com_emundus_programme_candidate_link . '&cid=' . $this->campaign['id'];?>" data-toggle="sc-modal" ><?= JText::_('APPLY_NOW');?></a>
            </fieldset>

<?php } ?>

<script>
    jQuery(document).ready(function() {
        var titre = "<?php echo $this->campaign['label']; ?>";
        jQuery(document).prop('title', titre);
    });
</script>