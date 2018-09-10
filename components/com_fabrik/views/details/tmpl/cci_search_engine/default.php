<?php
/**
 * Form details template used for the HESAM search engine pages.
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2018  eMundus - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// If we are not logged in: we cannot access this page and so we are redirected to the login page.
$user = JFactory::getUser();

require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
$m_files = new EmundusModelFiles();
$form = $this->form;
$model = $this->getModel();
$groupTmpl = $model->editable ? 'group' : 'group_details';
$active = ($form->error != '') ? '' : ' fabrikHide';

if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>">
	    <?php echo $this->escape($this->params->get('page_heading')); ?>
	</div>
<?php endif;

    var_dump($this->data);
    echo $this->plugintop;
    echo $this->loadTemplate('buttons');
    echo $this->loadTemplate('relateddata');

    // TODO: GET Themes from GESCOF
    $theme = $this->data['jos_emundus_setup_teaching_unity___programmes_ro_raw'][0];

    // GETS all svg icons
    $date_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_dates.svg");
    $diplomant_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_diplomant.svg");
    $duree_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_duree.svg");
    $intervenant_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_intervenant.svg");
    $lieu_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_lieu.svg");
    $objectif_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_objectifs.svg");
    $pointscles_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_pointscles.svg");
    $prerequis_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_prerequis.svg");
    $prix_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_prix.svg");
    $public_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_public.svg");
    $telechargement_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_telechargement.svg");

    //var_dump($this->data).die();
?>


<!-- TODO: Do before style foreach theme -->
<style>
    /* TODO: do for each theme  */
    .em-icon-RECHERCHE svg path {
        fill: #55AD32 !important;
    }


    #details {
        display: inline-block;
        width: 80%;
    }

    .em-details {
        width: 49%;
        display: inline-block;
    }

    .em-offer {
        width: 80%;
    }




    #objectif-details {
        width: 84%;
        display: inline-block;
    }

    #partner {
        display: inline-block;
        width: 19%;
    }

    .em-details-icon {
        display: inline-block;
    }

    .em-details-icon svg, .em-option-price svg, .em-option-documents svg, .em-option-certificate svg {
        width: 40px;
        height: 40px;
    }

    .top-details {
         height: 75px;
         margin-top: 5px;
         margin-bottom: 10px;
     }

    .bottom-details {
         height: 75px;
         margin-bottom: 10px;
     }

    #em-people-detail {
        display: inline-block;
        position: absolute;
        max-width: 15%;
        max-height: 55px;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-left: 5px;
        line-height: 20px;
    }

    /* date details */
    #em-date-detail {
        display: inline-block;
        position: absolute;
    }

    .em-date {
        margin-left: 10px;
        font-weight: bold;
    }

    .em-days {
        margin-left: 10px;
        margin-top: -15px;
    }

    /* requirements details */
    #em-requirements-detail {
        display: inline-block;
        position: absolute;
        max-width: 15%;
        max-height: 55px;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-left: 5px;
        line-height: 20px;
        margin-top: 10px;
    }

    /* location details */
    #em-location-detail{
        display: inline-block;
        position: absolute;
        margin-top: 10px;
        margin-left: 10px;
        font-weight: bold;
    }


    .offer-icon {
        display: inline-block;
    }


    .offer-icon svg {
        width: 70px;
        height: 70px;
    }

    #objectif-details, #key-details, #certificate-details {
        display: inline-block;
        position: absolute;
        margin-left: 35px;
    }





