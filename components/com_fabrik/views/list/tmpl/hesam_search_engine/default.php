<?php
/**
 * Fabrik List Template: Div
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */


// No direct access
defined('_JEXEC') or die('Restricted access');

$doc = JFactory::getDocument();
$doc->addStyleSheet('media/com_emundus/lib/bootstrap-232/css/bootstrap.min.css');
$doc->addScript('media/com_emundus/lib/chosen/chosen.jquery.js');
$doc->addStyleSheet('media/com_emundus/lib/chosen/chosen.css');


function jsonDecode($val) {
    return (!empty(json_decode($val)))?json_decode($val):$val;
}

$lang = JFactory::getLanguage();
$extension = 'com_emundus';
$base_dir = JPATH_SITE . '/components/com_emundus';
$language_tag = "fr-FR";
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);

// The number of columns to split the list rows into
$pageClass = $this->params->get('pageclass_sfx', '');

$user = JFactory::getSession()->get('emundusUser');

$jinput = JFactory::getApplication()->input;
// GET REGIONS FROM HEADER
$headRegions = $jinput->post->getVal('regions');
$selectedHeadRegions = explode(',', $headRegions);

// GET DEPARTMENTS FROM HEADER
$headDepartments = $jinput->post->getVal('departments');
$selectedHeadDepartments = explode(",", $headDepartments);

if ($pageClass !== '') :
    echo '<div class="' . $pageClass . '">';
endif;

if ($this->tablePicker != '') : ?>
    <div style="text-align:right"><?= FText::_('COM_FABRIK_LIST') ?>: <?= $this->tablePicker; ?></div>
<?php endif;

if ($this->params->get('show_page_heading')) :?>
    <h1><?= $this->params->get('page_heading');?> </h1>
<?php endif;

////// PAGE FUNCTIONS

// GET ALL REGIONS
function getAllRegions() {
    $db = JFactory::getDBO();
    $query = $db->getquery('true');

    $query->select("*")
        ->from($db->qn('data_regions'));

    $db->setQuery($query);
    return $db->loadObjectList();
}

// GET ALL DEPATMENTS
function getAllDepartments() {
    $db = JFactory::getDBO();
    $query = $db->getquery('true');

    $query->select("*")
        ->from($db->qn('data_departements'));

    $db->setQuery($query);
    return $db->loadObjectList();
}

// GET SELECTED REGIONS
function getSelectedRegions($headRegions) {
    if (!empty($headRegions)) {
        $db = JFactory::getDBO();
        $query = $db->getquery('true');
        $query2 = $db->getquery('true');


        $query2->select("cc2.id")
            ->from($db->qn('#__emundus_campaign_candidature', 'cc2'))
            ->join('INNER', $db->qn('#__emundus_recherche', 'er2') . ' ON ' . $db->qn('er2.fnum') . ' = ' . $db->qn('cc2.fnum'))
            ->join('INNER', $db->qn('#__emundus_recherche_744_repeat', 'err744') . ' ON ' . $db->qn('err744.parent_id') . ' = ' . $db->qn('er2.id'))
            ->where($db->qn('err744.region') . ' IN (' . $headRegions . ')');

        $query->select("cc1.id")
            ->from($db->qn('#__emundus_campaign_candidature', 'cc1'))
            ->join('INNER', $db->qn('#__emundus_recherche', 'er1') . ' ON ' . $db->qn('er1.fnum') . ' = ' . $db->qn('cc1.fnum'))
            ->join('INNER', $db->qn('#__emundus_recherche_630_repeat', 'err630') . ' ON ' . $db->qn('err630.parent_id') . ' = ' . $db->qn('er1.id'))
            ->where($db->qn('err630.region') . ' IN (' . $headRegions . ')')
            ->union($query2);

        $db->setQuery($query);

        return $db->loadColumn();
    } else {
        return array();
    }
}


