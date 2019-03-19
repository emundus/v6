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

$user = JFactory::getSession()->get('emundusUser');

if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>">
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</div>
<?php
endif;


require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'formations.php');

$m_users = new EmundusModelUsers();
$m_formations = new EmundusModelFormations();

$formations = $m_formations->getUserFormationByRH($this->data["jos_emundus_users___user_id_raw"], $user->id);
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

$numItems = count($formations);
$i = 0;
?>

<?php if (!empty($formations)) :?>

    <?php foreach ($formations as $formation):?>

    <div class="accordion-container accordion-container-<?php echo $formation->program_id; ?>" id="<?php echo $formation->fnum; ?>">
        <div class="em-top-details article-title article-title-<?php echo $formation->program_id; ?>">
            <div class="g-block size-70 em-formation-title">
                <div class="overflow">
                    <h2 rel="tooltip" title="<?php echo $formation->label; ?>"><?php echo $formation->label; ?></h2>
                </div>
                <div class="em-formation-details g-block size-100">
                    <div class="left g-block size-60">
                        <div class="formation-day">
                            <?php
                            setlocale(LC_ALL, 'fr_FR.utf8');

                            $date_start = $formation->date_start;
                            $date_end = $formation->date_end;

                            $start_day = date('d', strtotime($date_start));
                            $end_day = date('d', strtotime($date_end));
                            $start_month = date('m', strtotime($date_start));
                            $end_month = date('m', strtotime($date_end));
                            $start_year = date('y', strtotime($date_start));
                            $end_year = date('y', strtotime($date_end));

                            if ($start_day == $end_day && $start_month == $end_month && $start_year == $end_year) {
                                echo 'Date : le ' . strftime('%e', strtotime($date_start)) . " " . strftime('%B', strtotime($date_end)) . " " . date('Y', strtotime($date_end));
                            } elseif ($start_month == $end_month && $start_year == $end_year) {
                                echo 'Dates : du ' . strftime('%e', strtotime($date_start)) . " au " . strftime('%e', strtotime($date_end)) . " " . strftime('%B', strtotime($date_end)) . " " . date('Y', strtotime($date_end));
                            } elseif ($start_month != $end_month && $start_year == $end_year) {
                                echo 'Dates : du ' . strftime('%e', strtotime($date_start)) . " " . strftime('%B', strtotime($date_start)) . " au " . strftime('%e', strtotime($date_end)) . " " . strftime('%B', strtotime($date_end)) . " " . date('Y', strtotime($date_end));
                            } elseif (($start_month != $end_month && $start_year != $end_year) || ($start_month == $end_month && $start_year != $end_year)) {
                                echo 'Dates : du ' . strftime('%e', strtotime($date_start)) . " " . strftime('%B', strtotime($date_start)) . " " . date('Y', strtotime($date_start)) . " au " . strftime('%e', strtotime($date_end)) . " " . strftime('%B', strtotime($date_end)) . " " . date('Y', strtotime($date_end));
                            }
                            ?>
                        </div>

                        <div class="formation-number-details">
                            <?php echo JText::_("COM_EMUNDUS_SESSION_NUMBER") . ' : ' . $formation->session_code; ?>
                        </div>

                        <div class="formation-location">
                            <?php
                            $town = preg_replace('/[0-9]+/', '', str_replace(" cedex", "", ucfirst(strtolower($formation->location_city))));
                            $town = ucwords(strtolower($town), '\',. ');
                            $beforeComma = strpos($town, "D'");
                            if (!empty($beforeComma)) {
                                $replace = strpbrk($town, "D'");
                                $town = substr_replace($town, lcfirst($replace), $beforeComma);
                            }
                            echo JText::_("COM_EMUNDUS_SESSION_LOCATION") . ' : ' . $town;
                            ?>
                        </div>
                    </div>

                    <div class="right g-block size-35">
                        <div class="formation-length">
                            <?php echo ($formation->hours == 1) ? JText::_("DURATION") . ' : ' . $formation->hours. ' ' . JText::_("HOUR") : JText::_("DURATION") . ' : ' . $formation->hours . ' ' . JText::_("HOURS"); ?>
                        </div>

                        <div class="fomation-code">
                            <?php echo JText::_("CODE") . ' : ' . str_replace('FOR', '', $formation->code); ?>
                        </div>
                    </div>


                </div>

            </div>

            <div class="g-block size-30 links">
                <div class="em-status">
                    <span class="label label-<?php echo $formation->class; ?>">
                        <?php echo $formation->value; ?>
                    </span>
                </div>
                <div class="em-button-see-formation">
                    <a href="<?php echo '/formation?rowid=' . $formation->program_id; ?>" target="_blank"><?php echo JText::_("COM_EMUNDUS_SEE_FORMATION"); ?></a>
                </div>

                <div class="em-delete-application"
                     onclick="deleteApplication('<?php echo $formation->fnum; ?>')">
                    <?php echo JText::_('COM_EMUNDUS_REMOVE_APPLICATION'); ?>
                </div>

            </div>

        </div>
        <?php if(++$i != $numItems) :?>
            <hr class="formation-breaker">
        <?php endif; ?>

    </div>
