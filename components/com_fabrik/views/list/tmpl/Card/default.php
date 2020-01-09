<?php
/**
 * Bootstrap List Template - Default
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'award.php');
$m_award = new EmundusModelAward();
$pageClass = $this->params->get('pageclass_sfx', '');

if ($pageClass !== '') :
	echo '<div class="' . $pageClass . '">';
endif;

if ($this->tablePicker != '') : ?>
	<div style="text-align:right"><?php echo FText::_('COM_FABRIK_LIST') ?>: <?php echo $this->tablePicker; ?></div>
<?php
endif;

if ($this->params->get('show_page_heading')) :
	echo '<h1>' . $this->params->get('page_heading') . '</h1>';
endif;

if ($this->showTitle == 1) : ?>
	<div class="page-header">
		<h1><?php echo $this->table->label;?></h1>
	</div>
<?php
endif;

// Intro outside of form to allow for other lists/forms to be injected.
echo $this->table->intro;

?>
<form class="fabrikForm form-search" action="<?php echo $this->table->action;?>" method="post" id="<?php echo $this->formid;?>" name="fabrikList">

<?php
if ($this->hasButtons):
	echo $this->loadTemplate('buttons');
endif;

if ($this->showFilters && $this->bootShowFilters) :
	echo $this->layoutFilters();
endif;
//for some really ODD reason loading the headings template inside the group
//template causes an error as $this->_path['template'] doesn't contain the correct
// path to this template - go figure!
$headingsHtml = $this->loadTemplate('headings');
echo $this->loadTemplate('tabs');
?>

<div class="fabrikDataContainer">

<?php foreach ($this->pluginBeforeList as $c) :
	echo $c;
endforeach;
?>
    <section class="award">
        <div class="em-cardContainer">
            <?php
            $gCounter = 0;
            $fnum = $this->table->db_table_name.'___fnum';
            $nb_vote = $this->table->db_table_name.'___nb_vote';


            $i = 0;

            foreach ($this->rows as $groupedby => $group) {

                //Création d'un tableau pour récupérer les classes des éléments
                foreach($this->cellClass as $key => $val){

                    $keys = array($i);
                    $classArray[] = array_fill_keys($keys,$val);

                    $i = $i +1;

                }
                // Décalage des indexes du tableau de 1 pour les modulos
                array_unshift($group,"");
                unset($group[0]);

                //Boucle de création des éléments spécifique à afficher dans la carte en fonction de la classe de l'élément
                for($j=0; $j < count($classArray); $j++) {
                    for ($h = 0; $h < count($classArray); $h++) {

                        $element = explode(' ', $classArray[$j][$h]['class']);

                        if (end($element) == 'em-title') {
                            $titre = $element[0];
                        }
                        if (end($element) == 'em-description') {
                            $description = $element[0];
                        }
                        if (end($element) == 'em-attachment1') {
                            $attachment_id = $element[0];
                        }
                    }
                }

                for($i=1; $i <= count($group); $i++) {
                    //récupération du nom de l'image
                    $filename = $m_award->getUpload($group[$i]->data->$fnum,$group[$i]->data->$attachment_id);
                    $user = $this->table->db_table_name.'___user_raw';
                    //Pair
                    if (($i % 2) == 0 ) {
                            ?>
                            <div class="em-cardContainer-card">
                                <div class="em-cardContainer-card-image">
                                    <img src="<?= JUri::base() .'images'.DS.'emundus'.DS.'files'.DS.$group[$i]->data->$user.DS.$filename; ?>"
                                         alt="">

                                </div>

                                <div class="em-cardContainer-card-content">
                                    <div class="em-cardContainer-card-content-txt">
                                        <h1><?= $group[$i]->data->$titre; ?></h1>
                                        <p><?= $group[$i]->data->$description; ?></p>
                                    </div>

                                    <div class="em-cardContainer-card-content-btn">

                                        <?php
                                        //Condition qui permet de changer de couleurs tout les trois cartes

                                        /*if(($i%3) == 1) { ?>
                                        <div class="btn-icons">
                                            <i id="angle" class="fas fa-angle-right"></i>
                                            <i id="arrow" class="fas fa-arrow-right"></i>
                                        </div>
                                        <?php }
                                        if(($i%3) == 2) { ?>
                                        <div class="btn-icons red">
                                            <i id="angle" class="fas fa-angle-right"></i>
                                            <i id="arrow" class="fas fa-arrow-right"></i>
                                        </div>
                                        <?php }
                                        if(($i%3) == 0) { ?>
                                        <div class="btn-icons green">
                                            <i id="angle" class="fas fa-angle-right"></i>
                                            <i id="arrow" class="fas fa-arrow-right"></i>
                                        </div>
                                        <?php } */?>

                                        <div>
                                            <?php
                                            // If we are not logged in: we cannot access this page and so we are redirected to the login page.
                                            $user = JFactory::getUser();
                                            $url = 'index.php?option=com_emundus&view=award&fnum='.$group[$i]->data->jos_emundus_challenges___fnum.'&aid='.$group[$i]->data->jos_emundus_challenges___attachment_id.'&aid2='.$group[$i]->data->jos_emundus_challenges___attachment_id_2;
                                            if ($user->guest) {
                                                JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($url)), JText::_('JGLOBAL_YOU_MUST_LOGIN_FIRST'), 'warning');
                                                return;
                                            } else { ?>
                                                <?= $group[$i]->data->fabrik_view; ?>
                                                <!--<a href="index.php?option=com_emundus&view=award&fnum=<?= $group[$i]->data->jos_emundus_challenges___fnum;?>&aid=<?= $group[$i]->data->jos_emundus_challenges___attachment_id;?>&aid2=<?= $group[$i]->data->jos_emundus_challenges___attachment_id_2;?>" target="_blank">VOIR PLUS</a>-->
                                            <?php } ?>
                                        </div>
                                    </div>


                                <!--<div class="em-cardContainer-card-vote">
                                    <hr>
                                    <p id="em-vote<?= $i;?>">Votez: <?= $group[$i]->data->$nb_vote; ?></p>
                                    <div class="em-cardContainer-card-vote-button">
                                        <a onclick="addVote('<?= $group[$i]->data->jos_emundus_challenges___fnum; ?>','<?=$i;?>')">YES <i class="fas fa-thumbs-up"></i></a>
                                        <a onclick="deleteVote('<?= $group[$i]->data->jos_emundus_challenges___fnum; ?>','<?=$i;?>')">NO <i class="fas fa-thumbs-down"></i></a>
                                    </div>
                                </div>-->

                                </div>
                                <div class="em-bulle left">
                                    <?php if(($i%3) == 1) { ?>
                                        <img src="https://assets.website-files.com/5e00e13e5fe2d91a0086a72a/5e010decd5ac3c1d0013f3b3_Forme-verte.png">
                                    <?php }
                                    if(($i%3) == 2) { ?>
                                        <img src="https://assets.website-files.com/5e00e13e5fe2d91a0086a72a/5e021faa8d3c51660ee2747a_Jaune.png">
                                    <?php }
                                    if(($i%3) == 0) { ?>
                                        <img src="https://assets.website-files.com/5e00e13e5fe2d91a0086a72a/5e021fba0d272032cb375473_Bleu.png">
                                    <?php } ?>
                                </div>
                            </div>

                        <?php
                    }
                    //Impair
                    if (($i % 2) == 1) {
                            ?>
                            <div class="em-cardContainer-card rouge">
                                <div class="em-cardContainer-card-image">
                                    <img src="<?= JUri::base() .'images'.DS.'emundus'.DS.'files'.DS.$group[$i]->data->$user.DS.$filename; ?>"
                                         alt="">
                                </div>
                                <div class="em-cardContainer-card-content">

                                    <div class="em-cardContainer-card-content-txt">
                                        <h1><?= $group[$i]->data->$titre; ?></h1>
                                        <p><?= $group[$i]->data->$description; ?></p>
                                    </div>
                                    <div class="em-cardContainer-card-content-btn">
                                        <?php /* if(($i%3) == 1) { ?>
                                            <div class="btn-icons">
                                                <i id="angle" class="fas fa-angle-right"></i>
                                                <i id="arrow" class="fas fa-arrow-right"></i>
                                            </div>
                                        <?php }
                                        if(($i%3) == 2) { ?>
                                            <div class="btn-icons red">
                                                <i id="angle" class="fas fa-angle-right"></i>
                                                <i id="arrow" class="fas fa-arrow-right"></i>
                                            </div>
                                        <?php }if(($i%3) == 0) { ?>
                                            <div class="btn-icons green">
                                                <i id="angle" class="fas fa-angle-right"></i>
                                                <i id="arrow" class="fas fa-arrow-right"></i>
                                            </div>
                                        <?php } */?>

                                        <div>
                                            <?php
                                            // If we are not logged in: we cannot access this page and so we are redirected to the login page.
                                            $user = JFactory::getUser();
                                            $url = 'index.php?option=com_emundus&view=award&fnum='.$group[$i]->data->jos_emundus_challenges___fnum.'&aid='.$group[$i]->data->jos_emundus_challenges___attachment_id.'&aid2='.$group[$i]->data->jos_emundus_challenges___attachment_id_2;
                                            if ($user->guest) {
                                            JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($url)), JText::_('JGLOBAL_YOU_MUST_LOGIN_FIRST'), 'warning');
                                            return;
                                            } else { ?>
                                                <?= $group[$i]->data->fabrik_view; ?>
                                                <!--<a href="index.php?option=com_emundus&view=award&fnum=<?= $group[$i]->data->jos_emundus_challenges___fnum;?>&aid=<?= $group[$i]->data->jos_emundus_challenges___attachment_id;?>&aid2=<?= $group[$i]->data->jos_emundus_challenges___attachment_id_2;?>" target="_blank">VOIR PLUS</a>-->
                                            <?php } ?>
                                        </div>
                                    </div>


                                <!--<div class="em-cardContainer-card-vote">
                                    <hr>
                                    <p id="em-vote<?= $i;?>">Votez: <?= $group[$i]->data->$nb_vote; ?></p>
                                    <div class="em-cardContainer-card-vote-button">
                                        <a onclick="addVote('<?= $group[$i]->data->jos_emundus_challenges___fnum; ?>','<?=$i;?>')">YES <i class="fas fa-thumbs-up"></i></a>
                                        <a onclick="deleteVote('<?= $group[$i]->data->jos_emundus_challenges___fnum; ?>','<?=$i;?>')">NO <i class="fas fa-thumbs-down"></i></a>
                                    </div>
                                </div>-->
                            </div>
                                <div class="em-bulle right">
                                    <?php if(($i%3) == 1) { ?>
                                        <img src="https://assets.website-files.com/5e00e13e5fe2d91a0086a72a/5e010decd5ac3c1d0013f3b3_Forme-verte.png">
                                    <?php }
                                    if(($i%3) == 2) { ?>
                                        <img src="https://assets.website-files.com/5e00e13e5fe2d91a0086a72a/5e021faa8d3c51660ee2747a_Jaune.png">
                                    <?php }
                                    if(($i%3) == 0) { ?>
                                        <img src="https://assets.website-files.com/5e00e13e5fe2d91a0086a72a/5e021fba0d272032cb375473_Bleu.png">
                                    <?php } ?>
                                </div>
                            </div>
                    <?php }
                }
            }
            ?>
        </div>
    </section>
	<?php print_r($this->hiddenFields);?>
