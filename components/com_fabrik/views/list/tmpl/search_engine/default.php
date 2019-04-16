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
    <div style="text-align:right"><?php echo FText::_('COM_FABRIK_LIST') ?>: <?php echo $this->tablePicker; ?></div>
<?php endif;

if ($this->params->get('show_page_heading')) :?>
    <h1><?php echo $this->params->get('page_heading');?> </h1>
<?php endif; ?>

<?php if ($this->showTitle == 1) : ?>
    <div class="page-header">
        <?php if (!JFactory::getUser()->guest) :?><h1>Vous êtes un <?php echo $user->profile_label;?></h1> <?php endif; ?>
        <div class="em-page-header-description">
            <p style="padding: 1rem; background-color: #e9e9e9; text-align: justify;"><span style="font-size: 14pt;"><span style="text-decoration: underline;">Sur cette page, vous pouvez consulter les offres déjà en ligne</span>. Vous pouvez préciser votre demande par type d'acteur recherché, par région, département et thématique souhaités grâce aux filtres ci-dessous. Cliquez sur l'intitulé de l'annonce qui vous intéresse pour la découvrir en détail et pouvoir contacter son auteur (dit aussi "déposant").<br /><br />Vous n'avez pas trouvez ce que vous cherchiez ? Déposez l'annonce qui vous correspond en <a href="https://hesam.emundus.fr/index.php?option=com_fabrik&amp;view=form&amp;formid=102">proposant une offre</a>. </span><span style="font-size: 14pt;"></span><br /><br /><span style="font-size: 14pt;"><span style="text-decoration: underline;">Vous souhaitez en savoir plus avant de vous lancer</span> ? Découvrez les récits d'expérience et astuces d'une <a href="https://hesam.emundus.fr/index.php?option=com_content&amp;view=article&amp;id=122:francoise-ramel-un-chercheur-nous-permet-de-formuler-des-desirs-des-besoins-mais-aussi-des-solutions&amp;catid=101">conseillère municipale de Pontivy</a> ou d'un <a href="https://hesam.emundus.fr/1000-doctorants/les-temoignages/58-boris-chevrot">doctorant d'une communauté de communes de Bourgogne</a> dans la rubrique <a href="https://hesam.emundus.fr/1000-doctorants/les-temoignages">Témoignages</a> de cette plateforme. Dans la rubrique <a href="https://hesam.emundus.fr/1000-doctorants/boite-a-outils">Boîte à outils</a></span><span style="font-size: 14pt;">, des articles sont régulièrement déposés pour rendre toujours plus clair et plus simple le programme. A la moindre question, consultez la <a href="https://hesam.emundus.fr/vos-questions">Foire aux questions</a> ou écrivez-nous à</span><span style="font-size: 14pt;"> <a href="mailto:1000docs@hesam.eu">1000docs@hesam.eu</a> </span><strong><span style="font-size: 14pt;"><br /></span></strong></p>
        </div>
    </div>
<?php endif;

////// PAGE FUNCTIONS

// GET ALL REGIONS
function getAllRegions() {
    $db = JFactory::getDBO();
    $query = $db->getquery('true');

    $query
        ->select("*")
        ->from($db->qn('data_regions'));

    $db->setQuery($query);
    return $db->loadObjectList();
}

// GET ALL DEPATMENTS
function getAllDepartments() {
    $db = JFactory::getDBO();
    $query = $db->getquery('true');

    $query
        ->select("*")
        ->from($db->qn('data_departements'));

    $db->setQuery($query);
    return $db->loadObjectList();
}

