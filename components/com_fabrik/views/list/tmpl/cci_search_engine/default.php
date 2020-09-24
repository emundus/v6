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
    if (empty(json_decode($val))) {
        return $val;
    } else {
        return json_decode($val);
    }
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

$page_title = "Rechercher une formation";

$jinput = JFactory::getApplication()->input;
$category = $jinput->get->get('category');
$cible = $jinput->get->get('cible');

if (!empty($category)) {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query
        ->select('*')
        ->from($db->quoteName('#__emundus_setup_thematiques'))
        ->where($db->quoteName('title') . ' LIKE ' . $db->quote($category));

    $db->setQuery($query);
    $category = $db->loadAssoc();

    $page_title .= " en ".$category['label'];
}

if (!empty($cible)) {
    switch ($cible) {
        case 'dirigeant' :
            $cible = "DIRIGEANT";
        break;

        case 'salarie':
            $cible = "SALARIÉ";
        break;

        case 'hotel-restaurant' :
            $cible = "HÔTELIER - RESTAURATEUR";
        break;

        case 'immobilier' :
            $cible = "PROFESSIONNEL DE L’IMMOBILIER";
        break;

        case 'createur' :
            $cible = "CRÉATEUR - REPRENEUR D’ENTREPRISE";
        break;

        default:
            $cible = strtoupper($cible);
        break;
    }

    $page_title .= " pour ".$cible;
}