</div>
</form>
<?php
echo $this->table->outro;
if ($pageClass !== '') :
	echo '</div>';
endif;
?>
<script>
    var button = document.querySelector('.em-cardContainer-card-vote-button a');
    button.addEventListener('click', () => {button.classList.add("active")});

    function addVote(fnum,i) {
            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'index.php?option=com_emundus&controller=award&task=addvote',
                data: ({
                    fnum: fnum
                }),
                success: function (result) {

                    if (result.status) {

                        Swal.fire({
                            type: 'success',
                            title: "<?php echo JText::_('COM_EMUNDUS_VOTE_ACCEPTED'); ?>"
                        });
                        jQuery('#em-vote'+i).html('Votez: '+result.nb_vote);
                    } else {
                        Swal.fire({
                            type: 'error',
                            text: "<?php echo JText::_('COM_EMUNDUS_VOTE_NON_ACCEPTED'); ?>"
                        });
                    }
                },
                error: function (jqXHR, textStatus) {
                    console.log(textStatus);
                    Swal.fire({
                        type: 'error',
                        text: "<?php echo JText::_('COM_EMUNDUS_VOTE_NON_ACCEPTED'); ?>"
                    });
                }
            });
        }
    function deleteVote(fnum,i) {
        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?option=com_emundus&controller=award&task=deletevote',
            data: ({
                fnum: fnum
            }),
            success: function (result) {
                if (result.status) {

                    Swal.fire({
                        type: 'success',
                        title: "<?php echo JText::_('COM_EMUNDUS_VOTE_ACCEPTED'); ?>"
                    });
                    jQuery('#em-vote'+i).html('Votez: '+result.nb_vote);
                } else {
                    Swal.fire({
                        type: 'error',
                        text: "<?php echo JText::_('COM_EMUNDUS_VOTE_NON_ACCEPTED'); ?>"
                    });
                }
            },
            error: function (jqXHR, textStatus) {
                console.log(textStatus);
                Swal.fire({
                    type: 'error',
                    text: "<?php echo JText::_('COM_EMUNDUS_VOTE_NON_ACCEPTED'); ?>"
                });
            }
        });
    }
</script>
