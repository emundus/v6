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


$lang = JFactory::getLanguage();
$extension = 'com_emundus';
$base_dir = JPATH_SITE . '/components/com_emundus';
$language_tag = "fr-FR";
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);


// If we are not logged in: we cannot access this page and so we are redirected to the login page.
$user = JFactory::getUser();

if ($user->guest) {
    JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode(JFactory::getURI())), JText::_('JGLOBAL_YOU_MUST_LOGIN_FIRST'), 'warning');
    return;
}
$user = JFactory::getSession()->get('emundusUser');

// This is currently the only way of getting the fnum.
$fnum = $this->data["jos_emundus_recherche___fnum_raw"];
$author = JFactory::getUser((int)substr($fnum, -7));

require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
$m_files = new EmundusModelFiles();
$fnumInfos = $m_files->getFnumInfos($fnum);

$form = $this->form;
$model = $this->getModel();
$groupTmpl = $model->editable ? 'group' : 'group_details';
$active = ($form->error != '') ? '' : ' fabrikHide';

if ($this->params->get('show_page_heading', 1)) :?>
    <div class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>">
        <?php echo $this->escape($this->params->get('page_heading')); ?>
    </div>
<?php endif;

$db = JFactory::getDBO();

$query = $db->getquery('true');
// Get all uploaded files
$query->select($db->quoteName(array('eup.filename', 'sa.value')))
    ->from($db->quoteName('#__emundus_uploads', 'eup'))
    ->join('LEFT', $db->quoteName('#__emundus_setup_attachments', 'sa') . ' ON (' . $db->quoteName('sa.id') . ' = ' . $db->quoteName('eup.attachment_id') . ')')
    ->where($db->quoteName('fnum') . ' LIKE "' . $this->data['jos_emundus_recherche___fnum_raw'] . '" AND eup.can_be_viewed = 1');

$db->setQuery($query);

try {
    $files = $db->loadAssocList();
    $query->clear();
} catch (Exception $e) {
    echo "<pre>";
    var_dump($query->__toString());
    echo "</pre>";
    die();
}

/*
// GET project Departments
function getDepartment($dept) {
    $db = JFactory::getDBO();

    $query = $db->getquery('true');

    $query
        ->select($db->quoteName('departement_nom'))
        ->from($db->quoteName('data_departements'))
        ->where($db->quoteName('departement_id') . ' = ' . $dept);

    $db->setQuery($query);
    try {
        return $db->loadResult();
    } catch (Exception $e) {
        echo "<pre>";
        var_dump($query->__toString());
        echo "</pre>";
        die();
    }
}

*/



echo $this->plugintop;

echo $this->loadTemplate('relateddata');

$region = "";
$department = "";
$chercheur = "";
$cherches = "";
$themes = "";
$chercheur = strtolower($this->data['jos_emundus_setup_profiles___label_raw'][0]);
$profile = $this->data['jos_emundus_setup_profiles___id_raw'][0];

require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'cifre.php');
$m_cifre = new EmundusModelCifre();