function getThematic($id) {
	$db = JFactory::getDbo();
	// Get the list of categories.
	$query = $db->getQuery(true);
	$query
		->select('*')
		->from($db->quoteName('#__emundus_setup_thematiques'))
		->where($db->qn('id') . ' = ' . $id);
	$db->setQuery($query);
	try {
		return $db->loadObject();
	} catch (Exception $e) {
		JLog::add('Error getting programme codes in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
	}
}

$doc->setTitle($page_title);
?>

<div class="main">
    <div class="form">
        <form class="fabrikForm form-search" action="<?php echo $this->table->action;?>" method="post" id="<?php echo $this->formid;?>" name="fabrikList">

            <?php if (!empty($category)) :?>
                <div class="theme-filter">
                    <div class="em-themes em-theme-title em-theme-<?php echo $category['color']; ?>">
                        <?php echo $category['label']; ?>
                    </div>
                    <a href="/recherche"><span aria-hidden="true">&times;</span></a>
                </div>
            <?php endif; ?>

            <?php if (!empty($cible)) :?>
                <div class="theme-filter">
                    <div class="em-filter-cible">
                        <?php echo $cible; ?>
                    </div>
                    <a href="/rechercher"><span aria-hidden="true">&times;</span></a>
                </div>
            <?php endif; ?>

			<?php if ($this->hasButtons):
				echo $this->loadTemplate('buttons');
			 endif; ?>

            <div class="em-search-engine-filters">
		        <?php if ($this->showFilters && $this->bootShowFilters) :
			        echo $this->layoutFilters();
		        endif; ?>
            </div>


            <div class="fabrikDataContainer">

				<?php foreach ($this->pluginBeforeList as $c) :
					echo $c;
				 endforeach;

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

                <div class="em-search-engine-data">

                        <?php if ($this->navigation->total > 1) :?>
                            <h2><?php echo $this->navigation->total; ?> formations trouvées</h2>
                        <?php elseif ($this->navigation->total == 1) :?>
                            <h2><?php echo $this->navigation->total ;?> formation trouvée</h2>
                        <?php else :?>
                            <h2>
                                Aucune formation trouvée <br>
                                Il existe forcément une formation adaptée à votre demande. <br>
                                Appelez-nous au <a href="tel:+33546847092">05 46 84 70 92</a>
                            </h2>
                        <?php endif; ?>

                                <?php
                                $gCounter = 0;
                                foreach ($data as $d) {
                                    $cat_div = "";

                                    $themes = explode(', ', $d['jos_emundus_setup_programmes___programmes_raw']);


                                    if (sizeof($themes) > 1) {
                                        foreach ($themes as $theme) {
                                            $t = getThematic($theme);
                                            $cat_div .= "<div class=\"em-theme em-theme-$t->color\"><a href=\"/formations/".str_replace(['é','è','ê'],'e', html_entity_decode(mb_strtolower(str_replace('---','-', $t->title))))."\">$t->label</a></div>";
                                        }
                                    }
                                    else {
                                        // Parse theme info because Fabrik groups them if there are multiple.
                                        $theme_color = jsonDecode($d['jos_emundus_setup_thematiques___color_raw']);
                                        if (is_array($theme_color)) {
                                            $theme_color = $theme_color[0];
                                        }

                                        $theme_title = jsonDecode($d['jos_emundus_setup_thematiques___title_raw']);

                                        if (is_array($theme_title)) {
                                            $theme_title = $theme_title[0];
                                        }

                                        $theme_label = jsonDecode($d['jos_emundus_setup_thematiques___label_raw']);
                                        if (is_array($theme_label)) {
                                            $theme_label = $theme_label[0];
                                        }

                                        $cat_div = "<div class=\"em-theme em-theme-$theme_color\"><a href=\"/formations/".str_replace(['é','è','ê'],'e', html_entity_decode(mb_strtolower(str_replace('---','-', $d['jos_emundus_setup_thematiques___title_raw']))))."\">$theme_label</a></div>";
                                    }

                                    $days = jsonDecode($d['jos_emundus_setup_teaching_unity___days_raw']);
                                    if (is_array($days)) {
                                        $days = $days[0];
                                    }

                                    $title = jsonDecode($d['jos_emundus_setup_teaching_unity___label_raw']);

                                    if (is_array($title)) {
                                        $title = $title[0];
                                    }

                                    if (($gCounter % 2) == 1) {
                                        $class = "light-stripe";
                                    } else {
                                        $class = "dark-stripe";
                                    }

                                    ?>


                                    <div class="g-block size-100 <?php echo $class; ?>">
                                        <div class="em-top-details">
                                            <div class="em-title">
                                                <h3 class="em-offre-title">
                                                    <?php echo "<a href='".$d['fabrik_view_url']."' >".$title."</a>"; ?>
                                                    <?php if (!empty($d['jos_emundus_setup_programmes___numcpf_raw'])) :?>
                                                        <img class="cpf-picto" src="images/custom/ccirs/icons/picto-CPF1.png" alt="cpf-icon"></img>
                                                    <?php endif; ?>
                                                </h3>
                                            </div>

                                            <div class="em-themes em-theme-title">
                                                <?php echo $cat_div;?>
                                            </div>

                                        </div>

                                        <div class="em-bottom-details">

                                            <div class="em-people-details">
                                                <p>
                                                    <?php
                                                        if (!empty($d['jos_emundus_setup_programmes___audience_raw'])) {
                                                            echo $d['jos_emundus_setup_programmes___audience_raw'];
                                                        }
                                                        else {
                                                            echo "Aucun public précisé.";
                                                        }
                                                    ?>
                                                </p>
                                            </div>

                                            <div  class="em-day-details">
                                                <p>
                                                    <?php
                                                    if (floatval($days) > 1) {
                                                        echo $days." jours";
                                                    }
                                                    elseif (floatval($days) == 1) {
                                                        echo $days." jour";
                                                    }
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
                                <?php if (!empty($data)) :?>
                                    <td colspan="<?php echo count($this->headings);?>">
                                        <?php echo $this->nav;?>
                                    </td>
                                <?php endif; ?>
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

            <?php if (!empty(JFactory::getApplication()->input->post->getString('search'))) :?>
                document.getElementById("formation-search").value = '<?php echo JFactory::getApplication()->input->post->getString('search'); ?>';
            <?php endif; ?>

            /* This fixes the issue with Fakrik not having GREATER or LESS THAN in date filters. */
            fixDateRangeFilters();

            /* This fixes the issue with Fabrik not having CONTAINS checkbox. */
            singleToggleCheckboxes();
        });

        function singleToggleCheckboxes() {

            /* Build a single checkbox that controls multiple. */
            var cpfContainer = jQuery('[data-filter-row="jos_emundus_setup_programmes___numcpf"]');
            cpfContainer.children().hide();
            cpfContainer.append('' +
                '<div class="row-fluid">\n' +
                    '<div class="fabrikgrid_checkbox span12">' +
                        '<label for="cpfCheckbox" class="checkbox">\n' +
                        '\t<input type="checkbox" class="fabrikinput  fabrik_filter" name="cpfCheckbox" id="cpfCheckbox"  onchange="toggleCPF(this);"><span>éligible au CPF</span></label>\n' +
                    '</div>\n' +
                '</div>');

            /* Precheck the checkbox in case data is already selected in the session. */
            if (jQuery('[data-filter-row="jos_emundus_setup_programmes___numcpf"] input:checkbox:checked').not('#cpfCheckbox').length > 0)
                jQuery('#cpfCheckbox').prop('checked', true);

            /* Build a single checkbox that controls multiple. */
            var certContainer = jQuery('[data-filter-row="jos_emundus_setup_programmes___certificate"]');
            certContainer.children().hide();
            certContainer.append('' +
                '<div class="row-fluid">\n' +
                    '<div class="fabrikgrid_checkbox span12">' +
                        '<label for="certCheckbox" class="checkbox">\n' +
                        '\t<input type="checkbox" class="fabrikinput  fabrik_filter" name="certCheckbox" id="certCheckbox"  onchange="toggleCert(this);"><span>certifiante ou diplômante</span></label>\n' +
                    '</div>\n' +
                '</div>');

            /* Precheck the checkbox in case data is already selected in the session. */
            if (jQuery('[data-filter-row="jos_emundus_setup_programmes___certificate"] input:checkbox:checked').not('#certCheckbox').length > 0)
                jQuery('#certCheckbox').prop('checked', true);

        }

        function toggleCPF(checkbox) {
            jQuery('[data-filter-row="jos_emundus_setup_programmes___numcpf"] input:checkbox').not(checkbox).prop('checked', checkbox.checked);
        }
        function toggleCert(checkbox) {
            jQuery('[data-filter-row="jos_emundus_setup_programmes___certificate"] input:checkbox').not(checkbox).prop('checked', checkbox.checked);
        }

        function fixDateRangeFilters() {

            var dateStart = jQuery('input[id^="jos_emundus_setup_teaching_unity___date_start_306_com_fabrik_306_filter_range_1_"]');
            dateStart.parent().parent().hide();
            dateStart.val("3030-01-01");

            var dateEnd = jQuery('input[id^="jos_emundus_setup_teaching_unity___date_end_306_com_fabrik_306_filter_range_0_"]');
            dateEnd.parent().parent().hide();
            dateEnd.val("1970-01-02");

        }
    </script>
</div>
