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
$doc->addStyleSheet('templates/g5_helium/custom/css/moteur-de-recherche.css');

// The number of columns to split the list rows into
$pageClass = $this->params->get('pageclass_sfx', '');

function jsonDecode($val) {
    if(empty(json_decode($val)))
        return $val;
    else
        return json_decode($val);
}


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


				if (!empty($this->rows)) {
					foreach ($this->rows as $k => $v) {
						foreach ($this->headings as $key => $val) {
                            $raw = $key.'_raw';
							if (array_key_exists($raw, $v[0]->data)) {
								if (strcasecmp($v[0]->data->$key, "1") == 0)
                                    $data[$i][$val] = $v[0]->data->$key;

								else {
                                    $data[$i][$key] = $v[0]->data->$key;
                                    $data[$i][$raw] = $v[0]->data->$raw;
                                }
							}
						}
                        if (array_key_exists('fabrik_view_url', $v[0]->data)) {
                            $data[$i]['fabrik_view_url'] = $v[0]->data->fabrik_view_url;
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
                                    $title = ucfirst(mb_strtolower(jsonDecode($d['jos_emundus_setup_programmes___label_raw'])));


                                    $theme = mb_strtolower(str_replace(' ','-',$d['jos_emundus_setup_programmes___programmes_raw']));
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
                                    <div class="details-table g-block size-100">
                                        <?php echo $div; ?>
                                        <h1 class="em-offre-title">
                                            <?php echo "<a href='".$d['fabrik_view_url']."' >" . $title . "</a>"; ?>
                                        </h1>
                                        <div>
                                            <div class="people-div g-block size-50">
                                                <div class="em-details-icon em-icon-<?php echo $theme?>">
                                                    <?php echo $public_svg; ?>
                                                </div>
                                                <div class="em-people-detail">
                                                    <?php
                                                        if($d['jos_emundus_setup_teaching_unity___audiance_raw'] == null)
                                                            echo "Toute personne amenée à travailler dans le cadre d’une démarche " .  str_replace('-', ' ', $theme);
                                                        else
                                                            echo $d['jos_emundus_setup_teaching_unity___audiance_raw'];
                                                    ?>
                                                </div>
                                            </div>

                                            <div class="date-div g-block size-49">
                                                <div class="em-details-icon em-icon-<?php echo $theme?>">
                                                    <?php echo $date_svg; ?>
                                                </div>
                                                <div class="em-date-detail">
                                                    <p class="em-date">
                                                        <?php
                                                        setlocale(LC_ALL, 'fr_FR');
                                                        $dateArray =jsonDecode($d['jos_emundus_setup_campaigns___start_date_raw']);
                                                        $start_month = date('m',strtotime($d['jos_emundus_setup_campaigns___start_date_raw']));
                                                        $end_month = date('m',strtotime($d['jos_emundus_setup_teaching_unity___date_end_raw']));
                                                        $start_year = date('y',strtotime($d['jos_emundus_setup_teaching_unity___date_start_raw']));
                                                        $end_year = date('y',strtotime($d['jos_emundus_setup_teaching_unity___date_end_raw']));
                                                        $days = $d['jos_emundus_setup_teaching_unity___days_raw'];
                                                        if(sizeof($dateArray) > 1) {
                                                            $lastEl = array_values(array_slice($dateArray, -1))[0];
                                                            $start_month = date('m',strtotime($dateArray[0]));
                                                            $end_month = date('m',strtotime($lastEl));
                                                            $start_year = date('Y',strtotime($dateArray[0]));
                                                            $end_year = date('Y',strtotime($lastEl));

                                                            if($start_month == $end_month && $start_year == $end_year)
                                                                echo "Plusieurs sessions en " . ucfirst(strftime('%B',strtotime($dateArray[0]))) . ' ' . $start_year;
                                                            elseif($start_month != $end_month && $start_year == $end_year)
                                                                echo "Plusieurs sessions en " . ucfirst(strftime('%B',strtotime($dateArray[0]))) . ' à ' . ucfirst(strftime('%B',strtotime($lastEl))) . ' ' . $start_year;
                                                            elseif (($start_month != $end_month && $start_year != $end_year) || ($start_month == $end_month && $start_year != $end_year))
                                                                echo "Plusieurs sessions en " . ucfirst(strftime('%B',strtotime($dateArray[0]))) . ' ' . $start_year . ' à ' . ucfirst(strftime('%B',strtotime($lastEl))) . ' ' . $end_year;
                                                        }
                                                        else {
                                                            if($days > 1) {
                                                                if($start_month == $end_month && $start_year == $end_year)
                                                                    echo strftime('%e',strtotime($d['jos_emundus_setup_teaching_unity___date_start_raw'])) . " au " . strftime('%e',strtotime($d['jos_emundus_setup_teaching_unity___date_end_raw'])) . " " . ucfirst(strftime('%B',strtotime($d['jos_emundus_setup_teaching_unity___date_end_raw']))) . " " . date('Y',strtotime($d['jos_emundus_setup_teaching_unity___date_end_raw']));
                                                                elseif ($start_month != $end_month && $start_year == $end_year)
                                                                    echo strftime('%e',strtotime($d['jos_emundus_setup_teaching_unity___date_start_raw'])) . " " . ucfirst(strftime('%B',strtotime($d['jos_emundus_setup_teaching_unity___date_start_raw']))) . " au " . strftime('%e',strtotime($d['jos_emundus_setup_teaching_unity___date_end_raw'])) . " " . ucfirst(strftime('%B',strtotime($d['jos_emundus_setup_teaching_unity___date_end_raw']))) . " " . date('Y',strtotime($d['jos_emundus_setup_teaching_unity___date_end_raw']));
                                                                elseif (($start_month != $end_month && $start_year != $end_year) || ($start_month == $end_month && $start_year != $end_year))
                                                                    echo strftime('%e',strtotime($d['jos_emundus_setup_teaching_unity___date_start_raw'])) . " " . ucfirst(strftime('%B',strtotime($d['jos_emundus_setup_teaching_unity___date_end_raw']))) . " " . date('Y',strtotime($d['jos_emundus_setup_teaching_unity___date_start_raw'])) . " au " . strftime('%e',strtotime($d['jos_emundus_setup_teaching_unity___date_end_raw'])) . " " . ucfirst(strftime('%B',strtotime($d['jos_emundus_setup_teaching_unity___date_end_raw']))) . " " . date('Y',strtotime($d['jos_emundus_setup_teaching_unity___date_end_raw']));

                                                            }
                                                            elseif ($days = 1)
                                                                echo strftime('%e',strtotime($d['jos_emundus_setup_teaching_unity___date_start_raw'])) . " " . strftime('%B',strtotime($d['jos_emundus_setup_teaching_unity___date_end_raw'])). " " . date('Y',strtotime($d['jos_emundus_setup_teaching_unity___date_start_raw']));
                                                            else
                                                                echo "Pas de jours définis";
                                                        }

                                                        ?>
                                                    </p>
                                                    <p class="em-days">
                                                        <?php
                                                        if($days > 1)
                                                            echo "Durée de la formation : " . $days . " jours";
                                                        elseif($days = 1)
                                                            echo "Durée de la formation : " . $days . " jour";
                                                        ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <div class="location-div g-block size-50">
                                                <div class="em-details-icon em-icon-<?php echo $theme?>">
                                                    <?php echo $lieu_svg; ?>
                                                </div>
                                                <div class="em-location-detail">
                                                    <?php
                                                        $cityArray = jsonDecode($d['jos_emundus_setup_teaching_unity___location_city_raw']);
                                                        if(!empty($d['jos_emundus_setup_teaching_unity___location_city_raw'])) {
                                                            if (sizeof($cityArray) > 1) {
                                                                $cityArray = array_unique($cityArray);
                                                                $len = count($cityArray);
                                                                foreach ($cityArray as $cities) {
                                                                    echo str_replace(" cedex", "", ucfirst(strtolower($cities)));
                                                                    if ($len > 1) echo ', ';
                                                                    $len--;
                                                                }
                                                            } else
                                                                echo str_replace(" cedex", "", ucfirst(strtolower($cityArray)));
                                                        }
                                                        else
                                                            echo "Pas de localisation";
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class='em-search-engine-details'><a href="<?php echo $d['fabrik_view_url']; ?>">En savoir plus</a></div>


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
