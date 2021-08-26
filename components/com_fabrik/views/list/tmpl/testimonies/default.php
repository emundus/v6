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


$pageClass = $this->params->get('pageclass_sfx', '');
$document = JFactory::getDocument();


$app = JFactory::getApplication();

$menu = $app->getMenu();
$menu_id = $menu->getActive();


$current_user = JFactory::getUser();


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
    <input type="hidden" id="menu_id" value="<?= $menu_id->id; ?>">
    
       

        <?php foreach ($this->pluginBeforeList as $c) :
            echo $c;
        endforeach;


        ?>

        <?php

        $fnum_element = $this->table->db_table_name.'___fnum';
        $user = $this->table->db_table_name.'___user_raw';

        $i = 0; ?>

        <table class="<?php echo $this->list->class;?>" id="list_<?php echo $this->table->renderid;?>" >
            <tr><?php echo $headingsHtml?></tr>
        </table>

        <div id="testimonies" class="section-normal">
            <div class="w-container">
            <h3 class="h3-lascala center dark">Alumni, what they say about us ?</h3>

                <?php
        if ($this->showFilters && $this->bootShowFilters) :
            echo $this->loadTemplate('filter');
        endif; ?>

        <?php
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

                    //ajouter une classe sur l'élément fabrik souhaité
                    if (end($element) == 'em-lastname') {
                        $lastname = $element[0];
                    }
                    if (end($element) == 'em-firstname') {
                        $firstname = $element[0];
                    }
                    if (end($element) == 'em-schoolyear') {
                        $schoolyear = $element[0];
                    }
                    
                    if (end($element) == 'em-identity') {
                        $filename = $element[0];
                        $filename_raw = $element[0].'_raw';
                    }
                    if (end($element) == 'em-link') {
                        $link = $element[0];
                    }
                    if (end($element) == 'em-testimonies') {
                        $testimonies = $element[0];
                    }
                }
            }
           
            for($i=1; $i <= count($group); $i++) {

                
                $fnum = $group[$i]->data->$fnum_element;

                //récupération du nom de l'image

                $current_user = JFactory::getUSer();
                //Pair
                ?>

                <div class="testimonies-blocs">
                <div class="testimonies-image"><img src="<?= JUri::base() .$group[$i]->data->$filename_raw; ?>" /></div>
                <div class="testimonies-text">
                <div class="testimonies-title">
                <h4><?= $group[$i]->data->$lastname.' '.$group[$i]->data->$firstname; ?></h4>
                <?php if(!empty($group[$i]->data->$link) && $group[$i]->data->$link != ''){ ?>
                                    <a href="<?= $group[$i]->data->$link; ?>"><img src="images/custom/linkedin-logo.png" /></a>
                <?php } ?>
                </div>
                <h5>Class of <?= $group[$i]->data->$schoolyear; ?></h5>
                <p><?= $group[$i]->data->$testimonies; ?></p>
                </div>
                </div>

            <?php } 
        } ?>
        </div>
</div>
</div>
                              
        <script src="https://d3e54v103j8qbb.cloudfront.net/js/jquery-3.4.1.min.220afd743d.js" type="text/javascript" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>

        <!-- [if lte IE 9]><script src="https://cdnjs.cloudflare.com/ajax/libs/placeholders/3.0.2/placeholders.min.js"></script><![endif] -->
        <?php print_r($this->hiddenFields);?>
</form>
<?= $this->nav; ?>