// GET SELECTED REGIONS
function getSelectedDepartments($headDepartments) {
    if (!empty($headDepartments)) {
        $db = JFactory::getDBO();
        $query = $db->getquery('true');
        $query2 = $db->getquery('true');

        $query2->select("cc2.id")
            ->from($db->qn('#__emundus_campaign_candidature', 'cc2'))
            ->join('INNER', $db->qn('#__emundus_recherche', 'er2') . ' ON ' . $db->qn('er2.fnum') . ' = ' . $db->qn('cc2.fnum'))
            ->join('INNER', $db->qn('#__emundus_recherche_744_repeat', 'err744') . ' ON ' . $db->qn('err744.parent_id') . ' = ' . $db->qn('er2.id'))
            ->join('INNER', $db->qn('#__emundus_recherche_744_repeat_repeat_department', 'errd744') . ' ON ' . $db->qn('errd744.parent_id') . ' = ' . $db->qn('err744.id'))
            ->where($db->qn('errd744.department') . ' IN (' . $headDepartments . ')');

        $query->select("cc1.id")
            ->from($db->qn('#__emundus_campaign_candidature', 'cc1'))
            ->join('INNER', $db->qn('#__emundus_recherche', 'er1') . ' ON ' . $db->qn('er1.fnum') . ' = ' . $db->qn('cc1.fnum'))
            ->join('INNER', $db->qn('#__emundus_recherche_630_repeat', 'err630') . ' ON ' . $db->qn('err630.parent_id') . ' = ' . $db->qn('er1.id'))
            ->join('INNER', $db->qn('#__emundus_recherche_630_repeat_repeat_department', 'errd630') . ' ON ' . $db->qn('errd630.parent_id') . ' = ' . $db->qn('er1.id'))
            ->where($db->qn('errd630.department') . ' IN (' . $headDepartments . ')')
            ->union($query2);

        $db->setQuery($query);
        return $db->loadColumn();
    } else {
        return array();
    }
}



// GETS DEPARTMENTS FOR ACTEUR PUBLIQUE
function getActeurDepartments($fnum) {
    $db = JFactory::getDBO();

    $query = $db->getquery('true');

    $query->select($db->quoteName('dd.departement_nom'))
        ->from($db->quoteName('#__emundus_recherche', 'u'))
        ->leftJoin($db->quoteName('#__emundus_recherche_744_repeat', 'ur'). ' ON '.$db->quoteName('ur.parent_id') . ' = ' . $db->quoteName('u.id'))
        ->leftJoin($db->quoteName('#__emundus_recherche_744_repeat_repeat_department', 'urd'). ' ON '.$db->quoteName('urd.parent_id') . ' = ' . $db->quoteName('ur.id'))
        ->leftJoin($db->quoteName('data_departements', 'dd'). ' ON '.$db->quoteName('dd.departement_id') . ' = ' . $db->quoteName('urd.department'))
        ->where($db->quoteName('u.fnum') . ' LIKE "' . $fnum . '"');

    $db->setQuery($query);
    try {

        return $db->loadObjectList();

    } catch (Exception $e) {
        echo "<pre>";
        var_dump($query->__toString());
        echo "</pre>";
        die();
    }
}


// GETS DEPARTMENTS FOR Futur Doc and Chercheurs
function getOtherDepartments($fnum) {
    $db = JFactory::getDBO();

    $query = $db->getquery('true');

    $query->select($db->quoteName('dd.departement_nom'))
        ->from($db->quoteName('#__emundus_recherche', 'u'))
        ->leftJoin($db->quoteName('#__emundus_recherche_630_repeat', 'ur'). ' ON '.$db->quoteName('ur.parent_id') . ' = ' . $db->quoteName('u.id'))
        ->leftJoin($db->quoteName('#__emundus_recherche_630_repeat_repeat_department', 'urd'). ' ON '.$db->quoteName('urd.parent_id') . ' = ' . $db->quoteName('ur.id'))
        ->leftJoin($db->quoteName('data_departements', 'dd'). ' ON '.$db->quoteName('dd.departement_id') . ' = ' . $db->quoteName('urd.department'))
        ->where($db->quoteName('u.fnum') . ' LIKE "' . $fnum . '"');

    $db->setQuery($query);
    try {

        return $db->loadColumn();

    } catch (Exception $e) {
        echo "<pre>";
        var_dump($query->__toString());
        echo "</pre>";
        die();
    }
}