// GET SELECTED REGIONS
function getSelectedRegions($headRegions) {
    if(!empty($headRegions)) {
        $db = JFactory::getDBO();
        $query = $db->getquery('true');
        $query2 = $db->getquery('true');


        $query2
            ->select("cc2.id")
            ->from($db->qn('#__emundus_campaign_candidature', 'cc2'))
            ->join('INNER', $db->qn('#__emundus_recherche', 'er2') . ' ON ' . $db->qn('er2.fnum') . ' = ' . $db->qn('cc2.fnum'))
            ->join('INNER', $db->qn('#__emundus_recherche_744_repeat', 'err744') . ' ON ' . $db->qn('err744.parent_id') . ' = ' . $db->qn('er2.id'))
            ->where($db->qn('err744.region') . ' IN (' . $headRegions . ')');

        $query
            ->select("cc1.id")
            ->from($db->qn('#__emundus_campaign_candidature', 'cc1'))
            ->join('INNER', $db->qn('#__emundus_recherche', 'er1') . ' ON ' . $db->qn('er1.fnum') . ' = ' . $db->qn('cc1.fnum'))
            ->join('INNER', $db->qn('#__emundus_recherche_630_repeat', 'err630') . ' ON ' . $db->qn('err630.parent_id') . ' = ' . $db->qn('er1.id'))
            ->where($db->qn('err630.region') . ' IN (' . $headRegions . ')')
            ->union($query2);

        $db->setQuery($query);

        return $db->loadColumn();
    }
    else {
        return array();
    }
}


// GET SELECTED REGIONS
function getSelectedDepartments($headDepartments) {
    if(!empty($headDepartments)) {
        $db = JFactory::getDBO();
        $query = $db->getquery('true');
        $query2 = $db->getquery('true');


        $query2
            ->select("cc2.id")
            ->from($db->qn('#__emundus_campaign_candidature', 'cc2'))
            ->join('INNER', $db->qn('#__emundus_recherche', 'er2') . ' ON ' . $db->qn('er2.fnum') . ' = ' . $db->qn('cc2.fnum'))
            ->join('INNER', $db->qn('#__emundus_recherche_744_repeat', 'err744') . ' ON ' . $db->qn('err744.parent_id') . ' = ' . $db->qn('er2.id'))
            ->join('INNER', $db->qn('#__emundus_recherche_744_repeat_repeat_department', 'errd744') . ' ON ' . $db->qn('errd744.parent_id') . ' = ' . $db->qn('err744.id'))
            ->where($db->qn('errd744.department') . ' IN (' . $headDepartments . ')');

        $query
            ->select("cc1.id")
            ->from($db->qn('#__emundus_campaign_candidature', 'cc1'))
            ->join('INNER', $db->qn('#__emundus_recherche', 'er1') . ' ON ' . $db->qn('er1.fnum') . ' = ' . $db->qn('cc1.fnum'))
            ->join('INNER', $db->qn('#__emundus_recherche_630_repeat', 'err630') . ' ON ' . $db->qn('err630.parent_id') . ' = ' . $db->qn('er1.id'))
            ->join('INNER', $db->qn('#__emundus_recherche_630_repeat_repeat_department', 'errd630') . ' ON ' . $db->qn('errd630.parent_id') . ' = ' . $db->qn('er1.id'))
            ->where($db->qn('errd630.department') . ' IN (' . $headDepartments . ')')
            ->union($query2);

        $db->setQuery($query);
        return $db->loadColumn();
    }
    else {
        return array();
    }
}