// GET Regions
function getRegions($fnum, $profile) {
    $db = JFactory::getDBO();

    $query = $db->getquery('true');

    if ($profile == '1008') {
        $query
            ->select($db->quoteName('dr.name'))
            ->from($db->quoteName('#__emundus_recherche', 'er'))
            ->leftJoin($db->quoteName('#__emundus_recherche_744_repeat', 'err'). ' ON '.$db->quoteName('err.parent_id') . ' = ' . $db->quoteName('er.id'))
            ->leftJoin($db->quoteName('data_regions', 'dr'). ' ON '.$db->quoteName('dr.id') . ' = ' . $db->quoteName('err.region'))
            ->where($db->quoteName('er.fnum') . ' LIKE "' . $fnum . '"');
    }
    else {
        $query
            ->select($db->quoteName('dr.name'))
            ->from($db->quoteName('#__emundus_recherche', 'er'))
            ->leftJoin($db->quoteName('#__emundus_recherche_630_repeat', 'err'). ' ON '.$db->quoteName('err.parent_id') . ' = ' . $db->quoteName('er.id'))
            ->leftJoin($db->quoteName('data_regions', 'dr'). ' ON '.$db->quoteName('dr.id') . ' = ' . $db->quoteName('err.region'))
            ->where($db->quoteName('er.fnum') . ' LIKE "' . $fnum . '"');
    }
        

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

//GET Departments
function getDepartments($fnum, $profile) {
    $db = JFactory::getDBO();

    $query = $db->getquery('true');

    if ($profile == '1008') {
        $query
            ->select($db->quoteName('dd.departement_nom'))
            ->from($db->quoteName('#__emundus_recherche', 'u'))
            ->leftJoin($db->quoteName('#__emundus_recherche_744_repeat', 'ur'). ' ON '.$db->quoteName('ur.parent_id') . ' = ' . $db->quoteName('u.id'))
            ->leftJoin($db->quoteName('#__emundus_recherche_744_repeat_repeat_department', 'urd'). ' ON '.$db->quoteName('urd.parent_id') . ' = ' . $db->quoteName('ur.id'))
            ->leftJoin($db->quoteName('data_departements', 'dd'). ' ON '.$db->quoteName('dd.departement_id') . ' = ' . $db->quoteName('urd.department'))
            ->where($db->quoteName('u.fnum') . ' LIKE "' . $fnum . '"');
    }
    else {
        $query
            ->select($db->quoteName('dd.departement_nom'))
            ->from($db->quoteName('#__emundus_recherche', 'u'))
            ->leftJoin($db->quoteName('#__emundus_recherche_630_repeat', 'ur'). ' ON '.$db->quoteName('ur.parent_id') . ' = ' . $db->quoteName('u.id'))
            ->leftJoin($db->quoteName('#__emundus_recherche_630_repeat_repeat_department', 'urd'). ' ON '.$db->quoteName('urd.parent_id') . ' = ' . $db->quoteName('ur.id'))
            ->leftJoin($db->quoteName('data_departements', 'dd'). ' ON '.$db->quoteName('dd.departement_id') . ' = ' . $db->quoteName('urd.department'))
            ->where($db->quoteName('u.fnum') . ' LIKE "' . $fnum . '"');
    }

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

//GET the project disciplines
function getProjectDisciplines($fnum) {
    $db = JFactory::getDBO();

    $query = $db->getquery('true');

    $query
        ->select($db->quoteName('d.disciplines'))
        ->from($db->quoteName('#__emundus_projet', 'ep'))
        ->leftJoin($db->quoteName('#__emundus_projet_621_repeat', 'pr'). ' ON '.$db->quoteName('pr.parent_id') . ' = ' . $db->quoteName('ep.id'))
        ->leftJoin($db->quoteName('em_discipline', 'd'). ' ON '.$db->quoteName('d.id') . ' = ' . $db->quoteName('pr.disciplines'))
        ->where($db->quoteName('ep.fnum') . ' LIKE "' . $fnum. '"');

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

//GET the project thematics
function getProjectThematics($fnum) {
    $db = JFactory::getDBO();

    $query = $db->getquery('true');

    $query
        ->select($db->quoteName('t.thematic'))
        ->from($db->quoteName('#__emundus_projet', 'ep'))
        ->leftJoin($db->quoteName('#__emundus_projet_620_repeat', 'pr'). ' ON '.$db->quoteName('pr.parent_id') . ' = ' . $db->quoteName('ep.id'))
        ->leftJoin($db->quoteName('data_thematics', 't'). ' ON '.$db->quoteName('t.id') . ' = ' . $db->quoteName('pr.themes'))
        ->where($db->quoteName('ep.fnum') . ' LIKE "' . $fnum. '"');

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
?>



    <!-- Title -->
    <p class="em-offre-title">
        <?php echo $this->data['jos_emundus_projet___titre_raw'][0]; ?>
    </p>

    <div class="em-offre-meta">
        <p><?php echo JText::_('COM_EMUNDUS_FABRIK_SUBJECT_DEPOT'); ?><strong class="em-highlight"><?php echo date('d/m/Y', strtotime($fnumInfos['date_submitted'])); ?></strong></p>

    </div>

    <!-- Author -->
    <div class="em-offre-author">
        <h1 class="em-offre-title"><?php echo JText::_('COM_EMUNDUS_FABRIK_SUBJECT_PROFILE'); ?></h1>
        <div class="em-offre-author-profile">
            <div class="em-offre-author-name"><strong><?php echo JText::_('COM_EMUNDUS_FABRIK_AUTHOR_TYPE'); ?></strong><?php echo $chercheur; ?></div>
        </div>

        <?php
        // We need to change up the page based on if the person is viewing an offer from a lab, a future PHd, or a municiplaity.
        //// Profile 1006 : Futur doctorant = display no special information.
        //// Profile 1007 : Researcher = display information about his lab.
        //// Profile 1008 : Municipality = display information about the organization.

        if ($profile == '1006') :?>

            <div class="em-offre-inst">
                <div class="em-offre-institution">
                    <strong><?php echo JText::_('COM_EMUNDUS_FABRIK_AUTHOR_PARCOURS'); ?></strong>
                    <?php $master = $m_cifre->getUserMasters($author->id); ?>
                    <?php echo $master->master_2_intitule . ' - ' . $master->master_2_etablissement . ' (' . $master->master_2_annee . ')'; ?>
                </div>
            </div>

        <?php elseif ($profile == '1007') : ?>
            <?php $laboratoire = $m_cifre->getUserLaboratory($author->id); ?>
            <div class="em-offre-inst">
                <div class="em-offre-institution">
                    <strong><?php echo JText::_('COM_EMUNDUS_FABRIK_AUTHOR_RESEARCH_UNIT'); ?></strong>
                    <?php
                    if (!empty($laboratoire->website)) {
                        $parse = parse_url($laboratoire->website, PHP_URL_SCHEME) === null ? 'http://' . $laboratoire->website : $laboratoire->website;
                        echo '<a target="_blank " href="' . $parse . '">';
                    }

                    echo $laboratoire->name;
                    if (!empty($laboratoire->website))
                        echo '</a>';
                    ?>
                </div>
            </div>

            <?php $ecole_doc = $m_cifre->getDoctorale($author->id); ?>
                <?php if (!empty($ecole_doc)) :?>
                    <div class="em-offre-ecole">
                        <div class="em-offre-ecole-doctorale">
                            <strong><?php echo JText::_('COM_EMUNDUS_FABRIK_AUTHOR_SCHOOL'); ?></strong><?php echo $ecole_doc; ?>
                        </div>
                    </div>
                <?php endif; ?>



        <?php elseif ($profile == '1008') : ?>
            <?php
            $institution = $m_cifre->getUserInstitution($author->id);
            ?>
            <div class="em-offre-inst">
                <div class="em-offre-institution">
                    <strong><?php echo JText::_('COM_EMUNDUS_FABRIK_INSTITUTE_NAME'); ?></strong>
                    <?php
                    if (!empty($institution->website)) {
                        $parse = parse_url($institution->website, PHP_URL_SCHEME) === null ? 'http://' . $institution->website : $institution->website;
                        echo '<a target="_blank " href="' . $parse . '">';
                    }
                    echo $institution->nom_de_structure;
                    if (!empty($institution->website))
                        echo '</a>';
                    ?>
                </div>
            </div>


        <?php endif; ?>

        <div class="em-offre-limit-date">
            <strong><?php echo JText::_('COM_EMUNDUS_FABRIK_DISPO_DATE'); ?></strong> <?php echo date('d/m/Y', strtotime($this->data['jos_emundus_projet___limit_date'][0])); ?>
        </div>
    </div>

    <div class="em-offre">
        <h1 class="em-offre-title"><?php echo JText::_('COM_EMUNDUS_FABRIK_PROJECT_TITLE'); ?></h1>

        <p class="em-offre-subject-title">
            <strong><?php echo JText::_('COM_EMUNDUS_FABRIK_PROJECT_NAME'); ?></strong><?php echo $this->data['jos_emundus_projet___titre_raw'][0]; ?>
        </p>

        <!-- THEMES -->
        <div class="em-offre-themes">
            <div class="em-offre-subtitle"><?php echo JText::_('COM_EMUNDUS_FABRIK_THEMES'); ?></div>
            <strong class="em-highlight"><?php echo !empty(getProjectThematics($fnum)) ? implode(', ', getProjectThematics($fnum)) : JText::_('COM_EMUNDUS_FABRIK_NO_THEMES'); ?></strong>
        </div>

        <!-- DISCIPLINES -->
        <div class="em-offre-disciplines">

            <div class="em-offre-subtitle"><?php echo JText::_('COM_EMUNDUS_FABRIK_DISCIPLINES'); ?></div>
            <strong class="em-highlight"><?php echo !empty(getProjectDisciplines($fnum)) ? implode(', ', getProjectDisciplines($fnum)) : JText::_('COM_EMUNDUS_FABRIK_NO_DISCIPLINES'); ?></strong>
        </div>

        <?php if ($profile != '1008') : ?>
            <!-- Project context -->
            <p class="em-offre-contexte">
                <div class="em-offre-subtitle"><?php echo JText::_('COM_EMUNDUS_FABRIK_ENJEU'); ?>
                </div><?php echo $this->data['jos_emundus_projet___contexte_raw'][0]; ?>
            </p>

        <?php else : ?>
            <!-- Project context -->
            <p class="em-offre-contexte">
            <div class="em-offre-subtitle"><?php echo JText::_('COM_EMUNDUS_FABRIK_TERRITOIRE'); ?>
            </div><?php echo $this->data['jos_emundus_projet___contexte_raw'][0]; ?>
            </p>
        <?php endif; ?>





        <!-- Project question -->
        <?php
        if ($profile == '1006')
            $questionText = JText::_('COM_EMUNDUS_FABRIK_PROBLEMATIQUE_FUTURE_DOC');
        elseif ($profile == '1007')
            $questionText = JText::_('COM_EMUNDUS_FABRIK_PROBLEMATIQUE_CHERCHEUR');
        elseif ($profile == '1008')
            $questionText = JText::_('COM_EMUNDUS_FABRIK_GRAND_DEFI');
        ?>
        <p class="em-offre-question">
        <div class="em-offre-subtitle"><?php echo $questionText; ?></div><?php echo $this->data['jos_emundus_projet___question_raw'][0]; ?>
        </p>

            <!-- Project methodology -->
            <p class="em-offre-methodologie">
            <div class="em-offre-subtitle"><?php echo JText::_('COM_EMUNDUS_FABRIK_METHODOLOGIE'); ?>
            </div><?php echo $this->data['jos_emundus_projet___methodologie_raw'][0]; ?>
            </p>


            <div class="em-regions">
                <strong><?php echo JText::_('COM_EMUNDUS_FABRIK_REGIONS'); ?></strong>
                    <?php
                        if ($this->data["jos_emundus_recherche___all_regions_depatments_raw"] == "non") {
                            $regions = getRegions($fnum, $profile);
                                if(!empty($regions)) {
                                    echo implode(', ', array_unique($regions));
                                }
                                else {
                                    echo JText::_('COM_EMUNDUS_FABRIK_NO_REGIONS');
                                }
                        }
                        else {
                            echo JText::_('COM_EMUNDUS_FABRIK_ALL_REGIONS');
                        }

                    ?>
            </div>

        <div class="em-departments">
            <strong><?php echo JText::_('COM_EMUNDUS_FABRIK_DEPARTMENTS'); ?></strong>
            <?php
            if ($this->data["jos_emundus_recherche___all_regions_depatments_raw"] == "non") {
                $departments = getDepartments($fnum,$profile);
                if (!empty($departments)) {
                    echo implode(', ', array_unique($departments) );
                }
                else {
                    echo JText::_('COM_EMUNDUS_FABRIK_NO_DEPARTMENTS');
                }
            }
            else {
                echo JText::_('COM_EMUNDUS_FABRIK_ALL_DEPARTMANTS');
            }

            ?>
        </div>
    </div>


    <div class="em-partenaires">
        <h1 class="em-partenaires-title"><?php echo JText::_('COM_EMUNDUS_FABRIK_PARTENAIRES'); ?></h1>

        <?php if ($profile != '1006') : ?>
            <!-- Have futur docs -->
            <p class="em-partenaires-futur-doc">
                <strong><?php echo JText::_('COM_EMUNDUS_FABRIK_FUTUR_DOC'); ?></strong><?php echo $this->data['jos_emundus_recherche___futur_doctorant_yesno']; ?>
            </p>

            <?php if ($this->data["jos_emundus_recherche___futur_doctorant_yesno_raw"] == 0) : ?>
                <p class="em-partenaires-futur-doc-name">
                    <strong><?php echo JText::_('COM_EMUNDUS_FABRIK_FUTUR_DOC_NAME'); ?></strong><?php echo strtoupper($this->data["jos_emundus_recherche___futur_doctorant_nom"]) . " " . $this->data["jos_emundus_recherche___futur_doctorant_prenom"]; ?>
                </p>
            <?php endif; ?>

        <?php endif; ?>

        <?php if ($profile != '1007') :?>
            <p class="em-partenaires-equipe-recherche">
                <strong><?php echo JText::_('COM_EMUNDUS_FABRIK_EQUIPE_RECHERCHE'); ?></strong><?php echo $this->data["jos_emundus_recherche___equipe_de_recherche_direction_yesno"]; ?>
            </p>
            <?php if ($this->data["jos_emundus_recherche___equipe_de_recherche_direction_yesno_raw"] == 0) : ?>
                <p class="em-partenaires-equipe-recherche-name">
                    <strong><?php echo JText::_('COM_EMUNDUS_FABRIK_EQUIPE_RECHERCHE_NAME'); ?></strong><?php echo $this->data["jos_emundus_recherche___equipe_direction_equipe_de_recherche_raw"]; ?>
                </p>
            <?php endif; ?>
        <?php endif; ?>



        <?php if ($this->data["jos_emundus_setup_profiles___id_raw"][0] != '1008') : ?>
            <p class="em-partenaires-acteur">
                <strong><?php echo JText::_('COM_EMUNDUS_FABRIK_EQUIPE_ACTEUR_PUB'); ?></strong><?php echo $this->data["jos_emundus_recherche___acteur_public_yesno"]; ?>
            </p>

            <p class="em-partenaires-acteur-type">
                <strong><?php echo JText::_('COM_EMUNDUS_FABRIK_EQUIPE_ACTEUR_PUB_TYPE'); ?></strong><?php echo $this->data["jos_emundus_recherche___acteur_public_type_raw"]; ?>
            </p>

            <?php if ($this->data["jos_emundus_recherche___acteur_public_yesno_raw"] == 0) : ?>
                <p class="em-partenaires-acteur-type">
                    <strong><?php echo JText::_('COM_EMUNDUS_FABRIK_EQUIPE_ACTEUR_PUB_NAME'); ?></strong><?php echo $this->data["jos_emundus_recherche___acteur_public_nom_de_structure_raw"]; ?>
                </p>
            <?php endif; ?>
        <?php endif; ?>

    </div>

<?php if (!empty($files)) : ?>
    <div class="em-attached-files">
        <h1 class="em-attached-title"><?php echo JText::_('COM_EMUNDUS_FABRIK_ATTACHED_FILES'); ?></h1>

        <?php foreach ($files as $file) : ?>

            <p class="em-attached-element">
            <div class="em-partenaires-subtitle"><?php echo $file["value"] . ' : '; ?></div><a target="_blank" href="<?php echo JURI::root() . 'images' . DS . 'emundus' . DS . 'files' . DS . $this->data['jos_emundus_campaign_candidature___applicant_id_raw'] . DS . $file['filename']; ?>"><?php echo $file['filename']; ?></a>
            </p>

        <?php endforeach; ?>
    </div>
<?php endif; ?>

    <!-- Contact information -->
    <div class="em-offre-contact">

<?php
// Log the action of opening the persons form.
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'logs.php');
EmundusModelLogs::log($user->id, $author->id, $fnum, 33, 'r', 'COM_EMUNDUS_LOGS_OPEN_OFFER');

if ((isset($this->data['Status']) && $this->data['Status'][0] == 2) || (isset($this->data['jos_emundus_campaign_candidature___status']) && $this->data['jos_emundus_campaign_candidature___status'][0] == 2)) {
    $status = 2;
} else {
    $status = 1;
}

if ($status === 2) :?>

    <div class="em-search-item-action">
        <div id="em-search-item-action-button">
            <button type="button" class="btn btn-default" disabled><?php echo JText::_('COM_EMUNDUS_CIFRE_CANCELED_BUTTON'); ?></button>
        </div>
    </div>

<?php elseif ($this->data['jos_emundus_campaign_candidature___applicant_id'][0] == JFactory::getUser()->id) : ?>

    <?php if ((isset($d['Status']) && $d['Status'] == 3) || (isset($d['jos_emundus_campaign_candidature___status']) && $d['jos_emundus_campaign_candidature___status'] == 3)) : ?>

        <div class="em-search-item-action">
            <div id="em-search-item-action-button">
                <button type="button" class="btn btn-default" disabled><?php echo JText::_('COM_EMUNDUS_CIFRE_WAITING_BUTTON'); ?></button>
            </div>
        </div>

    <?php else : ?>

        <div class="em-search-item-action">
            <div id="em-search-item-action-button">
                <button type="button" class="btn btn-default" disabled><?php echo JText::_('COM_EMUNDUS_CIFRE_OWN_BUTTON'); ?></button>
            </div>
        </div>

    <?php endif; ?>

<?php else : ?>

    <?php
    // Action button types:
    // // NO BUTTON : if the offer belongs to the user.
    // // ENTREZ EN CONTACT : If the user has not already contacted.
    // // REPONDRE : If the user has already been contacted for this offer but has not answered.
    // // RELANCE : If the user has contacted but not been answered yet.
    // // BREAK UP : If the user is collaborating with the other.
    require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'controllers' . DS . 'cifre.php');
    $c_ciffe = new EmundusControllerCifre();
    $action_button = $c_ciffe->getActionButton($fnum);
    ?>

    <!-- Button used for matching with the offer -->
    <div class="em-search-item-action">

    <span class="alert alert-danger hidden" id="em-action-text"></span>

    <div id="em-search-item-action-button">

        <?php if ($action_button == 'contact') : ?>

    <?php $offers = $c_ciffe->getOwnOffers($fnum); ?>

        <button type="button" class="btn btn-success hesam-btn-contact" data-toggle="modal"
                data-target="#contactModal">
            <?php echo JText::_('COM_EMUNDUS_CIFRE_CONTACT_BUTTON'); ?>
        </button>

        <div class="modal fade" id="contactModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php echo JText::_('COM_EMUNDUS_CIFRE_CONTACT'); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <?php if ($user->profile == '1006') : ?>
                        <p><?php echo JText::_('COM_EMUNDUS_CIFRE_FUTUR_DOC_FIRST_QUESTION'); ?></p>
                        <textarea id="em-contact-message" placeholder="Texte (2000 caractères)" maxlength="2000"></textarea>
                        <p><?php echo JText::_('COM_EMUNDUS_CIFRE_FUTUR_DOC_SECOND_QUESTION'); ?></p>
                        <textarea id="em-contact-motivation" placeholder="Texte (2000 caractères)" maxlength="2000"></textarea>
                        <?php if (!empty($offers)) : ?>
                            <p><?php echo JText::_('COM_EMUNDUS_CIFRE_JOIN_CIFRE'); ?></p>
                            <select id="em-join-offer">
                                <option value=""><?php echo JText::_('COM_EMUNDUS_CIFRE_NO_JOIN_CIFRE'); ?></option>
                                <?php foreach ($offers as $offer) : ?>
                                    <option value="<?php echo $offer->fnum; ?>"><?php echo $offer->titre; ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>

                        <hr>
                        <span class="em-upload-explain-text"><?php echo JText::_('COM_EMUNDUS_CIFRE_SELECT_FILE'); ?></span>
                        <!-- Upload a file from computer -->
                        <div id="em-attachment-list">
                            <div id="cv-upload_file">
                                <h4 id="em-filename"><?php echo JText::_('COM_EMUNDUS_CIFRE_ADD_CV'); ?></h4>
                                <label for="em-cv_to_upload" accept="application/pdf"
                                       id="em-cv_to_upload_label">
                                    <input type="file" id="em-cv_to_upload">
                                </label>
                                <span className="file-name" id="cv-file-name"></span>
                            </div>

                            <span class="input-group-btn">
                                    <a class="btn btn-grey" type="button" id="uploadButton" style="top:13px;"
                                       onClick="cvAddFile();"><?php echo JText::_('COM_EMUNDUS_CIFRE_JOIN'); ?></a>
                                </span>

                            <div id="doc-upload_file">
                                <h4 id="em-filename"><?php echo JText::_('COM_EMUNDUS_CIFRE_ADD_FILE'); ?></h4>
                                <span class="em-upload-explain-text"><?php echo JText::_('COM_EMUNDUS_CIFRE_SELECT_FILE'); ?></span>
                                <label for="em-doc_to_upload" id="em-doc_to_upload_label">
                                    <input type="file" id="em-doc_to_upload">
                                </label>
                                <span className="file-name" id="other-doc-file-name"></span>
                            </div>

                            <span class="input-group-btn">
                                        <a class="btn btn-grey" type="button" accept="application/pdf" id="uploadButton"
                                           style="top:13px;" onClick="docAddFile();"><?php echo JText::_('COM_EMUNDUS_CIFRE_JOIN'); ?></a>
                                    </span>

                            <?php else : ?>

                            <p><?php echo JText::_('COM_EMUNDUS_CIFRE_QUESTION'); ?></p>
                            <textarea id="em-contact-message" placeholder="Texte (3000 caractères)" maxlength="3000"></textarea>
                            <?php if (!empty($offers)) : ?>
                                <p><?php echo JText::_('COM_EMUNDUS_CIFRE_JOIN_CIFRE'); ?></p>
                                <select id="em-join-offer">
                                    <option value=""><?php echo JText::_('COM_EMUNDUS_CIFRE_NO_JOIN_CIFRE'); ?></option>
                                    <?php foreach ($offers as $offer) : ?>
                                        <option value="<?php echo $offer->fnum; ?>"><?php echo $offer->titre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>

                            <hr>
                            <!-- Upload a file from computer -->
                            <div id="em-attachment-list">
                                <div id="doc-upload_file">
                                    <h4 id="em-filename"><?php echo JText::_('COM_EMUNDUS_CIFRE_ADD_FILE'); ?></h4>
                                    <span class="em-upload-explain-text"><?php echo JText::_('COM_EMUNDUS_CIFRE_SELECT_FILE'); ?></span>
                                    <label for="em-doc_to_upload" id="em-doc_to_upload_label">
                                        <input type="file" id="em-doc_to_upload">
                                    </label>
                                    <span className="file-name" id="other-doc-file-name"></span>
                                </div>

                                <span class="input-group-btn">
                                        <a class="btn btn-grey" type="button" accept="application/pdf" id="uploadButton"
                                           style="top:13px;" onClick="docAddFile();"><?php echo JText::_('COM_EMUNDUS_CIFRE_JOIN'); ?></a>
                                    </span>

                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-dismiss="modal"
                                        onclick="actionButton('contact')"><?php echo JText::_('COM_EMUNDUS_CIFRE_SEND_CONTACT'); ?>
                                </button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo JText::_('CANCEL'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <?php elseif ($action_button == 'reply') : ?>
                    <button type="button" class="btn btn-primary" onclick="actionButton('reply')">
                        <?php echo JText::_('COM_EMUNDUS_CIFRE_ANSWER'); ?>
                    </button>
                    <button type="button" class="btn btn-primary" onclick="breakUp('ignore')">
                        <?php echo JText::_('COM_EMUNDUS_CIFRE_IGNORE'); ?>
                    </button>

                <?php elseif ($action_button == 'retry') : ?>
                    <button type="button" class="btn btn-primary" onclick="actionButton('retry')">
                        <?php echo JText::_('COM_EMUNDUS_CIFRE_RECALL'); ?>
                    </button>
                    <button type="button" class="btn btn-primary" onclick="breakUp('cancel')">
                        <?php echo JText::_('COM_EMUNDUS_CIFRE_CANCEL'); ?>
                    </button>

                <?php elseif ($action_button == 'breakup') : ?>
                    <button type="button" class="btn btn-primary" onclick="breakUp('breakup')">
                        <?php echo JText::_('COM_EMUNDUS_CIFRE_CUT_CONTACT'); ?>
                    </button>
                <?php endif; ?>

            </div>
        </div>
        <?php echo $this->loadTemplate('buttons'); ?>
    </div>

    <div class="em-modal-sending-emails" id="em-modal-sending-emails">
        <div id="em-sending-email-caption"><?php echo JText::_('COM_EMUNDUS_CIFRE_SENDING'); ?></div>
        <img class="em-sending-email-img" id="em-sending-email-img" src="/images/emundus/sending-email.gif">
    </div>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>

        <script>


        jQuery('#em-doc_to_upload').on('change',function(evt) {
            jQuery('#other-doc-file-name').html(evt.target.files[0].name);
        });

        jQuery('#em-cv_to_upload').on('change',function(evt) {
            jQuery('#cv-file-name').html(evt.target.files[0].name);
        });

        function actionButton(action) {

            var fnum = '<?php echo $fnum; ?>';
            var data = {
                fnum: fnum
            };

            if (action == 'contact') {

                jQuery('#em-modal-sending-emails').css('display', 'block');
                if (document.getElementById('em-join-offer') != null) {
                    // Get the offer selected from the dropdown by the user.
                    var linkedOffer = document.getElementById('em-join-offer').value;
                    if (linkedOffer != null && linkedOffer != '' && typeof linkedOffer != 'undefined')
                        data.linkedOffer = linkedOffer;

                }

                // Get the attached message if there is one.
                var message = document.getElementById('em-contact-message').value;
                if (message != null && message != '' && typeof message != 'undefined')
                    data.message = message;

                // Get the attached message if there is one.
                if (document.getElementById('em-contact-motivation')) {
                    var motivation = document.getElementById('em-contact-motivation').value;
                    if (motivation != null && motivation != '' && typeof motivation != 'undefined')
                        data.motivation = motivation;
                }

                var CV = jQuery('#cv-upload_file').find('.hidden').text();
                if (CV != null && CV != '' && typeof CV != 'undefined')
                    data.CV = CV;

                var DOC = jQuery('#doc-upload_file').find('.hidden').text();
                if (DOC != null && DOC != '' && typeof DOC != 'undefined')
                    data.DOC = DOC;

                data.bcc = jQuery('#em-bcc-me').prop('checked');

            }

            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'index.php?option=com_emundus&controller=cifre&task=' + action,
                data: data,
                beforeSend: function () {
                    jQuery('#em-search-item-action-button').html('<button type="button" class="btn btn-default" disabled> ... </button>');

                    if (action == 'contact') {
                        jQuery('#contactModal').modal('hide');
                        jQuery('body').removeClass('modal-open');
                        jQuery('.modal-backdrop').remove();
                    }

                },
                success: function (result) {
                    jQuery('#em-modal-sending-emails').css('display', 'none');
                    Swal.fire(
                        '<?php echo JText::_("COM_EMUNDUS_CIFRE_SENT"); ?>',
                        '',
                        'success'
                    )
                    if (result.status) {

                        // When we successfully change the status, we simply dynamically change the button.
                        if (action == 'contact') {
                            jQuery('#em-search-item-action-button').html('<button type="button" class="btn btn-primary" onclick="actionButton(\'retry\')"><?php echo JText::_("COM_EMUNDUS_CIFRE_RECALL"); ?></button> ' +
                                ' <button type="button" class="btn btn-primary" onclick="breakUp(\'cancel\')">Annuler la demande</button>');
                        } else if (action == 'retry') {
                            jQuery('#em-search-item-action-button').html('<button type="button" class="btn btn-default" disabled ><?php echo JText::_("COM_EMUNDUS_CIFRE_SENT"); ?></button>');
                        } else if (action == 'reply') {
                            jQuery('#em-search-item-action-button').html('<button type="button" class="btn btn-danger" onclick="breakUp()"><?php echo JText::_("COM_EMUNDUS_CIFRE_CUT_CONTACT"); ?></button>');
                        }

                    } else {
                        var actionText = document.getElementById('em-action-text');
                        actionText.classList.remove('hidden');
                        actionText.innerHTML = result.msg;
                    }
                },
                error: function (jqXHR) {
                    jQuery('#em-modal-sending-emails').css('display', 'none');
                    console.log(jqXHR.responseText);
                }
            });
        }

        function breakUp(action) {
            var data = {
                fnum: '<?php echo $fnum; ?>'
            };

            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'index.php?option=com_emundus&controller=cifre&task=breakup&action=' + action,
                data: data,
                beforeSend: function () {
                    jQuery('#em-search-item-action-button').html('<button type="button" class="btn btn-default" disabled> ... </button>');
                },
                success: function (result) {
                    if (result.status) {

                        // Dynamically change the button back to the state of not having a link.
                        jQuery('#em-search-item-action-button').html('' +
                            '<button type="button" class="btn btn-success hesam-btn-contact" data-toggle="modal" data-target="#contactModal">' +
                            '        <?php echo JText::_("COM_EMUNDUS_CIFRE_CONTACT_BUTTON"); ?>' +
                            '        </button>' +
                            '        <div class="modal fade" id="contactModal" tabindex="-1" role="dialog">' +
                            '            <div class="modal-dialog" role="document">' +
                            '                <div class="modal-content">' +
                            '                    <div class="modal-header">' +
                            '                        <h5 class="modal-title"><?php echo JText::_("COM_EMUNDUS_CIFRE_CONTACT"); ?></h5>' +
                            '                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">' +
                            '                            <span aria-hidden="true">&times;</span>' +
                            '                        </button>' +
                            '                    </div>' +
                            '                    <div class="modal-body">' +
                            <?php if ($user->profile == '1006') : ?>
                            '             <p><?php echo JText::_("COM_EMUNDUS_CIFRE_FUTUR_DOC_FIRST_QUESTION"); ?></p>' +
                            '                <textarea id="em-contact-message" placeholder="Texte (2000 caractères)" maxlength="2000"></textarea>' +
                            '            <p><?php echo JText::_("COM_EMUNDUS_CIFRE_FUTUR_DOC_SECOND_QUESTION"); ?></p>' +
                            '                <textarea id="em-contact-motivation" placeholder="Texte (2000 caractères)" maxlength="2000"></textarea>' +
                            <?php if (!empty($offers)) : ?>
                            '                 <p><?php echo JText::_("COM_EMUNDUS_CIFRE_JOIN_CIFRE"); ?></p>' +
                            '                 <select id="em-join-offer">' +
                            '                     <option value=""><?php echo JText::_("COM_EMUNDUS_CIFRE_NO_JOIN_CIFRE"); ?></option>' +
                            <?php foreach ($offers as $offer) : ?>
                            '                         <option value="<?php echo $offer->fnum; ?>"><?php echo $offer->titre; ?></option>' +
                            <?php endforeach; ?>
                            '                </select>' +
                            <?php endif; ?>

                            '            <hr>' +
                            '            <span class="em-upload-explain-text"><?php echo JText::_("COM_EMUNDUS_CIFRE_SELECT_CV"); ?></span>' +
                            '            <div id="em-attachment-list">' +
                            '                <div id="cv-upload_file">' +
                            '                    <h4 id="em-filename"><?php echo JText::_("COM_EMUNDUS_CIFRE_ADD_CV"); ?></h4>' +
                            '                     <label for="em-cv_to_upload" accept="application/pdf"' +
                            '                            id="em-cv_to_upload_label">' +
                            '                         <input type="file" id="em-cv_to_upload">' +
                            '                    </label>' +
                            '                   <span className="file-name" id="cv-file-name"></span>' +
                            '                </div>' +

                            '               <span class="input-group-btn">' +
                            '       <a class="btn btn-grey" type="button" id="uploadButton" style="top:13px;"' +
                            '           onClick="cvAddFile();"><?php echo JText::_("COM_EMUNDUS_CIFRE_JOIN"); ?></a>' +
                            '  </span>' +

                            '   <div id="doc-upload_file">' +
                            '                 <h4 id="em-filename"><?php echo JText::_("COM_EMUNDUS_CIFRE_ADD_FILE"); ?></h4>' +
                            '                 <span class="em-upload-explain-text"><?php echo JText::_("COM_EMUNDUS_CIFRE_SELECT_FILE"); ?></span>' +
                            '                 <label for="em-doc_to_upload" id="em-doc_to_upload_label">' +
                            '                     <input type="file" id="em-doc_to_upload">' +
                            '                 </label>' +
                            '                   <span className="file-name" id="other-doc-file-name"></span>' +
                            '             </div>' +

                            '             <span class="input-group-btn">' +
                            '         <a class="btn btn-grey" type="button" accept="application/pdf" id="uploadButton"' +
                            '            style="top:13px;" onClick="docAddFile();"><?php echo JText::_("COM_EMUNDUS_CIFRE_JOIN"); ?></a>' +
                            '     </span>' +

                            <?php else : ?>

                            '          <p><?php echo JText::_("COM_EMUNDUS_CIFRE_QUESTION"); ?></p>' +
                            '          <textarea id="em-contact-message" placeholder="Texte (3000 caractères)" maxlength="3000"></textarea>' +
                            <?php if (!empty($offers)) : ?>
                            '              <p><?php echo JText::_("COM_EMUNDUS_CIFRE_JOIN_CIFRE"); ?></p>' +
                            '              <select id="em-join-offer">' +
                            '                  <option value=""><?php echo JText::_("COM_EMUNDUS_CIFRE_NO_JOIN_CIFRE"); ?></option>' +
                            <?php foreach ($offers as $offer) : ?>
                            '                      <option value="<?php echo $offer->fnum; ?>"><?php echo str_replace("'", "\\'", $offer->titre); ?></option>' +
                            <?php endforeach; ?>
                            '              </select>' +
                            <?php endif; ?>

                            '         <hr>' +
                            '         <div id="em-attachment-list">' +
                            '             <div id="doc-upload_file">' +
                            '                 <h4 id="em-filename"<?php echo JText::_("COM_EMUNDUS_CIFRE_ADD_FILE"); ?></h4>' +
                            '                 <span class="em-upload-explain-text"><?php echo JText::_("COM_EMUNDUS_CIFRE_SELECT_FILE"); ?></span>' +
                            '                 <label for="em-doc_to_upload" id="em-doc_to_upload_label">' +
                            '                     <input type="file" id="em-doc_to_upload">' +
                            '                 </label>' +
                            '                   <span className="file-name" id="other-doc-file-name"></span>' +
                            '             </div>' +

                            '             <span class="input-group-btn">' +
                            '         <a class="btn btn-grey" type="button" accept="application/pdf" id="uploadButton"' +
                            '            style="top:13px;" onClick="docAddFile();"><?php echo JText::_("COM_EMUNDUS_CIFRE_JOIN"); ?></a>' +
                            '     </span>' +

                            <?php endif; ?>
                            '                    </div>' +
                            '                    <div class="modal-footer">' +
                            '                        <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="actionButton(\'contact\')"><?php echo JText::_("COM_EMUNDUS_CIFRE_SEND_CONTACT"); ?></button>' +
                            '                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo JText::_("CANCEL"); ?></button>' +
                            '                    </div>' +
                            '                </div>' +
                            '            </div>');

                    } else {
                        var actionText = document.getElementById('em-action-text');
                        actionText.classList.remove('hidden');
                        actionText.innerHTML = result.msg;
                    }
                },
                error: function (jqXHR) {
                    console.log(jqXHR.responseText);
                }
            });
        }

        // Add file to the list being attached.
        function cvAddFile() {
            // We need to get the file uploaded by the user.

            var cv = jQuery("#em-cv_to_upload")[0].files[0];
            var cvId = jQuery("#cv-upload_file");
            var uploadcv = new Upload(cv, cvId);

            // Verification of style size and type can be done here.
            uploadcv.doUpload();
            jQuery('#cv-file-name').empty();
        }

        // Add file to the list being attached.
        function docAddFile() {
            console.log("hello");
            // We need to get the file uploaded by the user.
            var doc = jQuery("#em-doc_to_upload")[0].files[0];
            var docId = jQuery("#doc-upload_file");
            var uploaddoc = new Upload(doc, docId);

            // Verification of style size and type can be done here.
            uploaddoc.doUpload();
            jQuery('#other-doc-file-name').empty();
        }

        // Helper function for uploading a file via AJAX.
        var Upload = function (file, id) {
            this.file = file;
            this.id = id;
        };

        Upload.prototype.getType = function () {
            return this.file.type;
        };
        Upload.prototype.getSize = function () {
            return this.file.size;
        };
        Upload.prototype.getName = function () {
            return this.file.name;
        };

        Upload.prototype.doUpload = function () {
            var that = this;
            var formData = new FormData();

            if (this.getType() != 'application/pdf') {
                Swal.fire({
                    type: 'error',
                    title: '<?php echo JText::_("COM_EMUNDUS_CIFRE_ALERT_PDF"); ?>'
                })
                return false;
            }

            // add assoc key values, this will be posts values
            formData.append("file", this.file, this.getName());
            formData.append("upload_file", true);
            formData.append('filetype', 'pdf');
            formData.append('user', <?php echo $user->id; ?>);
            formData.append('fnum', '<?php echo $fnum; ?>');

            jQuery.ajax({
                type: "POST",
                url: "index.php?option=com_emundus&controller=messages&task=uploadfiletosend",
                xhr: function () {
                    var myXhr = jQuery.ajaxSettings.xhr();
                    if (myXhr.upload) {
                        myXhr.upload.addEventListener('progress', that.progressHandling, false);
                    }
                    return myXhr;
                },
                success: function (data) {
                    data = JSON.parse(data);
                    if (data.status) {
                        jQuery(that.id).find('.list-group-item').remove();
                        jQuery(that.id).append('<li class="list-group-item upload"><div class="value hidden">' + data.file_path + '</div>' + data.file_name + '<span class="badge btn-danger" onClick="removeAttachment(this);"><i class="fa fa-times"></i></span></li>');
                    } else {
                        jQuery(that.id).append('<span class="alert"> <?php echo JText::_('UPLOAD_FAILED'); ?> </span>')
                    }

                },
                error: function (error) {
                    // handle error
                    this.id.append('<span class="alert"> <?php echo JText::_('UPLOAD_FAILED'); ?> </span>')
                },
                async: true,
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                timeout: 60000
            });
        };


        function removeAttachment(element) {

            element = jQuery(element);

            if (element.parent().hasClass('candidate_file')) {

                // Remove 'disabled' attr from select options.
                jQuery('#em-select_candidate_file option[value="' + element.parent().find('.value').text() + '"]').prop('disabled', false);

            } else if (element.parent().hasClass('setup_letters')) {

                // Remove 'disabled' attr from select options.
                jQuery('#em-select_setup_letters option[value="' + element.parent().find('.value').text() + '"]').prop('disabled', false);

            }

            jQuery(element).parent().remove();
        }

    </script>

<?php endif;

echo $this->pluginbottom;
echo $this->loadTemplate('actions');
echo '</div>';
echo $form->outro;
echo $this->pluginend;