// Intro outside of form to allow for other lists/forms to be injected.
echo $this->table->intro;

?>

<div class="content">
    <div class="w-container">

	    <?php if ($this->showTitle == 1) : ?>
            <h1 class="heading-2"><?= $this->table->label;?></h1>
        <?php endif; ?>

        <form class="fabrikForm form-search" action="<?= $this->table->action;?>" method="post" id="<?= $this->formid;?>" name="fabrikList">

            <?php
            if ($this->hasButtons) {
                echo $this->loadTemplate('buttons');
            }
            ?>

            <div class="fabrikDataContainer">

                <?php foreach ($this->pluginBeforeList as $c) {
                    echo $c;
                }

                $getSelectedRegions = getSelectedRegions($headRegions);
                $getSelectedDepartments = getSelectedDepartments($headDepartments);

                $data = array();
                $i = 0;
                if (!empty($this->rows[0])) {
                    foreach ($this->rows[0] as $k => $v) {
                        if (($getSelectedRegions && in_array($v->data->jos_emundus_campaign_candidature___id_raw, $getSelectedRegions)) || ($getSelectedDepartments && in_array($v->data->jos_emundus_campaign_candidature___id_raw, $getSelectedDepartments)) || (!$getSelectedDepartments && !$getSelectedRegions)) {
                            foreach ($this->headings as $key => $val) {
                                $raw = $key.'_raw';
                                if (array_key_exists($key, $v->data)) {
                                    if (strcasecmp($v->data->$key, "1") == 0) {
                                        $data[$i][$val] = $v->data->$key;
                                    } else {
                                        $data[$i][$key] = $v->data->$key;
                                        if (array_key_exists($raw, $v->data)) {
                                            $data[$i][$raw] = $v->data->$raw;
                                        }
                                    }
                                }
                            }
                            if (array_key_exists('fabrik_view_url', $v->data)) {
                                $data[$i]['fabrik_view_url'] = $v->data->fabrik_view_url;
                            }
                            $i++;
                        } else {
                            unset($this->rows[0][$k]);
                        }
                        $v->total = $i;
                    }
                }
                ?>

                <div class="hesam-search-filters">
                    <a href="<?= JUri::current() ?>?resetfilters=1" class="em-reset-filter">
                        <i data-isicon="true" class="icon-refresh"></i> Effacer les filtres
                    </a>
                    <?php if ($this->showFilters && $this->bootShowFilters) {
                        echo $this->layoutFilters();
                    } ?>
                </div>

                <div class="profil-container">
                    <div class="column-card-container w-row">
                        <?php if (!empty($data)) :?>
                            <tfoot>
                                <tr class="fabrik___heading">
                                    <td colspan="<?= count($this->headings);?>">
                                        <?= $this->nav; ?>
                                    </td>
                                </tr>
                            </tfoot>
                        <?php endif; ?>

						<?php
						$gCounter = 0;
						foreach ($data as $d) {

							$cherches = [];
							if ($d['jos_emundus_recherche___futur_doctorant_yesno'] == 'oui') {
							    $cherches[] = $this->headings['jos_emundus_recherche___futur_doctorant_yesno'];
							}
							if ($d['jos_emundus_recherche___acteur_public_yesno'] == 'oui') {
							    $cherches[] = $this->headings['jos_emundus_recherche___acteur_public_yesno'];
							}
							if ($d['jos_emundus_recherche___equipe_de_recherche_direction_yesno'] == 'oui') {
							    $cherches[] = $this->headings['jos_emundus_recherche___equipe_de_recherche_direction_yesno'];
							}
							if ($d['jos_emundus_recherche___equipe_de_recherche_codirection_yesno'] == 'oui') {
							    $cherches[] = $this->headings['jos_emundus_recherche___equipe_de_recherche_codirection_yesno'];
							}

							$themes = jsonDecode($d['data_thematics___thematic_raw']);
							if (is_array($themes)) {
								if (sizeof($themes) > 4) {
									$themes = implode(' - ', array_slice($themes, 0, 4)).' ... ';
								} else {
									$themes = implode(' - ', $themes);
								}
							}

                            if ($d["jos_emundus_recherche___all_regions_depatments_raw"] == "non") {
                                if ($d["jos_emundus_setup_profiles___id_raw"] != "1008") {
                                    $departments = getOtherDepartments($d["jos_emundus_recherche___fnum_raw"]);
                                    if ($departments) {
                                        if (sizeof($departments) > 8) {
                                            $departments = implode(' - ', array_slice($departments, 0, 8)).' ... ';
                                        } else {
                                            $departments = implode(' - ', $departments);
                                        }
                                    }
                                } else {
                                    $departments =  array_unique(array_column(getActeurDepartments($d["jos_emundus_recherche___fnum_raw"]), 'departement_nom'));
                                    if (sizeof($departments) > 8) {
                                        $departments = implode(' - ', array_slice($departments, 0, 8)) . ' ... ';
                                    } else {
                                        $departments = implode(' - ', $departments);
                                    }
                                }
                            }

                            if ((isset($d['Status']) && $d['Status'] == 2) || (isset($d['jos_emundus_campaign_candidature___status']) && $d['jos_emundus_campaign_candidature___status'] == 2)) {
                                $status = 2;
                            } else {
                                $status = 1;
                            }
                            ?>

                            <div class="w-col w-col-6">
                                <div>
                                    <div class="card-offre <?= ($status === 2)?'em-closed-offer':''; ?>">
                                        <div class="text-block-2"><?= $d['jos_emundus_projet___titre']; ?></div>
                                        <div class="div-block margin">
                                            <i class="fa fa-user"></i>
                                            <div class="name"><?= ucfirst(strtolower($d['jos_emundus_setup_profiles___label'])); ?></div>
                                        </div>
                                        <?php if (!empty($cherches)) :?>
                                            <div class="div-block">
                                                <i class="fa fa-bullseye"></i>
                                                <div class="name"><?= implode('</div>&nbsp-&nbsp;<div class="name">', $cherches); ?></div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="div-block-copy">
                                            <div class="text-block-2-copy">Thématiques</div>
                                            <div class="name"><?= $themes?$themes:'Aucune thématique'; ?></div>
                                        </div>
                                        <div class="div-block-copy">
                                            <div class="text-block-2-copy">Départements</div>
                                            <div class="name">
                                                <?php
                                                    if ($d["jos_emundus_recherche___all_regions_depatments_raw"] == "oui") {
                                                        echo JText::_('COM_EMUNDUS_FABRIK_ALL_DEPARTMANTS');
                                                    } else {
                                                        echo $departments ? $departments : 'Aucun département';
                                                    }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
	                                <?php if (JFactory::getUser()->guest) :?>
                                        <a href="<?= 'index.php?option=com_users&view=login&return='.base64_encode(JFactory::getURI()); ?>" class="cta-offre w-inline-block"><div class="text-block-2"> Connectez-vous pour en savoir plus </div></a>
	                                <?php else :?>
                                        <a href="<?= $d['fabrik_view_url']; ?>" class="cta-offre w-inline-block"><div class='text-block-2 <?= ($status === 2 || $status === 5)?'em-closed-offer-btn':'em-open-offer-btn'; ?>'><?= ($status === 2 || $status === 5)?'Offre clôturée':'Consultez l\'offre'; ?></div></a>

                                        <!--
		                                <?php if ($d['jos_emundus_campaign_candidature___applicant_id_raw'] == JFactory::getUser()->id && ((isset($d['Status']) && $d['Status'] == 3) || (isset($d['jos_emundus_campaign_candidature___status']) && $d['jos_emundus_campaign_candidature___status'] == 3))) :?>
                                            <div class="cta-offre w-inline-block">
                                                <div class="text-block-2">Offre en attente de validation</div>
                                            </div>
		                                <?php endif; ?>
		                                -->
	                                <?php endif; ?>
                                </div>
                            </div>
                            <?php
                            unset($cherches);
                            unset($themes);
                            $gCounter++;
                        }
                        ?>

                        <?php if ($this->hasCalculations) : ?>
                            <tfoot>
                            <tr class="fabrik_calculations">

                                <?php foreach ($this->headings as $key => $heading) :
                                    $h = $this->headingClass[$key];
                                    $style = empty($h['style']) ? '' : 'style="' . $h['style'] . '"'; ?>
                                    <td class="<?= $h['class']?>" <?= $style?>>
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
                    </div>
                </div>
            </div>
	        <?php print_r($this->hiddenFields);?>
        </form>
        <?php if ($user->id != 0) :?>
            <a href="/?option=com_fabrik&view=form&formid=102" class="em-search-not-found-btn">
                <span class="em-search-not-found-btn-content">Vous n'avez pas trouvé ce que vous cherchiez ? Déposez l'annonce qui vous correspond.<br> <strong>Proposez une offre</strong></span>
                <span class="em-search-not-found-icon"><i class="fa fa-arrow-right" aria-hidden="true"></i></span>
            </a>
        <?php endif; ?>
    </div>