/* this is the aside css section */
    .em-options {
        width: 100%;
        display: block;
    }

    .em-option-menu {
         width: 26%;
         display: inline-flex;
         background-color: #bbc0c3;
         cursor: pointer;
     }

    .em-option-menu.active {
        background-color: #566268;
    }

    .em-option-menu b{
        text-align: center;
        display: block;
        margin-left: auto;
        margin-right: auto;
        color: white;
        line-height: 20px;

    }

    #em-option-menu-intra {
        padding-bottom: 20px;
        margin-left: 36px;
        margin-right: 35px;
    }

    #em-option-menu-inter {
        padding-bottom: 20px;
    }

    .em-option {
        width: 100%;
        background-color: #566268;
        height: auto;
    }

    .em-option-details, .em-option-certificate, .em-option-price, .em-option-documents {
        height: 75px;
        margin-left: 10px;
    }

    .em-option-details {
        padding-top: 10px;
        margin-bottom: 20px;
    }

    .em-option-title {
        font-weight: bold;
    }

    .em-option-details p {
        color: white;
    }

    .em-option-price b, .em-option-certificate b, .em-option-documents b {
        color: white;
        position: absolute;
        display: inline-block;
        margin-left: 10px;
    }

    .em-option-price p {
        display: inline-block;
        position: absolute;
        color: white;
        margin-left: 10px;
        margin-top: 0px;
    }

    .em-option-buttons {
        padding-bottom: 10px;
    }

    .em-option-login {
        display: block;
        margin-left: auto;
        margin-right: auto;
        width: 85%;
        background-color: #E50043;
        color: white;
        font-weight: bold;
        margin-bottom: 8px;
    }

    .em-option-contact {
        display: block;
        margin-left: auto;
        margin-right: auto;
        width: 85%;
        background-color: #bbc0c3;
        color: white;
        font-weight: bold;
    }

    #sur-mesure-details {
        height: 300px;
        max-width: 60%;
    }

    #sur-mesure-details b {
        margin-bottom: 10px;
    }


    .em-search {
        background-color: #e2e2d0;
        display: block;
        width: 100%;
        height: 120px;
        margin-top: 10px;
    }

    .em-search-bar {
        display: inline;
    }

    .em-search b {
        display: block;
        padding-top: 10px;
        padding-bottom: 15px;
        margin-left: 15px;
        font-size: 15px;
    }

    .em-formations {
        display: block;
        width: 100%;
        height: auto;
        margin-top: 10px;
    }

    .em-themes {
        width: 100%;
        height: auto;
        margin-bottom: 5px;
        cursor: pointer;
    }

    .em-themes p{
        color: white;
        font-size: 15px;
        padding: 2px;
        margin-left: 10px;
    }

    .em-theme-management {
        background-color: #81266B;
    }

    .em-theme-quality {
         background-color: #55AD32;
     }

    .em-theme-sale {
        background-color: #DC4A14;
    }

    .em-theme-buy {
        background-color: #C0A512;
    }

    .em-theme-formation {
        background-color: #0483A2;
    }

    .em-theme-digital {
        background-color: #F5A405;
    }

    .em-theme-accounting {
        background-color: #52BDD5;
    }

    .em-theme-language {
        background-color: #E50043;
    }

    .em-statuts {
        width: 100%;
        margin-top: 10px;
        height: auto;
        margin-bottom: 20px;
    }

    .em-statut {
        width: 100%;
        margin-bottom: 5px;
        height: auto;
        background-color: #e2e2d0;
        cursor: pointer;
    }

    .em-statut p {
        margin-left: 10px;
    }

    .em-certification {
        width: 100%;
        background-color: #e2e2d0;
        height: 250px;
    }

</style>


