<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

JHTML::stylesheet('media/com_emundus/css/emundus.css');
JHTML::stylesheet('media/com_emundus/css/emundus_programme.css');

$config      = Factory::getApplication()->getConfig();
$site_offset = $config->get('offset');
?>


<?php if (empty($this->campaign)) { ?>
    <div class="alert alert-warning"><?php echo JText::_('NO_RESULT_FOUND') ?></div>
<?php } else { ?>
    <h1 class="title em-program-title"><?php echo $this->campaign['label']; ?></h1>
    <div <?php if (!empty($this->com_emundus_programme_progdesc_class)) {
		echo "class=\"" . $this->com_emundus_programme_progdesc_class . "\"";
	} ?>>
        <p> <?php if (!empty($this->com_emundus_programme_showprogramme)) {
				echo $this->campaign['notes'];
			} ?> </p>

		<?php if ($this->com_emundus_programme_showlink) : ?>
            <a class="btn btn-primary <?php echo !empty($this->com_emundus_programme_showlink_class) ? $this->com_emundus_programme_showlink_class : ""; ?>"
               target="_blank" href="<?php echo $this->campaign['link']; ?>"><?php echo JText::_('MORE_INFO'); ?></a>
		<?php endif; ?>

    </div>
    <div <?php if (!empty($this->com_emundus_programme_campdesc_class)) {
		echo "class=\"" . $this->com_emundus_programme_campdesc_class . "\"";
	} ?>>
        <p> <?php if ($this->com_emundus_programme_showcampaign) {
				echo $this->campaign['description'];
			} ?></p>
    </div>

    <br>

<?php } ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.title = "<?php echo $this->campaign['label']; ?>";
    });
</script>
