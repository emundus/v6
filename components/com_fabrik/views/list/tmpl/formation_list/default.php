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


require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');

$m_campaign = new EmundusModelCampaign();


$pageClass = $this->params->get('pageclass_sfx', '');
$document = JFactory::getDocument();

$document->addStyleSheet('https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css');


$app = JFactory::getApplication();

$menu = $app->getMenu();
$menu_id = $menu->getActive();


$current_user = JFactory::getUser();
$model = $this->getModel();
$nav  = $model->getPagination();

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
//echo $this->table->intro;

$form = $this->form;
$form_id = $form->id;

?>
<form class="fabrikForm form-search" action="<?php echo $this->table->action;?>" method="post" id="<?php echo $this->formid;?>" name="fabrikList" data-id="<?= $form_id; ?>">

    <?php
    if ($this->hasButtons):
        echo $this->loadTemplate('buttons');
    endif;



    //for some really ODD reason loading the headings template inside the group
    //template causes an error as $this->_path['template'] doesn't contain the correct
    // path to this template - go figure!

    echo $this->loadTemplate('tabs');
    ?>



    <?php
    $gCounter = 0;
    $id_projet = $this->table->db_table_name.'___id_raw';
    $training_code = $this->table->db_table_name.'___code_raw';
    $fnum_element = $this->table->db_table_name.'___fnum';
    $user = $this->table->db_table_name.'___user_raw';
    $app       = JFactory::getApplication();
    $menu      = $app->getMenu();
    $connexionCandidater    = $menu->getItem(2700);
    $apply    = $menu->getItem(2879);

    $lang = JFactory::getLanguage();

    $i = 0;
    if($current_user->guest == 1){
        if ($lang->getTag() == 'en-GB') {
            $reset= "liste-des-formations?resetfilters=1";
            $connexion = $connexionCandidater->alias;
        }
        else{
            $reset= "fr/liste-des-formations?resetfilters=1";
            $connexion = "fr/".$connexionCandidater->alias;
        }
    }
    else{
        if ($lang->getTag() == 'en-GB') {
            $reset= "liste-des-formations-connecte?resetfilters=1";
        }
        else{
            $reset= "fr/liste-des-formations-connecte?resetfilters=1";
        }
    }
    ?>

    <table class="<?php echo $this->list->class;?>" id="list_<?php echo $this->table->renderid;?>" >
        <tr><?php echo $headingsHtml?></tr>
    </table>
    <section class="em-search">

        <div class="em-searchContainer">
            <div class="em-filter">
                <div class="header">
                    <h2><?= JText::_('FILTER');?></h2>
                    <a href="<?= $reset ?>"><img src="/images/custom/francediploma/reset.svg"></i></a>
                </div>
                <div class="em-select">
                    <?php
                    if ($this->showFilters && $this->bootShowFilters) :
                        echo $this->loadTemplate('filter');
                    endif; ?>
                </div>
            </div>
            <div class="em-card">
                <div class="em-result">
                    <h4><?= $nav->total.' '.JText::_('RESULT'); ?></h4>
                </div>
                <div class="em-cardContainer">
                    <?php

                    foreach ($this->rows as $groupedby => $group) {

                        //Création d'un tableau pour récupérer les classes des éléments
                        foreach ($this->cellClass as $key => $val) {

                            $keys = array($i);
                            $classArray[] = array_fill_keys($keys, $val);

                            $i = $i + 1;

                        }
                        // Décalage des indexes du tableau de 1 pour les modulos
                        array_unshift($group, "");
                        unset($group[0]);

                        //Boucle de création des éléments spécifique à afficher dans la carte en fonction de la classe de l'élément
                        for ($j = 0; $j < count($classArray); $j++) {
                            for ($h = 0; $h < count($classArray); $h++) {

                                $element = explode(' ', $classArray[$j][$h]['class']);

                                //ajouter une classe sur l'élément fabrik souhaité
                                if (end($element) == 'em-label') {
                                    $label = $element[0];
                                }
                                if (end($element) == 'em-language') {
                                    $language = $element[0];
                                }
                                if (end($element) == 'em-pole') {
                                    $pole = $element[0];
                                    //$thematique_raw = $element[0].'_raw';
                                }
                                if (end($element) == 'em-campus') {
                                    $campus = $element[0];
                                }
                                if (end($element) == 'em-level') {
                                    $level = $element[0];
                                }
                                if (end($element) == 'em-school') {
                                    $school = $element[0];
                                }
                                if (end($element) == 'em-price') {
                                    $price = $element[0];
                                }
                                if (end($element) == 'em-brochure') {
                                    $brochure = $element[0];
                                }
                                if (end($element) == 'em-brochureEcole') {
                                    $brochureEcole = $element[0];
                                }
                                if (end($element) == 'em-rentree') {
                                    $rentree = $element[0];
                                }

                            }
                        }


                        for ($i = 1; $i <= count($group); $i++) {

                            $training = $group[$i]->data->$training_code;
                            $cid = $m_campaign->getCampaignsByCourse($training)['id'];
                            $fnum = $group[$i]->data->$fnum_element;
                            $id = $group[$i]->data->$id_projet;

                            $url_detail = 'index.php?option=com_fabrik&view=details&formid=' . $form_id . '&Itemid=' . $menu_id->id . '&usekey=id&rowid=' . $id;

                            if($current_user->guest == 1){ ?>
                                <div class="card">
                                    <div class="cardContainer">
                                        <div class="cardContainerHeader">
                                            <h4><?= $group[$i]->data->$label; ?></h4>
                                        </div>
                                        <div class="cardContainerContent">
                                            <p><span class='intitule'><?= JText::_('LEVEL'); ?>:</span> <?= $group[$i]->data->$level; ?></p>
                                            <p><span class='intitule'><?= JText::_('CAMPUS'); ?>:</span> <?= $group[$i]->data->$campus; ?></p>
                                            <p><span class='intitule'><?= JText::_('LANGUAGE'); ?>:</span> <?= $group[$i]->data->$language; ?></p>
                                            <p class="em-domaine-container"><span class='intitule'><?= JText::_('POLE'); ?>:</span><span class="em-domaine-formation"><?= $group[$i]->data->$pole; ?></span></p>
                                        </div>
                                    </div>
                                    <a class="btn btn-connexion" href="<?= $connexion;?>"><?= JText::_('APPLY_NOW'); ?></a>
                                </div>

                                <?php
                            }
                            else {
                                ?>
                                <div class="card">
                                    <div class="cardContainer">
                                        <div class="cardContainerHeader">
                                            <h4><?= $group[$i]->data->$label; ?></h4>
                                            <a class="em-dowload" target="_blank"
                                               href="<?= $group[$i]->data->$brochure; ?>" data-toggle="tooltip"
                                               data-placement="top" title="<?= JText::_('DOWNLOAD_DOC'); ?>"><img
                                                        src="images/custom/francediploma/cloud-computing.png"></a>
                                        </div>
                                        <div class="cardContainerContent">
                                            <p><span
                                                        class='intitule'><?= JText::_('LEVEL'); ?>:</span> <?= $group[$i]->data->$level; ?>
                                            </p>
                                            <p><span
                                                        class='intitule'><?= JText::_('CAMPUS'); ?>:</span> <?= $group[$i]->data->$campus; ?>
                                            </p>
                                            <p><span
                                                        class='intitule'><?= JText::_('LANGUAGE'); ?>:</span> <?= $group[$i]->data->$language; ?>
                                            </p>
                                            <p class="em-domaine-container"><span
                                                        class='intitule'><?= JText::_('POLE'); ?>:</span><span
                                                        class="em-domaine-formation"> <?= $group[$i]->data->$pole; ?></span>
                                            </p>
                                            <p><span class='intitule'><?= JText::_('SCHOOL'); ?>:</span><a
                                                        href="<?= $group[$i]->data->$brochureEcole; ?>"
                                                        target="_blank"> <?= $group[$i]->data->$school; ?></a></p>
                                            <p><span
                                                        class='intitule'><?= JText::_('RENTREE'); ?>:</span> <?= $group[$i]->data->$rentree; ?>
                                            </p>
                                            <p><span
                                                        class='intitule'><?= JText::_('PRICE'); ?>:</span> <?= $group[$i]->data->$price; ?>
                                                €</p>
                                        </div>
                                    </div>
                                    <?php $link = base64_encode('index.php?option=com_fabrik&view=form&formid=102&course=<?= $training;?>&cid=<?=$cid;?>'); ?>
                                    <a class="btn btn-connexion"
                                       href="<?= $apply->alias ?>?course=<?= $training; ?>&cid=<?= $cid; ?>&itemId=2879"><?= JText::_('SUBMIT_APPLICATION_FILE'); ?></a>
                                </div>
                            <?php }
                        }
                    }
                    ?>
                </div>
            </div>
    </section>
    <?= $this->nav; ?>

    <script src="https://d3e54v103j8qbb.cloudfront.net/js/jquery-3.4.1.min.220afd743d.js" type="text/javascript" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>
    <script>
        $(document).ready(function() {
            $('select[id^=jos_emundus_setup_programmes___]').select2({
                theme: "classic"
            });
            $('select[id^=jos_emundus_setup_teaching_unity___]').select2({
                theme: "classic"
            });
            $('[data-toggle="tooltip"]').tooltip()
        });
    </script>
    <!-- [if lte IE 9]><script src="https://cdnjs.cloudflare.com/ajax/libs/placeholders/3.0.2/placeholders.min.js"></script><![endif] -->
    <?php print_r($this->hiddenFields);?>
    </div>

</form>