<!-- Title -->
<!-- TODO: Get categories from cci and make div  before the title -->
<div class="g-grid">
    <div class="g-block size-70" id="offer">
        <div class="em-theme"></div>
        <p class="em-offre-title">
            <?php echo "<b>" . $this->data['jos_emundus_setup_teaching_unity___label_raw'] . "</b>"; ?>
        </p>

        <hr style="width: 97%; margin-bottom: 10px;">

        <div id="details">
            <div class="top-details">
                <div class="em-details" id="people">
                    <div class="em-details-icon em-icon-<?php echo $theme?>">
                        <?php echo $public_svg; ?>
                    </div>
                    <div id="em-people-detail">
                            <?php
                                if($this->data['jos_emundus_setup_teaching_unity___audiance_raw'] == null)
                                    echo "Toute personne amenée à travailler dans le cadre d’une démarche " .  $this->data['jos_emundus_setup_programme___programmes_raw'];
                                else
                                    echo $this->data['jos_emundus_setup_teaching_unity___audiance_raw'];
                            ?>
                    </div>

                </div>

                <div class="em-details" id="date">
                    <div class="em-details-icon em-icon-<?php echo $theme?>">
                        <?php echo $date_svg; ?>
                    </div>
                    <div id="em-date-detail">
                        <p class="em-date">
                            <?php
                                setlocale(LC_ALL, 'fr_FR');
                                $start_month = date('m',strtotime($this->data['jos_emundus_setup_teaching_unity___date_start_raw']));
                                $end_month = date('m',strtotime($this->data['jos_emundus_setup_teaching_unity___date_end_raw']));
                                $start_year = date('y',strtotime($this->data['jos_emundus_setup_teaching_unity___date_start_raw']));
                                $end_year = date('y',strtotime($this->data['jos_emundus_setup_teaching_unity___date_end_raw']));
                                $days = $this->data['jos_emundus_setup_teaching_unity___days_raw'];


                                if($days > 1) {
                                    if($start_month == $end_month && $start_year == $end_year)
                                        echo strftime('%e',strtotime($this->data['jos_emundus_setup_teaching_unity___date_start_raw'])) . " au " . strftime('%e',strtotime($this->data['jos_emundus_setup_teaching_unity___date_end_raw'])) . " " . strftime('%B',strtotime($this->data['jos_emundus_setup_teaching_unity___date_end_raw'])) . " " . date('Y',strtotime($this->data['jos_emundus_setup_teaching_unity___date_end_raw']));
                                    elseif ($start_month != $end_month && $start_year == $end_year)
                                        echo strftime('%e',strtotime($this->data['jos_emundus_setup_teaching_unity___date_start_raw'])) . " " . strftime('%B',strtotime($this->data['jos_emundus_setup_teaching_unity___date_end_raw'])). " au " . strftime('%e',strtotime($this->data['jos_emundus_setup_teaching_unity___date_end_raw'])) . " " . strftime('%B',strtotime($this->data['jos_emundus_setup_teaching_unity___date_end_raw'])) . " " . date('Y',strtotime($this->data['jos_emundus_setup_teaching_unity___date_end_raw']));
                                    elseif (($start_month != $end_month && $start_year != $end_year) || ($start_month == $end_month && $start_year != $end_year))
                                        echo strftime('%e',strtotime($this->data['jos_emundus_setup_teaching_unity___date_start_raw'])) . " " . strftime('%B',strtotime($this->data['jos_emundus_setup_teaching_unity___date_end_raw'])). " " . date('Y',strtotime($this->data['jos_emundus_setup_teaching_unity___date_start_raw'])) . " au " . strftime('%e',strtotime($this->data['jos_emundus_setup_teaching_unity___date_end_raw'])) . " " . strftime('%B',strtotime($this->data['jos_emundus_setup_teaching_unity___date_end_raw'])) . " " . date('Y',strtotime($this->data['jos_emundus_setup_teaching_unity___date_end_raw']));

                                }

                            ?>
                        </p>
                        <p class="em-days">
                            <?php
                                if($days > 1)
                                    echo $days . " jours";
                                else
                                    echo $days . " jour";
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bottom-details">

                <div class="em-details" id="requirements">
                    <div class="em-details-icon em-icon-<?php echo $theme?>">
                        <?php echo $prerequis_svg; ?>
                    </div>
                    <div id="em-requirements-detail">
                        <?php
                          if($this->data['jos_emundus_setup_teaching_unity___prerequisite_raw'] == null)
                              echo "Pas de prérequis nécessaire";
                          else
                              echo $this->data['jos_emundus_setup_teaching_unity___prerequisite_raw'];
                        ?>
                    </div>
                </div>

                <div class="em-details" id="location">
                    <div class="em-details-icon em-icon-<?php echo $theme?>">
                        <?php echo $lieu_svg; ?>
                    </div>
                    <div id="em-location-detail">
                        <p>
                            <?php echo ucfirst(strtolower($this->data['jos_emundus_setup_teaching_unity___location_city_raw'])); ?>
                        </p>
                    </div>
                </div>

            </div>
        </div>

        <div id="partner">
            <p>notre partenaire expert</p>
            <!-- TODO: get partners photo -->
        </div>

        <hr style="width: 97%; margin-top: 0px;">


        <div class="em-offer">

            <div id="objectif">

                <div class="offer-icon em-icon-<?php echo $theme?>" id="objectif-icon-<?php echo $theme?>">
                    <?php echo $objectif_svg; ?>
                </div>

                <div id="objectif-details">
                    <b style="font-size: 25px;">Objectifs</b>
                    <!-- TODO: Here goes the objectifs-->
                </div>

            </div>

            <div id="keys">
                <div class="offer-icon em-icon-<?php echo $theme?>" id="keys-icon-<?php echo $theme?>">
                    <?php echo $pointscles_svg; ?>
                </div>

                <div id="key-details">
                    <b style="font-size: 25px;">Points clés</b>
                    <!-- TODO: Here goes the keys-->
                </div>

            </div>

            <div id="certificate">
                <div class="offer-icon em-icon-<?php echo $theme?>" id="certificate-icon-<?php echo $theme?>">
                    <?php echo $diplomant_svg; ?>
                </div>

                <div id="certificate-details">
                    <b style="font-size: 25px;">Certification ou diplôme</b>
                    <!-- TODO: Here goes the certification that is based on the theme -->
                </div>
            </div>

        </div>
    </div>

    <div class="g-block size-30" id="aside">

        <div class="em-options">

            <div class="em-option-menu active" id="em-option-menu-inter">
                <b>INTER</b>
            </div>

            <div class="em-option-menu" id="em-option-menu-intra">
                <b>INTRA</b>
            </div>

            <div class="em-option-menu" id="em-option-menu-sur-mesure">
                <b>SUR-MESURE</b>
            </div>

            <div class="em-option" id="em-option-inter">
                <div class="em-option-details">
                    <?php echo "<p class='em-option-title'>" . $this->data['jos_emundus_setup_teaching_unity___label_raw'] . "</p>"; ?>
                    <?php echo "<p style='margin-top: -10px;'>réf. " . $this->data['jos_emundus_setup_teaching_unity___code_raw'] . "</p>"; ?>
                </div>

                <div class="em-option-price">
                    <?php echo $prix_svg . '<b style="display: inline-block;">' . intval($this->data['jos_emundus_setup_teaching_unity___price_raw']) . ' € net de taxe</b>'; ?>
                </div>

                <div class="em-option-certificate">
                    <?php echo $diplomant_svg . '<b style="display: inline-block;">INTER Certificat</b>'; ?>
                </div>

                <div class="em-option-documents">
                    <?php echo $telechargement_svg . '<b style="display: inline-block;">INTER list of docs</b>'; ?>
                </div>

                <div class="em-option-buttons">
                    <button class="em-option-login">s'inscrire</button>

                    <button class="em-option-contact">être contacté par un conseiller</button>
                </div>


            </div>

            <div class="em-option hide" id="em-option-intra">
                <div class="em-option-details">
                    <?php echo "<p class='em-option-title'>" . $this->data['jos_emundus_setup_teaching_unity___label_raw'] . "</p>"; ?>
                    <?php echo "<p style='margin-top: -10px;'>réf. " . $this->data['jos_emundus_setup_teaching_unity___code_raw'] . "</p>"; ?>
                </div>

                <div class="em-option-price">
                    <?php echo $prix_svg . '<p style="font-weight: bold;">' . intval($this->data['jos_emundus_setup_teaching_unity___price_raw']) . ' € net de taxe tarif par personne</p> <p style="margin-top: 20px;">(' . $this->data['jos_emundus_setup_teaching_unity___min_occupants_raw'] . ' personnes minimum)</p>'; ?>
                </div>

                <div class="em-option-certificate">
                    <?php echo $diplomant_svg . '<b style="display: inline-block;">INTRA Certificat</b>'; ?>
                </div>

                <div class="em-option-documents">
                    <?php echo $telechargement_svg . '<b style="display: inline-block;">INTRA list of docs</b>'; ?>
                </div>

                <div class="em-option-buttons">
                    <button class="em-option-login">demander un devis</button>

                    <button class="em-option-contact">être contacté par un conseiller</button>
                </div>

            </div>

            <div class="em-option hide" id="em-option-sur-mesure">
                <div class="em-option-details" id="sur-mesure-details">
                    <b style="color: white"> Vous êtess intéressé par cette thématique mais vous avez besoin de spécifiques?</b>
                    <br>
                    <br>
                    <b style="color: white"> Nous pouvons élaborer une formation sur-mesure pour répondre au mieux à vos objectifs.</b>
                </div>

                <button class="em-option-contact" >être contacté par un conseiller</button>

            </div>
        </div>

        <div class="em-search">

            <b>cherchez une formation</b>

            <div class="em-search-bar">
                <div class="ui icon input" style="width: 90%; margin-left: 15px;">
                    <i class="search icon"></i>
                    <input type="text" placeholder="Search..">
                </div>
            </div>

        </div>

        <div class="em-formations">

            <div><b>Les formations</b></div>
            <div style="margin-top: -10px;"><b>par domaines de compétences</b></div>

            <div class="em-themes em-theme-management"><p>MANAGEMENT • RESSOURCES HUMAINES</p></div>
            <div class="em-themes em-theme-quality"><p>QUALITÉ • PERFORMANCE</p></div>
            <div class="em-themes em-theme-sale"><p>VENTE • DÉVELOPPEMENT COMMERCIAL</p></div>
            <div class="em-themes em-theme-buy"><p>ACHATS • APPROVISIONNEMENTS</p></div>
            <div class="em-themes em-theme-formation"><p>FORMATIONS RÉGLEMENTAIRES • SÉCURITÉ</p></div>
            <div class="em-themes em-theme-digital"><p>DIGITAL • BUREAUTIQUE</p></div>
            <div class="em-themes em-theme-accounting"><p>COMPTABILITÉ • GESTION</p></div>
            <div class="em-themes em-theme-language"><p>LANGUES</p></div>




        </div>

        <div class="em-statuts">
            <div><b>Vous êtes...</b></div>


            <div class="em-statut" id="dirigeant"><p>DIRIGEANT</p></div>
            <div class="em-statut" id="salarie"><p>SALARIÉ</p></div>
            <div class="em-statut" id="hotel-restaurant"><p>HÔTELIER / RESTAURATEUR</p></div>
            <div class="em-statut" id="immobilier"><p>PROFESSIONNEL DE L’IMMOBILIER</p></div>
            <div class="em-statut" id="entreprise"><p>CRÉATEUR / REPRENEUR D’ENTREPRISE</p></div>

        </div>

        <div class="em-certification em-certification-<?php echo $theme?>">

        </div>

    </div>