// GETS DEPARTMENTS FOR ACTEUR PUBLIQUE
function getActeurDepartments($fnum) {
    $db = JFactory::getDBO();


    $query = $db->getquery('true');

    $query
        ->select($db->quoteName('dd.departement_nom'))
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

    $query
        ->select($db->quoteName('dd.departement_nom'))
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

                $getSelectedRegions = getSelectedRegions($headRegions);
                $getSelectedDepartments = getSelectedDepartments($headDepartments);

                $data = array();
                $i = 0;
                if (!empty($this->rows[0])) {
                    foreach ($this->rows[0] as $k => $v) {
                        if ((in_array($v->data->jos_emundus_campaign_candidature___id_raw, $getSelectedRegions) && $getSelectedRegions) || (in_array($v->data->jos_emundus_campaign_candidature___id_raw, $getSelectedDepartments) && $getSelectedDepartments) || (!$getSelectedDepartments && !$getSelectedRegions)) {
                                foreach ($this->headings as $key => $val) {
                                    $raw = $key.'_raw';
                                    if (array_key_exists($key, $v->data)) {
                                        if (strcasecmp($v->data->$key, "1") == 0)
                                            $data[$i][$val] = $v->data->$key;
                                        else {
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
                                $i = $i + 1;
                        }
                        else {
                            unset($this->rows[0][$k]);
                        }
                        $v->total = $i;
                    }
                    $this->navigation->total = sizeof($data);

                }
                ?>

                <div class="em-search-engine-filters">
                    <?php if ($this->showFilters && $this->bootShowFilters)
                        echo $this->layoutFilters();
                    ?>
                </div>

                <div class="em-search-engine-data">

                    <table>
                        <?php if (!empty($data)) :?>
                            <thead>
                            <tr>
                                <td><h3>RÉSULTATS DE LA RECHERCHE</h3></td>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr class="fabrik___heading">
                                <td colspan="<?php echo count($this->headings);?>">
                                    <?php echo $this->nav;?>
                                </td>
                            </tr>
                            </tfoot>
                        <?php endif; ?>

                        <tbody>

						<?php

						$gCounter = 0;
						foreach ($data as $d) {

							$cherches = [];
							if ($d['jos_emundus_recherche___futur_doctorant_yesno'] == 'oui')
								$cherches[] = $this->headings['jos_emundus_recherche___futur_doctorant_yesno'];
							if ($d['jos_emundus_recherche___acteur_public_yesno'] == 'oui')
								$cherches[] = $this->headings['jos_emundus_recherche___acteur_public_yesno'];
							if ($d['jos_emundus_recherche___equipe_de_recherche_direction_yesno'] == 'oui')
								$cherches[] = $this->headings['jos_emundus_recherche___equipe_de_recherche_direction_yesno'];
							if ($d['jos_emundus_recherche___equipe_de_recherche_codirection_yesno'] == 'oui')
								$cherches[] = $this->headings['jos_emundus_recherche___equipe_de_recherche_codirection_yesno'];

							$themes = jsonDecode($d['data_thematics___thematic_raw']);
							if (is_array($themes)) {
								if (sizeof($themes) > 4) {
									$themes = implode('</div> - <div class="em-highlight">', array_slice($themes, 0, 4)).' ... ';
								} else {
									$themes = implode('</div> - <div class="em-highlight">', $themes);
								}
							}
                            if($d["jos_emundus_recherche___all_regions_depatments_raw"] == "non") {
                                if ($d["jos_emundus_setup_profiles___id_raw"] != "1008") {
                                    $departments = getOtherDepartments($d["jos_emundus_recherche___fnum_raw"]);
                                    if ($departments) {
                                        if (sizeof($departments) > 8) {
                                            $departments = implode('</div> - <div class="em-highlight">', array_slice($departments, 0, 8)).' ... ';
                                        } else {
                                            $departments = implode('</div> - <div class="em-highlight">', $departments);
                                        }
                                    }
                                }

                                else {
                                    $departments =  array_unique(array_column(getActeurDepartments($d["jos_emundus_recherche___fnum_raw"]), 'departement_nom'));
                                    if (sizeof($departments) > 8) {
                                        $departments = implode('</div> - <div class="em-highlight">', array_slice($departments, 0, 8)) . ' ... ';
                                    }
                                    else {
                                        $departments = implode('</div> - <div class="em-highlight">', $departments);
                                    }
                                }
                            }

                                if ((isset($d['Status']) && $d['Status'] == 2) || (isset($d['jos_emundus_campaign_candidature___status']) && $d['jos_emundus_campaign_candidature___status'] == 2)) {
                                    $status = 2;
                                } else {
                                    $status = 1;
                                }



                            ?>
                            <tr>
                                <td>
                                    <div class="em-search-engine-div-data <?php echo ($status === 2)?'em-closed-offer':''; ?>">
                                        <div class="em-search-engine-result-title"><?php echo $d['jos_emundus_projet___titre']; ?></div>
                                        <div class="em-search-engine-deposant">
                                            <i class="fa fa-user"></i> <strong>Déposant : </strong> <?php echo strtolower($d['jos_emundus_setup_profiles___label']); ?>
                                        </div>
                                        <?php if (!empty($cherches)) :?>
                                            <div class="em-search-engine-addressed">
                                                <i class="fa fa-users"></i> <strong>Projet adressé à : &nbsp;</strong><?php echo strtolower(implode( '&#32;-&#32;', $cherches)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="em-search-engine-thematics">
                                            <strong>Thématique(s)</strong> : <div class="em-highlight"><?php echo $themes?$themes:'Aucune thématique'; ?></div>
                                        </div>
                                        <div class="em-search-engine-departments">
                                            <strong>Département(s)</strong> :
                                            <div class="em-highlight">
                                                <?php
                                                    if($d["jos_emundus_recherche___all_regions_depatments_raw"] == "oui") {
                                                        echo JText::_('COM_EMUNDUS_FABRIK_ALL_DEPARTMANTS');
                                                    }
                                                    else {
                                                        echo $departments ? $departments : 'Aucun département';
                                                    }
                                                ?>
                                            </div>
                                        </div>
                                        <?php if (JFactory::getUser()->guest) :?>
                                            <div class="em-search-engine-learn-more"><a href="<?php echo 'index.php?option=com_users&view=login&return='.base64_encode(JFactory::getURI())?>"> Connectez-vous pour en savoir plus </a></div>
                                        <?php else :?>
                                            <div class='em-search-engine-details <?php echo ($status === 2)?'em-closed-offer-btn':'em-open-offer-btn'; ?>'><a href="<?php echo $d['fabrik_view_url']; ?>"><?php echo ($status === 2)?'Offre clôturée':'Consultez l\'offre'; ?></a></div>

                                            <?php if ($d['jos_emundus_campaign_candidature___applicant_id_raw'] == JFactory::getUser()->id && ((isset($d['Status']) && $d['Status'] == 3) || (isset($d['jos_emundus_campaign_candidature___status']) && $d['jos_emundus_campaign_candidature___status'] == 3))) :?>
                                                <div class="em-float-left em-offer-not-published"><span class="label label-darkyellow">Offre en attente de validation</span></div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php
                            unset($cherches);
                            unset($themes);
                            $gCounter++;
                        }
                        ?>

                        </tbody>

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

                <?php if($user->id != 0) : ?>
                    <a href="/?option=com_fabrik&view=form&formid=102" class="em-search-not-found-btn">
                        <span class="em-search-not-found-btn-content">Vous n'avez pas trouvé ce que vous cherchiez ? Déposez l'annonce qui vous correspond.<br> <strong>Proposez une offre</strong></span>
                        <span class="em-search-not-found-icon"><i class="fa fa-arrow-right" aria-hidden="true"></i></span>
                    </a>
                <?php endif; ?>

                <?php print_r($this->hiddenFields);?>
            </div>
        </form>
    </div>
</div>
<script>
    jQuery(document).ready(function(){
        //region and department object
        $allRegions= <?php echo json_encode(getAllRegions()); ?>;
        $allDepartments= <?php echo json_encode(getAllDepartments()); ?>;

        $regionArray = <?php echo json_encode($selectedHeadRegions); ?>;

        $departmentArray = <?php echo json_encode($selectedHeadDepartments);?>;

        //add region select
        $regionSelect = '<tr><td>Dans quelle(s) région(s)</td><td><select id="data_regions___name_0value" class="chosen chosen-region" multiple="true" style="width:400px;">';
        $pushRegions = [];
            jQuery($allRegions).each(region =>{
                $allRegions[region]["selected"] = "";
                jQuery($regionArray).each(selected => {
                    if ($allRegions[region].id == jQuery($regionArray)[selected]) {
                        $allRegions[region]["selected"] = "selected";
                        $pushRegions.push($regionArray);
                    }
                });
                $regionSelect += '<option value="'+jQuery($allRegions)[region].id+'" '+ jQuery($allRegions)[region].selected +'>'+jQuery($allRegions)[region].name+'</option>';
            });
        $regionSelect += '</select></td><input type="hidden" id="hidden-regions-input" name="regions" value="'+$regionArray+'"></tr>';

        jQuery(".filtertable tbody .fabrik_row").first().after($regionSelect);

        jQuery('#data_regions___name_0value').after('<button type="button" onclick="selectAllRegions()" class="chosen-toggle-region select">Sélectionnez toutes les régions</button>');
        jQuery(".chosen-region").chosen();

        //add department select
        $departmentSelect = '<tr><td>Dans quel(s) département(s)</td><td><select id="data_departements___departement_nomvalue" class="chosen chosen-department" multiple="true" style="width:400px;">';
        $pushDepartments = [];
        jQuery($allDepartments).each(department =>{
            $allDepartments[department]["selected"] = "";
                jQuery($departmentArray).each(selected => {
                    if ($allDepartments[department].departement_id == jQuery($departmentArray)[selected]) {
                        $allDepartments[department]["selected"] = "selected";
                        $pushDepartments.push($departmentArray);
                    }
                });
                $departmentSelect += '<option value="'+jQuery($allDepartments)[department].departement_id+'"'+ jQuery($allDepartments)[department].selected +'>'+jQuery($allDepartments)[department].departement_nom+'</option>';
            });
        $departmentSelect += '</select></td><input type="hidden" id="hidden-department-input" name="departments" value="'+$departmentArray+'"></tr>';
        jQuery(".filtertable tbody .fabrik_row").first().after($departmentSelect);
        
        jQuery('#data_departements___departement_nomvalue').after('<button type="button" onclick="selectAllDepartments()" class="chosen-toggle-department select">Sélectionnez tous les départements</button>');
        jQuery(".chosen-department").chosen();

        // chosen change regions
        jQuery("#data_regions___name_0value").chosen().change(function(event){
            if (event.target == this){
                $regionArray = jQuery(this).val();
                if (jQuery(this).val())
                    jQuery("#hidden-regions-input").val($regionArray);
                if ($regionArray == null)
                    jQuery("#hidden-regions-input").val("");
            }
        });

        // chosen change departments
        jQuery("#data_departements___departement_nomvalue").chosen().change(function(event){
            if (event.target == this){
                $departmentArray = (jQuery(this).val());
                if (jQuery(this).val())
                    jQuery("#hidden-department-input").val($departmentArray);
                if ($departmentArray == null)
                    jQuery("#hidden-department-input").val("");
            }
        });

        jQuery('select.fabrik_filter[multiple]').chosen({
            placeholder_text_single: "<?php echo JText::_('CHOSEN_SELECT_ONE'); ?>",
            placeholder_text_multiple: "<?php echo JText::_('CHOSEN_SELECT_MANY'); ?>",
            no_results_text: "<?php echo JText::_('CHOSEN_NO_RESULTS'); ?>"
        })
    });


    function selectAllRegions() {
        if(jQuery('.chosen-toggle-region').hasClass('select')) {
            $regionArray = [];
            jQuery('#data_regions___name_0value option').each(function() {
                $regionArray.push(jQuery(this).val());
            });
            jQuery('#data_regions___name_0value option').prop('selected', jQuery('.chosen-toggle-region').hasClass('select')).parent().trigger('chosen:updated');

            jQuery('#hidden-regions-input').val($regionArray);
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
        if(jQuery('.chosen-toggle-department').hasClass('select')) {
            $departmentArray = [];
            jQuery('#data_departements___departement_nomvalue option').each(function() {
                $departmentArray.push(jQuery(this).val());
            });
            jQuery('#hidden-department-input').val($departmentArray);
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
