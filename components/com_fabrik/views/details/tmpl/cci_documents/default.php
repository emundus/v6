<?php
/**
 * Bootstrap Details Template
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$form = $this->form;
$model = $this->getModel();
require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'files.php');

$user = JFactory::getSession()->get('emundusUser');

if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>">
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</div>
<?php
endif;
?>
<div class="page-header">
    <h1><?php echo JText::_("COM_EMUNDUS_APPLICATIONS_FOR") . ucfirst($this->data["jos_emundus_users___firstname_raw"] . " " . strtoupper($this->data["jos_emundus_users___lastname_raw"])); ?></h1>
</div>
<?php

echo $form->intro;
if ($this->isMambot) :
	echo '<div class="fabrikForm fabrikDetails fabrikIsMambot" id="' . $form->formid . '">';
else :
	echo '<div class="fabrikForm fabrikDetails" id="' . $form->formid . '">';
endif;
echo $this->plugintop;
echo $this->loadTemplate('buttons');
echo $this->loadTemplate('relateddata');

$h_files = new EmundusHelperFiles;
$attachments = $h_files->getAttachmentsTypesByProfileID($this->data["jos_emundus_users___profile_raw"]);
?>

<div class="accordion-container">
    <div class="article-title">
        <div class="article-name">
            <h2><?php echo $this->data["jos_emundus_setup_teaching_unity___label_raw"];?></h2>
            <span class="formation-date">
                <?php
                setlocale(LC_ALL, 'fr_FR.utf8');

                $date_start = $this->data["jos_emundus_setup_teaching_unity___date_start_raw"];
                $date_end = $this->data["jos_emundus_setup_teaching_unity___date_end_raw"];

                $start_day = date('d', strtotime($date_start));
                $end_day = date('d', strtotime($date_end));
                $start_month = date('m', strtotime($date_start));
                $end_month = date('m', strtotime($date_end));
                $start_year = date('y', strtotime($date_start));
                $end_year = date('y', strtotime($date_end));

                if ($start_day == $end_day && $start_month == $end_month && $start_year == $end_year) {
                    echo 'Le ' . strftime('%e', strtotime($date_start)) . " " . strftime('%B', strtotime($date_end)) . " " . date('Y', strtotime($date_end));
                } elseif ($start_month == $end_month && $start_year == $end_year) {
                    echo 'Du ' . strftime('%e', strtotime($date_start)) . " au " . strftime('%e', strtotime($date_end)) . " " . strftime('%B', strtotime($date_end)) . " " . date('Y', strtotime($date_end));
                } elseif ($start_month != $end_month && $start_year == $end_year) {
                    echo 'Du ' . strftime('%e', strtotime($date_start)) . " " . strftime('%B', strtotime($date_start)) . " au " . strftime('%e', strtotime($date_end)) . " " . strftime('%B', strtotime($date_end)) . " " . date('Y', strtotime($date_end));
                } elseif (($start_month != $end_month && $start_year != $end_year) || ($start_month == $end_month && $start_year != $end_year)) {
                    echo 'Du ' . strftime('%e', strtotime($date_start)) . " " . strftime('%B', strtotime($date_start)) . " " . date('Y', strtotime($date_start)) . " au " . strftime('%e', strtotime($date_end)) . " " . strftime('%B', strtotime($date_end)) . " " . date('Y', strtotime($date_end));
                }
                ?>
            </span>
        </div>
    </div>
    <div class="accordion-content">
        <?php foreach ($attachments as $attachment) :?>
            <?php if ($attachment->id == 102) :?>
                <div class="em-attachement fiche-pedago" onclick="getProductPDF('<?php echo $this->data["jos_emundus_setup_campaigns___training_raw"]; ?>')">
                    <i class="far fa-file-pdf"></i>
                    <p class="em-attachement-name"><?= $attachment->value ?></p>
                </div>
            <?php else:?>
                <div class="em-attachement">
                    <i class="far fa-file-pdf"></i>
                    <p class="em-attachement-name"><?= $attachment->value ?></p>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<?php
echo $this->pluginbottom;
echo $this->loadTemplate('actions');
echo '</div>';
echo $form->outro;
echo $this->pluginend;
?>

<script>

    function getProductPDF(code) {
        console.log(code);
        jQuery.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=files&task=getproductpdf',
            async: false,
            data: {
                product_code: code
            },
            success: function (result) {
                result = JSON.parse(result);
                if (result.status) {
                    var win = window.open(result.filename, '_blank');
                    win.focus();
                } else {
                    alert(result.msg);
                }
            },
            error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        });
    }
</script>