</div>




<script>

    jQuery(document).ready(function() {
    });

    document.getElementById("em-option-menu-inter").addEventListener('click', function (e) {
        e.stopPropagation();
        var intraMenu = document.getElementById("em-option-menu-intra");
        var mesureMenu = document.getElementById("em-option-menu-sur-mesure");
        var intra = document.getElementById("em-option-intra");
        var mesure = document.getElementById("em-option-sur-mesure");

        if (!$(this).classList.contains('active')) {
            if(intraMenu||mesureMenu) {
                intraMenu.classList.remove('active');
                mesureMenu.classList.remove('active');
                intra.classList.add('hide');
                mesure.classList.add('hide');
            }
            $(this).classList.add('active');
            document.getElementById("em-option-inter").classList.remove('hide');
        }
    });

    document.getElementById("em-option-menu-intra").addEventListener('click', function (e) {
        e.stopPropagation();
        var interMenu = document.getElementById("em-option-menu-inter");
        var mesureMenu = document.getElementById("em-option-menu-sur-mesure");
        var inter = document.getElementById("em-option-inter");
        var mesure = document.getElementById("em-option-sur-mesure");

        if (!$(this).classList.contains('active')) {
            if(interMenu||mesureMenu) {
                interMenu.classList.remove('active');
                mesureMenu.classList.remove('active');
                inter.classList.add('hide');
                mesure.classList.add('hide');
            }
            $(this).classList.add('active');
            document.getElementById("em-option-intra").classList.remove('hide');
        }
    });

    document.getElementById("em-option-menu-sur-mesure").addEventListener('click', function (e) {
        e.stopPropagation();
        var interMenu = document.getElementById("em-option-menu-inter");
        var intraMenu = document.getElementById("em-option-menu-intra");
        var inter = document.getElementById("em-option-inter");
        var intra = document.getElementById("em-option-intra");

        if (!$(this).classList.contains('active')) {
            if(interMenu||intraMenu) {
                interMenu.classList.remove('active');
                intraMenu.classList.remove('active');
                inter.classList.add('hide');
                intra.classList.add('hide');
            }
            $(this).classList.add('active');
            document.getElementById("em-option-sur-mesure").classList.remove('hide');
        }
    });

</script>

<?php
echo $this->pluginbottom;
echo $this->loadTemplate('actions');
echo '</div>';
echo $form->outro;
echo $this->pluginend;
