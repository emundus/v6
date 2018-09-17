<?php
/**
 * Fabrik List Template: Div
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die('Restricted access');

$doc = JFactory::getDocument();
$doc->addStyleSheet('media/com_emundus/lib/bootstrap-232/css/bootstrap.min.css');
$doc->addScript('media/com_emundus/lib/chosen/chosen.jquery.js');
$doc->addStyleSheet('media/com_emundus/lib/chosen/chosen.css');

// The number of columns to split the list rows into
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




?>

<style>

    .main {
        width: 100%;
    }

    .form {
        width: 100%;
    }

    .em-theme-title {
        display: inline-block;
    }
    .em-offre-title {
        font-size: 22px;
    }

    .details-table {
        display: inline-block;
        float: inherit;
        border: none;
        margin-bottom: 0px;
    }

    .details-table td {
        border: none;
    }

    .partner {
        display: inline-block;
        position: absolute;
    }

    .em-details-icon {
        display: inline-block;
    }

    /* TODO: do for each theme  */
    .em-icon-dse svg path {
        fill: #52BDD5 !important;
    }

    .em-icon-achat svg path {
        fill: #C0A512 !important;
    }

    .em-icon-compétences-et-formation svg path {
        fill: #0483A2 !important;
    }

    .em-icon-qualité svg path {
        fill: #55AD32 !important;
    }


    .em-details-icon svg, .em-option-price svg, .em-option-documents svg, .em-option-certificate svg {
        width: 40px;
        height: 40px;
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

    .searchButton {
        width: 20%;
        height: 41px;
        color: #566268;
        font-size: 20px;
        margin-top: -10px;
        margin-right: -4px;
    }

    #formation-search {
        display: inline-block;
        width: 75%;
        border: none;
        border-radius: 0px;
    }

    .em-statut {
        width: 100%;
        margin-bottom: 5px;
        height: auto;
        background-color: #e2e2d0;
        cursor: pointer;
        padding-left: 5px;
        font-size: 13px;
    }

    .em-themes {
        width: 100%;
        height: auto;
        cursor: pointer;
        margin-bottom: 5px;
        padding-left: 5px;
        font-size: 13px;
    }

    .em-themes a {
        color: white;
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

    .em-people-detail {
        display: inline-block;
        max-width: 75%;
        margin-left: 5px;
        line-height: 20px;
        margin-top: 10px;
    }

    /* date details */
    .em-date-detail {
        display: inline-block;
        line-height: 25px;
    }

    .em-date {
        margin-left: 10px;
        font-weight: bold;
        margin-top: 7px;
    }

    .em-days {
        margin-left: 10px;
        margin-top: -15px;
    }

    /* requirements details */
    .em-requirements-detail {
        display: inline-block;
        position: absolute;
        max-width: 15%;
        margin-left: 5px;
        line-height: 20px;
        margin-top: 12px;
    }

    /* location details */
    .em-location-detail{
        display: inline-block;
        margin-top: 5px;
        margin-left: 10px;
        font-weight: bold;
        position: absolute;
    }


    .limit {
        display: inline-block;
    }

    .pagination {
        display: inline-block;
        margin-left: 10%;
    }



</style>
<div class="main">
    <div class="form">
        <form class="fabrikForm form-search" action="<?php echo $this->table->action;?>" method="post" id="<?php echo $this->formid;?>" name="fabrikList">

			<?php
			if ($this->hasButtons)
				echo $this->loadTemplate('buttons');
			?>

            <div class="fabrikDataContainer">

				<?php foreach ($this->pluginBeforeList as $c) {
					echo $c;
				}


				$data = array();
				$i = 0;
				if (!empty($this->rows[0])) {
					foreach ($this->rows[0] as $k => $v) {
						foreach ($this->headings as $key => $val) {
                            $raw = $key.'_raw';
							if (array_key_exists($raw, $v->data)) {
								if (strcasecmp($v->data->$key, "1") == 0)
                                    $data[$i][$val] = $v->data->$key;

								else {
                                    $data[$i][$key] = $v->data->$key;
                                    $data[$i][$raw] = $v->data->$raw;
                                }

							}
						}

						if (array_key_exists('fabrik_view_url', $v->data)) {
							$data[$i]['fabrik_view_url'] = $v->data->fabrik_view_url;
						}
						$i = $i + 1;
					}
				}?>

                <div class="em-search-engine-filters">
					<?php if ($this->showFilters && $this->bootShowFilters)
						echo $this->layoutFilters();
					?>
                </div>

                <div class="em-search-engine-data">
                    <table>
                        <thead>
                            <tr>
                                <td><h3>RESULTAT DE LA RECHERCHE</h3></td>
                            </tr>
                        </thead>

                                <?php
                                $gCounter = 0;
                                foreach ($data as $d) {
                                    $title = ucfirst(mb_strtolower($d['jos_emundus_setup_teaching_unity___label_raw'],  'UTF-8'));
                                    $theme = strtolower(str_replace(' ','-',$d['jos_emundus_setup_programmes___programmes']));
                                    $theme =html_entity_decode($theme, ENT_QUOTES);

                                    // TODO: CASE FOR EACH THEME
                                    switch ($theme) {
                                        case 'dse':
                                            $div = "<div class=\"em-themes em-theme-title em-theme-accounting\"><a href=\"rechercher?category=dse\">COMPTABILITÉ • GESTION</a></div>";
                                            break;
                                        case 'achat':
                                            $div = "<div class=\"em-themes em-theme-title em-theme-buy\"><a href=\"rechercher?category=achat\">ACHATS • APPROVISIONNEMENTS</a></div>";
                                            break;
                                        case 'compétences-et-formation':
                                            $div = " <div class=\"em-themes em-theme-title em-theme-formation\"><a href=\"rechercher?category=cf\">FORMATIONS RÉGLEMENTAIRES • SÉCURITÉ</a></div>";
                                            break;
                                    }

                                    ?>
                                    <table class="details-table g-block size-100">
                                        <tr>
                                            <?php echo $div; ?>
                                            <p class="em-offre-title">
                                                <?php echo "<b>" . $title . "</b>"; ?>
                                            </p>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="em-details-icon em-icon-<?php echo $theme?>">
                                                    <?php echo $public_svg; ?>
                                                </div>
                                                <div class="em-people-detail">
                                                    <?php
                                                    if($d['jos_emundus_setup_teaching_unity___audiance'] == null)
                                                        echo "Toute personne amenée à travailler dans le cadre d’une démarche " .  $d['jos_emundus_setup_programme___programmes'];
                                                    else
                                                        echo $d['jos_emundus_setup_teaching_unity___audiance'];
                                                    ?>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="em-details-icon em-icon-<?php echo $theme?>">
                                                    <?php echo $date_svg; ?>
                                                </div>
                                                <div class="em-date-detail">
                                                    <p class="em-date">
                                                        <?php
                                                        setlocale(LC_ALL, 'fr_FR');
                                                        $start_month = date('m',strtotime($d['jos_emundus_setup_teaching_unity___date_start']));
                                                        $end_month = date('m',strtotime($d['jos_emundus_setup_teaching_unity___date_end']));
                                                        $start_year = date('y',strtotime($d['jos_emundus_setup_teaching_unity___date_start']));
                                                        $end_year = date('y',strtotime($d['jos_emundus_setup_teaching_unity___date_end']));
                                                        $days = $d['jos_emundus_setup_teaching_unity___days'];
                                                        if($days > 1) {
                                                            if($start_month == $end_month && $start_year == $end_year)
                                                                echo strftime('%e',strtotime($d['jos_emundus_setup_teaching_unity___date_start'])) . " au " . strftime('%e',strtotime($d['jos_emundus_setup_teaching_unity___date_end'])) . " " . strftime('%B',strtotime($d['jos_emundus_setup_teaching_unity___date_end'])) . " " . date('Y',strtotime($d['jos_emundus_setup_teaching_unity___date_end']));
                                                            elseif ($start_month != $end_month && $start_year == $end_year)
                                                                echo strftime('%e',strtotime($d['jos_emundus_setup_teaching_unity___date_start'])) . " " . strftime('%B',strtotime($d['jos_emundus_setup_teaching_unity___date_end'])). " au " . strftime('%e',strtotime($d['jos_emundus_setup_teaching_unity___date_end'])) . " " . strftime('%B',strtotime($d['jos_emundus_setup_teaching_unity___date_end'])) . " " . date('Y',strtotime($d['jos_emundus_setup_teaching_unity___date_end']));
                                                            elseif (($start_month != $end_month && $start_year != $end_year) || ($start_month == $end_month && $start_year != $end_year))
                                                                echo strftime('%e',strtotime($d['jos_emundus_setup_teaching_unity___date_start'])) . " " . strftime('%B',strtotime($d['jos_emundus_setup_teaching_unity___date_end'])). " " . date('Y',strtotime($d['jos_emundus_setup_teaching_unity___date_start'])) . " au " . strftime('%e',strtotime($d['jos_emundus_setup_teaching_unity___date_end'])) . " " . strftime('%B',strtotime($d['jos_emundus_setup_teaching_unity___date_end'])) . " " . date('Y',strtotime($d['jos_emundus_setup_teaching_unity___date_end']));
                                                        }
                                                        elseif ($days = 1)
                                                            echo strftime('%e',strtotime($d['jos_emundus_setup_teaching_unity___date_start'])) . " " . strftime('%B',strtotime($d['jos_emundus_setup_teaching_unity___date_end'])). " " . date('Y',strtotime($d['jos_emundus_setup_teaching_unity___date_start']));
                                                        else
                                                            echo "Pas de jours définis";

                                                        ?>
                                                    </p>
                                                    <p class="em-days">
                                                        <?php
                                                        if($days > 1)
                                                            echo $days . " jours";
                                                        elseif($days = 1)
                                                            echo $days . " jour";
                                                        ?>
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <div class="em-details-icon em-icon-<?php echo $theme?>">
                                                    <?php echo $prerequis_svg; ?>
                                                </div>
                                                <div class="em-requirements-detail">
                                                    <?php
                                                    if($d['jos_emundus_setup_teaching_unity___prerequisite'] == null)
                                                        echo "Pas de prérequis nécessaire";
                                                    else
                                                        echo $d['jos_emundus_setup_teaching_unity___prerequisite'];
                                                    ?>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="em-details-icon em-icon-<?php echo $theme?>">
                                                    <?php echo $lieu_svg; ?>
                                                </div>
                                                <div class="em-location-detail">
                                                    <?php
                                                        if(!empty($d['jos_emundus_setup_teaching_unity___location_city']))
                                                            echo ucfirst(strtolower($d['jos_emundus_setup_teaching_unity___location_city']));
                                                        else
                                                            echo "Pas de localisation";
                                                    ?>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>

                                    <div class='em-search-engine-details'><a href="<?php echo $d['fabrik_view_url']; ?>">Consultez l'offre</a></div>


                                    <?php
                                    echo "<hr>";
                                    $gCounter++;
                                }

                                ?>

                        <tfoot>
                            <tr class="fabrik___heading">
                                <td colspan="<?php echo count($this->headings);?>">
                                    <?php echo $this->nav;?>
                                </td>
                            </tr>
                        </tfoot>


                        <?php if ($this->hasCalculations) : ?>
                            <tfoot>
                            <tr class="fabrik_calculations">

								<?php foreach ($this->headings as $key => $heading) :
									$h = $this->headingClass[$key];
									$style = empty($h['style']) ? '' : 'style="' . $h['style'] . '"'; ?>
                                    <td class="<?php echo $h['class']?>" <?php echo $style?>>
										<?php
										$cal = $this->calculations[$key];
										echo array_key_exists($groupedBy, $cal->grouped) ? $cal->grouped[$groupedBy] : $cal->calc;
										?>
                                    </td>
								<?php
								endforeach;
								?>

                            </tr>
                            </tfoot>
						<?php endif ?>
                    </table>
                </div>

				<?php print_r($this->hiddenFields);?>
            </div>
        </form>
    </div>

    <script>

        jQuery(document).ready(function(){
            jQuery('select.fabrik_filter[multiple]').chosen({
                placeholder_text_single: "<?php echo JText::_('CHOSEN_SELECT_ONE'); ?>",
                placeholder_text_multiple: "<?php echo JText::_('CHOSEN_SELECT_MANY'); ?>",
                no_results_text: "<?php echo JText::_('CHOSEN_NO_RESULTS'); ?>"
            });
        });
    </script>

</div>
