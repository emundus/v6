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
    if (empty(json_decode($val)))
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
							    $data[$i][$key] = $v[0]->data->$key;
                                $data[$i][$raw] = $v[0]->data->$raw;
							}
						}
                        if (array_key_exists('fabrik_view_url', $v[0]->data)) {
                            $data[$i]['fabrik_view_url'] = $v[0]->data->fabrik_view_url;
                        }
						$i = $i + 1;
					}
				}
				?>

                <div class="em-search-engine-filters">
					<?php if ($this->showFilters && $this->bootShowFilters)
						echo $this->layoutFilters();
					?>
                </div>

                <div class="em-search-engine-data">

                        <?php if(sizeof($data) > 1) :?>
                            <h2><?php echo sizeof($data) ;?> formations trouvées</h2>
                        <?php elseif (sizeof($data) == 1) :?>
                            <h2><?php echo sizeof($data) ;?> formation trouvée</h2>
                        <?php else :?>
                            <h2>Pas de formations trouvées</h2>
                        <?php endif; ?>

                                <?php
                                $gCounter = 0;
                                foreach ($data as $d) {
                                    $days = $d['jos_emundus_setup_teaching_unity___days_raw'];

                                    $title = ucfirst(mb_strtolower(jsonDecode($d['jos_emundus_setup_programmes___label_raw'])));

                                    // Parse theme info because Fabrik groups them if there are multiple.
                                    $theme_color = jsonDecode($d['jos_emundus_setup_thematiques___color_raw']);
                                    if (is_array($theme_color))
                                        $theme_color = $theme_color[0];

                                    $theme_title = jsonDecode($d['jos_emundus_setup_thematiques___title_raw']);
	                                if (is_array($theme_title))
		                                $theme_title = $theme_title[0];

	                                $theme_label = jsonDecode($d['jos_emundus_setup_thematiques___label_raw']);
	                                if (is_array($theme_label))
		                                $theme_label = $theme_label[0];


	                                if(($gCounter % 2) == 1) {
	                                    $class = "light-stripe";
                                    }
                                    else
                                        $class = "dark-stripe";
                                    ?>


                                    <div class="g-block size-100 <?php echo $class; ?>">
                                        <div class="em-top-details">
                                            <div class="em-title">
                                                <h3 class="em-offre-title">
                                                    <?php echo "<a href='".$d['fabrik_view_url']."' >" . $title . "</a>"; ?>
                                                </h3>
                                            </div>

                                            <div class="em-themes em-theme-title em-theme-<?php echo $theme_color; ?>">
                                                <a href="rechercher?category=<?php echo str_replace(['é','è','ê'],'e', html_entity_decode(mb_strtolower(str_replace(' ','-', $theme_title))));?>"><?php echo $theme_label; ?></a>
                                            </div>
                                        </div>

                                        <div class="em-bottom-details">

                                            <div class="em-people-details">
                                                <p>
                                                    <?php
                                                    if (!empty($d['jos_emundus_setup_programmes___audience_raw']))
                                                        echo $d['jos_emundus_setup_programmes___audience_raw'];
                                                    else
                                                        echo "Aucun public précisé."
                                                    ?>
                                                </p>
                                            </div>

                                            <div  class="em-day-details">
                                                <p>
                                                    <?php
                                                    if ($days > 1)
                                                        echo $days." jours";
                                                    elseif ($days = 1)
                                                        echo $days." jour";
                                                    ?>
                                                </p>
                                            </div>

                                        </div>

                                    </div>

                                    <?php
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