<?php
endforeach; ?>
<!--
    <div class="em-no-find-formation">
        <h3><?php echo JText::_('COM_EMUNDUS_NO_FIND_FORMATION');?><a href="/inscription?user=<?php echo $this->data["jos_emundus_users___user_id_raw"]; ?>"><?php echo JText::_("COM_EMUNDUS_HERE");?></a></h3>
        <?php if (!empty($this->data["jos_emundus_users___user_id_raw"])) :?>
            <div class="em-inscrire-col"></div>
        <?php endif; ?>
    </div>
-->
<?php else: ?>
    <div class="em-no-formations">
        <h2><?php echo JText::_('COM_EMUNDUS_HAS_NO_FORMATIONS');?><a href="/inscription?user=<?php echo $this->data["jos_emundus_users___user_id_raw"]; ?>"><?php echo JText::_("COM_EMUNDUS_HERE");?></a></h2>
        <?php if (!empty($this->data["jos_emundus_users___user_id_raw"])) :?>
            <div class="em-inscrire-col"></div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
echo $this->pluginbottom;
echo $this->loadTemplate('actions');
echo '</div>';
echo $form->outro;
echo $this->pluginend;
?>



<script>
    function deleteApplication(fnum) {
        Swal.fire({
                title: "<?php echo JText::_('COM_EMUNDUS_REMOVE_ASSOCIATE'); ?>",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#dc3545",
                confirmButtonText: "<?php echo JText::_('JYES');?>",
                cancelButtonText: "<?php echo JText::_('JNO');?>"
            }
        ).then(
            function (isConfirm) {
                if (isConfirm.value == true) {
                    jQuery.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: 'index.php?option=com_emundus&controller=files&task=removefile',
                        data: ({
                            fnum: fnum
                        }),
                        success: function (result) {
                            if (result.status) {
                                document.getElementById(fnum).hide();
                                Swal.fire({
                                    type: 'success',
                                    title: "<?php echo JText::_('COM_EMUNDUS_ASSOCIATE_REMOVED'); ?>"
                                });
                            } else {
                                Swal.fire({
                                    type: 'error',
                                    text: "<?php echo JText::_('COM_EMUNDUS_ASSOCIATE__NOT_REMOVED'); ?>"
                                });
                            }
                        },
                        error: function (jqXHR) {
                            console.log(jqXHR.responseText);
                            Swal.fire({
                                type: 'error',
                                text: "<?php echo JText::_('COM_EMUNDUS_ASSOCIATE__NOT_REMOVED'); ?>"
                            });
                        }
                    });
                }
            }
        );
    }
</script>