</div>


<script>
    let data = <?= sizeof($data); ?>;

    jQuery(document).ready(function(){
        //region and department object
        let allRegions = <?= json_encode(getAllRegions()); ?>;
        let allDepartments= <?= json_encode(getAllDepartments()); ?>;

        let regionArray = <?= json_encode($selectedHeadRegions); ?>;

        let departmentArray = <?= json_encode($selectedHeadDepartments); ?>;


        // Build region select
        let regionSelect = '<tr><td>Dans quelle(s) région(s)</td><td><select id="data_regions___name_0value" class="chosen chosen-region" multiple="true" style="width:400px;">';
        let pushRegions = [];
        jQuery(allRegions).each(region =>{
            allRegions[region]["selected"] = "";
            jQuery(regionArray).each(selected => {
                if (allRegions[region].id == jQuery(regionArray)[selected]) {
                    allRegions[region]["selected"] = "selected";
                    pushRegions.push(regionArray);
                }
            });
            regionSelect += '<option value="'+jQuery(allRegions)[region].id+'" '+ jQuery(allRegions)[region].selected +'>'+jQuery(allRegions)[region].name+'</option>';
        });
        regionSelect += '</select></td><input type="hidden" id="hidden-regions-input" name="regions" value="'+regionArray+'"></tr>';


        // Build department select
        let departmentSelect = '<tr><td>Dans quel(s) département(s)</td><td><select id="data_departements___departement_nomvalue" class="chosen chosen-department" multiple="true" style="width:400px;">';
        let pushDepartments = [];
        jQuery(allDepartments).each(department => {
            allDepartments[department]["selected"] = "";
            jQuery(departmentArray).each(selected => {
                if (allDepartments[department].departement_id == jQuery(departmentArray)[selected]) {
                    allDepartments[department]["selected"] = "selected";
                    pushDepartments.push(departmentArray);
                }
            });
            departmentSelect += '<option value="'+jQuery(allDepartments)[department].departement_id+'"'+ jQuery(allDepartments)[department].selected +'>'+jQuery(allDepartments)[department].departement_nom+'</option>';
        });
        departmentSelect += '</select></td><input type="hidden" id="hidden-department-input" name="departments" value="'+departmentArray+'"></tr>';


        // Place filters on page.
        jQuery(".filtertable tbody .fabrik_row").first().after(departmentSelect);
        jQuery(".filtertable tbody .fabrik_row").first().after(regionSelect);

        jQuery('#data_departements___departement_nomvalue').after('<button type="button" onclick="selectAllDepartments()" class="chosen-toggle-department select">Sélectionnez tous les départements</button>');
        jQuery(".chosen-department").chosen({
            placeholder_text_single: "<?= JText::_('CHOSEN_SELECT_ONE'); ?>",
            placeholder_text_multiple: "<?= JText::_('CHOSEN_SELECT_MANY'); ?>",
            no_results_text: "<?= JText::_('CHOSEN_NO_RESULTS'); ?>"
        });
        jQuery('#data_regions___name_0value').after('<button type="button" onclick="selectAllRegions()" class="chosen-toggle-region select">Sélectionnez toutes les régions</button>');
        jQuery(".chosen-region").chosen({
            placeholder_text_single: "<?= JText::_('CHOSEN_SELECT_ONE'); ?>",
            placeholder_text_multiple: "<?= JText::_('CHOSEN_SELECT_MANY'); ?>",
            no_results_text: "<?= JText::_('CHOSEN_NO_RESULTS'); ?>"
        });

        
        // chosen change regions
        jQuery("#data_regions___name_0value").chosen().change(function(event){
            if (event.target == this){
                regionArray = jQuery(this).val();
                if (jQuery(this).val()) {
                    jQuery("#hidden-regions-input").val(regionArray);
                }
                if (regionArray == null) {
                    jQuery("#hidden-regions-input").val("");
                }
            }
        });

        // chosen change departments
        jQuery("#data_departements___departement_nomvalue").chosen().change(function(event){
            if (event.target == this) {
                departmentArray = (jQuery(this).val());
                if (jQuery(this).val()) {
                    jQuery("#hidden-department-input").val(departmentArray);
                }
                if (departmentArray == null) {
                    jQuery("#hidden-department-input").val("");
                }
            }
        });

        jQuery('select.fabrik_filter[multiple]').chosen({
            placeholder_text_single: "<?= JText::_('CHOSEN_SELECT_ONE'); ?>",
            placeholder_text_multiple: "<?= JText::_('CHOSEN_SELECT_MANY'); ?>",
            no_results_text: "<?= JText::_('CHOSEN_NO_RESULTS'); ?>"
        });
    });

    function selectAllRegions() {
        if (jQuery('.chosen-toggle-region').hasClass('select')) {
            let regionArray = [];
            jQuery('#data_regions___name_0value option').each(function() {
                regionArray.push(jQuery(this).val());
            });
            jQuery('#data_regions___name_0value option').prop('selected', jQuery('.chosen-toggle-region').hasClass('select')).parent().trigger('chosen:updated');

            jQuery('#hidden-regions-input').val(regionArray);
            jQuery('.chosen-toggle-region').addClass('deselect');
            jQuery('.chosen-toggle-region').removeClass('select');
            jQuery('.chosen-toggle-region').text("Désélectionnez toutes les régions");
        }
        else if(jQuery('.chosen-toggle-region').hasClass('deselect')) {
            jQuery('#hidden-regions-input').val("");
            jQuery('#data_regions___name_0value option').prop('selected', jQuery('.chosen-toggle-region').hasClass('select')).parent().trigger('chosen:updated');
            jQuery('.chosen-toggle-region').addClass('select');
            jQuery('.chosen-toggle-region').removeClass('deselect');
            jQuery('.chosen-toggle-region').text("Sélectionnez toutes les régions");
        }
    }

    function selectAllDepartments() {
        if (jQuery('.chosen-toggle-department').hasClass('select')) {
            let departmentArray = [];
            jQuery('#data_departements___departement_nomvalue option').each(function() {
                departmentArray.push(jQuery(this).val());
            });
            jQuery('#hidden-department-input').val(departmentArray);
            jQuery('#data_departements___departement_nomvalue option').prop('selected', jQuery('.chosen-toggle-department').hasClass('select')).parent().trigger('chosen:updated');
            jQuery('.chosen-toggle-department').addClass('deselect');
            jQuery('.chosen-toggle-department').removeClass('select');
            jQuery('.chosen-toggle-department').text("Désélectionnez toutes les départements");
        }
        else if(jQuery('.chosen-toggle-department').hasClass('deselect')) {
            jQuery('#hidden-department-input').val("");
            jQuery('#data_departements___departement_nomvalue option').prop('selected', jQuery('.chosen-toggle-department').hasClass('select')).parent().trigger('chosen:updated');
            jQuery('.chosen-toggle-department').addClass('select');
            jQuery('.chosen-toggle-department').removeClass('deselect');
            jQuery('.chosen-toggle-department').text("Sélectionnez toutes les départements");
        }
    }
</script>
