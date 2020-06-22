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
    <div class="componentheading<?= $this->params->get('pageclass_sfx') ?>">
        <?= $this->escape($this->params->get('page_heading')); ?>
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


echo $this->plugintop;
echo $this->loadTemplate('relateddata');

$region = "";
$department = "";
$cherches = "";
$themes = "";
$author_type = strtolower($this->data['jos_emundus_setup_profiles___label_raw'][0]);
$profile = $this->data['jos_emundus_setup_profiles___id_raw'][0];

require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'cifre.php');
$m_cifre = new EmundusModelCifre();


// GET Regions
function getRegions($fnum, $profile) {
    $db = JFactory::getDBO();
    $query = $db->getquery('true');

    if ($profile == '1008') {
        $query->select($db->quoteName('dr.name'))
            ->from($db->quoteName('#__emundus_recherche', 'er'))
            ->leftJoin($db->quoteName('#__emundus_recherche_744_repeat', 'err'). ' ON '.$db->quoteName('err.parent_id') . ' = ' . $db->quoteName('er.id'))
            ->leftJoin($db->quoteName('data_regions', 'dr'). ' ON '.$db->quoteName('dr.id') . ' = ' . $db->quoteName('err.region'))
            ->where($db->quoteName('er.fnum') . ' LIKE "' . $fnum . '"');
    } else {
        $query->select($db->quoteName('dr.name'))
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
        $query->select($db->quoteName('dd.departement_nom'))
            ->from($db->quoteName('#__emundus_recherche', 'u'))
            ->leftJoin($db->quoteName('#__emundus_recherche_744_repeat', 'ur'). ' ON '.$db->quoteName('ur.parent_id') . ' = ' . $db->quoteName('u.id'))
            ->leftJoin($db->quoteName('#__emundus_recherche_744_repeat_repeat_department', 'urd'). ' ON '.$db->quoteName('urd.parent_id') . ' = ' . $db->quoteName('ur.id'))
            ->leftJoin($db->quoteName('data_departements', 'dd'). ' ON '.$db->quoteName('dd.departement_id') . ' = ' . $db->quoteName('urd.department'))
            ->where($db->quoteName('u.fnum') . ' LIKE "' . $fnum . '"');
    } else {
        $query->select($db->quoteName('dd.departement_nom'))
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

    $query->select($db->quoteName('d.disciplines'))
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

    $query->select($db->quoteName('t.thematic'))
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

// OpenGraph
$document = JFactory::getDocument();
$document->setMetaData('og:url', JUri::getInstance()->toString());
$document->setMetaData('og:type', 'website');
$document->setMetaData('og:title', $this->data['jos_emundus_projet___titre_raw'][0]);
$document->setMetaData('og:description', $this->data['jos_emundus_projet___question_raw'][0]);

?>

<div class="content-2">
    <div class="w-container">

        <!-- Title -->
        <h1 class="heading-2 no-dash">
            <?= $this->data['jos_emundus_projet___titre_raw'][0]; ?>
        </h1>
        <h4 class="date"><?= JText::_('COM_EMUNDUS_FABRIK_SUBJECT_DEPOT'); ?><strong class="bold-text"><?= date('d/m/Y', strtotime($fnumInfos['date_submitted'])); ?></strong></h4>
        <div class="underline"></div>

        <!-- Author -->
        <div class="profil-container">
            <h3 class="heading no-dash"><?= JText::_('COM_EMUNDUS_FABRIK_SUBJECT_PROFILE'); ?></h3>
            <div class="underline-small"></div>
            <p class="paragraph-infos">
                <strong><?= JText::_('COM_EMUNDUS_FABRIK_AUTHOR_TYPE'); ?></strong><?= $author_type; ?><br>

                <?php
                // We need to change up the page based on if the person is viewing an offer from a lab, a future PHd, or a municiplaity.
                //// Profile 1006 : Futur doctorant = display no special information.
                //// Profile 1007 : Researcher = display information about his lab.
                //// Profile 1008 : Municipality = display information about the organization.

                if ($profile == '1006') :?>
                    <strong><?= JText::_('COM_EMUNDUS_FABRIK_AUTHOR_PARCOURS'); ?></strong>
                    <?php $master = $m_cifre->getUserMasters($author->id); ?>
                    <?= $master->master_2_intitule . ' - ' . $master->master_2_etablissement . ' (' . $master->master_2_annee . ')'; ?><br>
                <?php elseif ($profile == '1007') : ?>
                    <?php $laboratoire = $m_cifre->getUserLaboratory($author->id); ?>
                    <strong><?= JText::_('COM_EMUNDUS_FABRIK_AUTHOR_RESEARCH_UNIT'); ?></strong>
                    <?php
                        if (!empty($laboratoire->website)) {
                            $parse = parse_url($laboratoire->website, PHP_URL_SCHEME) === null ? 'http://' . $laboratoire->website : $laboratoire->website;
                            echo '<a target="_blank " href="' . $parse . '">';
                        }
                        echo $laboratoire->name;
                        if (!empty($laboratoire->website)) {
                            echo '</a>';
                        }
                    ?>
                    <br>
                    <?php $ecole_doc = $m_cifre->getDoctorale($author->id); ?>
                    <?php if (!empty($ecole_doc)) :?>
                        <strong><?= JText::_('COM_EMUNDUS_FABRIK_AUTHOR_SCHOOL'); ?></strong><?= $ecole_doc; ?><br>
                    <?php endif; ?>

                <?php elseif ($profile == '1008') :?>
                    <?php $institution = $m_cifre->getUserInstitution($author->id); ?>
                    <strong><?= JText::_('COM_EMUNDUS_FABRIK_INSTITUTE_NAME'); ?></strong>
                    <?php
                        if (!empty($institution->website)) {
                            $parse = parse_url($institution->website, PHP_URL_SCHEME) === null ? 'http://' . $institution->website : $institution->website;
                            echo '<a target="_blank " href="' . $parse . '">';
                        }
                        echo $institution->nom_de_structure;
                        if (!empty($institution->website)) {
                            echo '</a>';
                        }
                    ?>
                    <br>
                <?php endif; ?>
            </p>

            <div class="em-offre-limit-date">
                <strong><?= JText::_('COM_EMUNDUS_FABRIK_DISPO_DATE'); ?></strong> <?= date('d/m/Y', strtotime($this->data['jos_emundus_projet___limit_date'][0])); ?>
            </div>
        </div>

        <div class="profil-container">
            <h3 class="heading no-dash"><?= JText::_('COM_EMUNDUS_FABRIK_PROJECT_TITLE'); ?></h3>
            <div class="underline-small"></div>

            <p class="paragraph-infos">

                <!-- Regions -->
                <strong><?= JText::_('COM_EMUNDUS_FABRIK_REGIONS'); ?></strong><br>
                <?php
                    if ($this->data["jos_emundus_recherche___all_regions_depatments_raw"] == "non") {
                        $regions = getRegions($fnum, $profile);
                        if (!empty($regions)) {
                            echo implode(', ', array_unique($regions));
                        } else {
                            echo JText::_('COM_EMUNDUS_FABRIK_NO_REGIONS');
                        }
                    } else {
                        echo JText::_('COM_EMUNDUS_FABRIK_ALL_REGIONS');
                    }
                ?>
                <br>
                <br>
                <!-- Departements -->
                <strong><?= JText::_('COM_EMUNDUS_FABRIK_DEPARTMENTS'); ?></strong><br>
                <?php
                    if ($this->data["jos_emundus_recherche___all_regions_depatments_raw"] == "non") {
                        $departments = getDepartments($fnum,$profile);
                        if (!empty($departments)) {
                            echo implode(', ', array_unique($departments) );
                        } else {
                            echo JText::_('COM_EMUNDUS_FABRIK_NO_DEPARTMENTS');
                        }
                    } else {
                        echo JText::_('COM_EMUNDUS_FABRIK_ALL_DEPARTMANTS');
                    }
                ?>
                <br>
                <br>
                <!-- THEMES -->
                <strong><?= JText::_('COM_EMUNDUS_FABRIK_THEMES'); ?></strong><br>
                <?= !empty(getProjectThematics($fnum)) ? implode(', ', getProjectThematics($fnum)) : JText::_('COM_EMUNDUS_FABRIK_NO_THEMES'); ?>
                <br>
                <br>
                <!-- DISCIPLINES -->
                <strong><?= JText::_('COM_EMUNDUS_FABRIK_DISCIPLINES'); ?></strong><br>
                <?= !empty(getProjectDisciplines($fnum)) ? implode(', ', getProjectDisciplines($fnum)) : JText::_('COM_EMUNDUS_FABRIK_NO_DISCIPLINES'); ?>
                <br>
                <br>
                <!-- Project context -->
                <?php if ($profile != '1008') : ?>
                    <strong><?= JText::_('COM_EMUNDUS_FABRIK_ENJEU'); ?></strong><br>
                    <?= $this->data['jos_emundus_projet___contexte_raw'][0]; ?>
                <?php else : ?>
                    <strong><?= JText::_('COM_EMUNDUS_FABRIK_TERRITOIRE'); ?></strong><br>
                    <?= $this->data['jos_emundus_projet___contexte_raw'][0]; ?>
                <?php endif; ?>
                <br>
                <br>
                <!-- Project question -->
                <?php
                if ($profile == '1006') {
                    $questionText = JText::_('COM_EMUNDUS_FABRIK_PROBLEMATIQUE_FUTURE_DOC');
                } elseif ($profile == '1007') {
                    $questionText = JText::_('COM_EMUNDUS_FABRIK_PROBLEMATIQUE_CHERCHEUR');
                } elseif ($profile == '1008') {
                    $questionText = JText::_('COM_EMUNDUS_FABRIK_GRAND_DEFI');
                }
                ?>
                <strong><?= $questionText; ?></strong><br>
                <?= $this->data['jos_emundus_projet___question_raw'][0]; ?>
                <br>
                <br>
                <!-- Project methodology -->
                <strong><?= JText::_('COM_EMUNDUS_FABRIK_METHODOLOGIE'); ?></strong><br>
                <?= $this->data['jos_emundus_projet___methodologie_raw'][0]; ?>
            </p>
        </div>


        <div class="profil-container">
            <h3 class="heading no-dash"><?= JText::_('COM_EMUNDUS_FABRIK_PARTENAIRES'); ?></h3>
            <div class="underline-small"></div>

            <p class="paragraph-infos">

            </p>
            <?php if ($profile != '1006') : ?>
                <!-- Have futur docs -->
                <strong><?= JText::_('COM_EMUNDUS_FABRIK_FUTUR_DOC'); ?></strong><?= $this->data['jos_emundus_recherche___futur_doctorant_yesno']; ?>
                <?php if ($this->data["jos_emundus_recherche___futur_doctorant_yesno_raw"] == 0) : ?>
                    <br>
                    <strong><?= JText::_('COM_EMUNDUS_FABRIK_FUTUR_DOC_NAME'); ?></strong><?= strtoupper($this->data["jos_emundus_recherche___futur_doctorant_nom"]) . " " . $this->data["jos_emundus_recherche___futur_doctorant_prenom"]; ?>
                <?php endif; ?>
                <br>
            <?php endif; ?>
            <?php if ($profile != '1007') :?>
                <strong><?= JText::_('COM_EMUNDUS_FABRIK_EQUIPE_RECHERCHE'); ?></strong><?= $this->data["jos_emundus_recherche___equipe_de_recherche_direction_yesno"]; ?>
                <?php if ($this->data["jos_emundus_recherche___equipe_de_recherche_direction_yesno_raw"] == 0) : ?>
                    <br>
                    <strong><?= JText::_('COM_EMUNDUS_FABRIK_EQUIPE_RECHERCHE_NAME'); ?></strong><?= $this->data["jos_emundus_recherche___equipe_direction_equipe_de_recherche_raw"]; ?>
                <?php endif; ?>
                <br>
            <?php endif; ?>
            <?php if ($profile != '1008') : ?>
                <strong><?= JText::_('COM_EMUNDUS_FABRIK_EQUIPE_ACTEUR_PUB'); ?></strong><?= $this->data["jos_emundus_recherche___acteur_public_yesno"]; ?>
                <br>
                <strong><?= JText::_('COM_EMUNDUS_FABRIK_EQUIPE_ACTEUR_PUB_TYPE'); ?></strong><?= $this->data["jos_emundus_recherche___acteur_public_type_raw"]; ?>
                <?php if ($this->data["jos_emundus_recherche___acteur_public_yesno_raw"] == 0) :?>
                    <br>
                    <strong><?= JText::_('COM_EMUNDUS_FABRIK_EQUIPE_ACTEUR_PUB_NAME'); ?></strong><?= $this->data["jos_emundus_recherche___acteur_public_nom_de_structure_raw"]; ?>
                <?php endif; ?>
                <br>
            <?php endif; ?>
        </div>

        <?php if (!empty($files)) : ?>
            <div class="profil-container">
                <h3 class="heading no-dash"><?= JText::_('COM_EMUNDUS_FABRIK_ATTACHED_FILES'); ?></h3>
                <div class="underline-small"></div>

                <p class="paragraph-infos">
                    <?php foreach ($files as $file) :?>
                        <a class="linkpj" target="_blank" href="<?= JURI::root().'images'.DS.'emundus'.DS.'files'.DS.$this->data['jos_emundus_campaign_candidature___applicant_id_raw'].DS.$file['filename']; ?>"><?= $file['value']; ?></a>
                        <br>
                        <br>
                    <?php endforeach; ?>
                </p>
            </div>
        <?php endif; ?>

        <?php
        // Log the action of opening the persons form.
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
        EmundusModelLogs::log($user->id, $author->id, $fnum, 33, 'r', 'COM_EMUNDUS_LOGS_OPEN_OFFER');

        if ((isset($this->data['Status']) && ($this->data['Status'][0] == 2 || $this->data['Status'][0] == 5)) || (isset($this->data['jos_emundus_campaign_candidature___status']) && ($this->data['jos_emundus_campaign_candidature___status'][0] == 2 || $this->data['jos_emundus_campaign_candidature___status'][0] == 5))) {
            $status = 2;
        } else {
            $status = 1;
        }

        if ($status === 2) :?>
            <!-- Contact button: offer cancelled. -->
            <a href="#" class="link-block-copy w-inline-block" data-ix="open-box" disabled>
                <div class="cta-end disabled">
                    <h3 class="entrer-en-contact no-dash"><?= JText::_('COM_EMUNDUS_CIFRE_CANCELED_BUTTON'); ?></h3>
                </div>
            </a>
        <?php elseif ($this->data['jos_emundus_campaign_candidature___applicant_id'][0] == JFactory::getUser()->id) : ?>

            <?php if ((isset($d['Status']) && $d['Status'] == 3) || (isset($d['jos_emundus_campaign_candidature___status']) && $d['jos_emundus_campaign_candidature___status'] == 3)) : ?>
                <!-- Contact button: message sent, awaiting accpetation -->
                <a href="#" class="link-block-copy w-inline-block" data-ix="open-box" disabled>
                    <div class="cta-end disabled">
                        <h3 class="entrer-en-contact no-dash"><?= JText::_('COM_EMUNDUS_CIFRE_WAITING_BUTTON'); ?></h3>
                    </div>
                </a>
            <?php else : ?>
                <!-- Contact button: this is your own offer. -->
                <a href="#" class="link-block-copy w-inline-block" data-ix="open-box" disabled>
                    <div class="cta-end disabled">
                        <h3 class="entrer-en-contact no-dash"><?= JText::_('COM_EMUNDUS_CIFRE_OWN_BUTTON'); ?></h3>
                    </div>
                </a>
            <?php endif; ?>

    <?php else : ?>

        <?php
            // Action button types:
            // // NO BUTTON : if the offer belongs to the user.
            // // ENTREZ EN CONTACT : If the user has not already contacted.
            // // REPONDRE : If the user has already been contacted for this offer but has not answered.
            // // RELANCE : If the user has contacted but not been answered yet.
            // // BREAK UP : If the user is collaborating with the other.
            require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'controllers'.DS.'cifre.php');
            $c_ciffe = new EmundusControllerCifre();
            $action_button = $c_ciffe->getActionButton($fnum);
        ?>

        <!-- Button used for matching with the offer -->
        <span class="alert alert-danger hidden" id="em-action-text"></span>
        <div id="em-search-item-action-button">
            <?php if ($action_button == 'contact') : ?>

                <?php $offers = $c_ciffe->getOwnOffers($fnum); ?>
                <a href="#" class="link-block-copy w-inline-block" disabled data-toggle="modal" data-target="#contactModal">
                    <div class="cta-end">
                        <h3 class="entrer-en-contact no-dash"><?= JText::_('COM_EMUNDUS_CIFRE_CONTACT_BUTTON'); ?></h3>
                    </div>
                </a>

                <div class="modal fade" id="contactModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="headertop">
                                <h3 class="entrer-en-contact no-dash"><?= JText::_('COM_EMUNDUS_CIFRE_CONTACT'); ?></h3>
                            </div>
                            <div class="div-block-4">
                                <?php if ($user->profile == '1006') :?>
                                    <p class="paragraph-demande-de-contact"><?= JText::_('COM_EMUNDUS_CIFRE_FUTUR_DOC_FIRST_QUESTION'); ?></p>
                                    <textarea id="em-contact-message" class="text-field-bigger-margintop w-input" placeholder="Texte (2000 caractères)" maxlength="2000"></textarea>
                                    <p class="paragraph-demande-de-contact"><?= JText::_('COM_EMUNDUS_CIFRE_FUTUR_DOC_SECOND_QUESTION'); ?></p>
                                    <textarea id="em-contact-motivation" class="text-field-bigger-margintop w-input" placeholder="Texte (2000 caractères)" maxlength="2000"></textarea>
                                <?php else :?>
                                    <p class="paragraph-demande-de-contact"><?= JText::_('COM_EMUNDUS_CIFRE_QUESTION'); ?></p>
                                    <textarea id="em-contact-message" class="text-field-bigger-margintop w-input" placeholder="Texte (3000 caractères)" maxlength="3000"></textarea>
                                <?php endif; ?>

                                <?php if (!empty($offers)) :?>
                                    <p class="paragraph-demande-de-contact"><?= JText::_('COM_EMUNDUS_CIFRE_JOIN_CIFRE'); ?></p>
                                    <select id="em-join-offer" class="text-field w-select">
                                        <option value=""><?= JText::_('COM_EMUNDUS_CIFRE_NO_JOIN_CIFRE'); ?></option>
                                        <?php foreach ($offers as $offer) : ?>
                                            <option value="<?= $offer->fnum; ?>"><?= $offer->titre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                                <hr>
                                <!-- Upload a file from computer -->
                                <div id="em-attachment-list">
                                    <?php if ($user->profile == '1006') : ?>
                                        <div id="cv-upload_file">
                                            <p class="paragraph-demande-de-contact">
                                                <strong><?= JText::_('COM_EMUNDUS_CIFRE_ADD_CV'); ?></strong>
                                            </p>
                                            <div class="w-clearfix">
                                                <label for="em-cv_to_upload" accept="application/pdf" id="em-cv_to_upload_label" class="ajouter">
                                                    <input type="file" id="em-cv_to_upload" onchange="cvAddFile()">
                                                </label>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Upload a file from computer -->
                                    <div id="doc-upload_file">
                                        <p class="paragraph-demande-de-contact">
                                            <strong><?= JText::_('COM_EMUNDUS_CIFRE_ADD_FILE'); ?></strong>
                                            <?= JText::_('COM_EMUNDUS_CIFRE_SELECT_FILE'); ?>
                                        </p>
                                        <div class="w-clearfix">
                                            <label for="em-doc_to_upload" id="em-doc_to_upload_label" class="ajouter">
                                                <input type="file" id="em-doc_to_upload" onchange="docAddFile();">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="div-block-5">
                                    <a href="#" class="button w-button" data-dismiss="modal" onclick="actionButton('contact')">
                                        <?= JText::_('COM_EMUNDUS_CIFRE_SEND_CONTACT'); ?>
                                    </a>
                                    <a href="#" class="cancel" data-dismiss="modal">
                                        <?= JText::_('CANCEL'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($action_button == 'reply') :?>
                <a href="#" class="link-block-copy w-inline-block" data-ix="open-box" disabled onclick="actionButton('reply')">
                    <div class="cta-end disabled">
                        <h3 class="entrer-en-contact no-dash"><?= JText::_('COM_EMUNDUS_CIFRE_ANSWER'); ?></h3>
                    </div>
                </a>
                <br>
                <a href="#" class="link-block-copy w-inline-block" data-ix="open-box" disabled onclick="breakUp('ignore')">
                    <div class="cta-end disabled">
                        <h3 class="entrer-en-contact no-dash"><?= JText::_('COM_EMUNDUS_CIFRE_IGNORE'); ?></h3>
                    </div>
                </a>
            <?php elseif ($action_button == 'retry') :?>
                <a href="#" class="link-block-copy w-inline-block" data-ix="open-box" disabled onclick="actionButton('retry')">
                    <div class="cta-end disabled">
                        <h3 class="entrer-en-contact no-dash"><?= JText::_('COM_EMUNDUS_CIFRE_RECALL'); ?></h3>
                    </div>
                </a>
                <br>
                <br>
                <a href="#" class="link-block-copy w-inline-block" data-ix="open-box" disabled onclick="breakUp('cancel')">
                    <div class="cta-end disabled">
                        <h3 class="entrer-en-contact no-dash"><?= JText::_('COM_EMUNDUS_CIFRE_CANCEL'); ?></h3>
                    </div>
                </a>
            <?php elseif ($action_button == 'breakup') :?>
                <?php $offers = $c_ciffe->getOwnOffers($fnum); ?>
                <a href="#" class="link-block-copy w-inline-block" data-ix="open-box" disabled onclick="breakUp('breakup')">
                    <div class="cta-end disabled">
                        <h3 class="entrer-en-contact no-dash"><?= JText::_('COM_EMUNDUS_CIFRE_CUT_CONTACT'); ?></h3>
                    </div>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="link-2">
        <?= $this->loadTemplate('buttons'); ?>
    </div>
</div>

<div class="em-modal-sending-emails" id="em-modal-sending-emails">
    <div id="em-sending-email-caption"><?= JText::_('COM_EMUNDUS_CIFRE_SENDING'); ?></div>
    <img class="em-sending-email-img" id="em-sending-email-img" src="/images/emundus/sending-email.gif">
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script>
    function actionButton(action) {

        const fnum = '<?= $fnum; ?>';
        let data = {
            fnum: fnum
        };

        if (action === 'contact') {

            jQuery('#em-modal-sending-emails').css('display', 'block');
            if (document.getElementById('em-join-offer') != null) {
                // Get the offer selected from the dropdown by the user.
                const linkedOffer = document.getElementById('em-join-offer').value;
                if (linkedOffer != null && linkedOffer != '' && typeof linkedOffer != 'undefined') {
                    data.linkedOffer = linkedOffer;
                }
            }

            // Get the attached message if there is one.
            const message = document.getElementById('em-contact-message').value;
            if (message != null && message != '' && typeof message != 'undefined') {
                data.message = message;
            }

            // Get the attached message if there is one.
            if (document.getElementById('em-contact-motivation')) {
                const motivation = document.getElementById('em-contact-motivation').value;
                if (motivation != null && motivation != '' && typeof motivation != 'undefined') {
                    data.motivation = motivation;
                }
            }

            const CV = jQuery('#cv-upload_file').find('.hidden').text();
            if (CV != null && CV != '' && typeof CV != 'undefined') {
                data.CV = CV;
            }

            const DOC = jQuery('#doc-upload_file').find('.hidden').text();
            if (DOC != null && DOC != '' && typeof DOC != 'undefined') {
                data.DOC = DOC;
            }
        }

        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?option=com_emundus&controller=cifre&task=' + action,
            data: data,
            beforeSend: function () {
                jQuery('#em-search-item-action-button').html('<button type="button" class="btn btn-default" disabled> ... </button>');

                if (action === 'contact') {
                    jQuery('#contactModal').modal('hide');
                    jQuery('body').removeClass('modal-open');
                    jQuery('.modal-backdrop').remove();
                }

            },
            success: function (result) {
                jQuery('#em-modal-sending-emails').css('display', 'none');
                if (result.status) {

                    // When we successfully change the status, we simply dynamically change the button.
                    if (action === 'contact') {
                        Swal.fire(
                            '<?= JText::_("COM_EMUNDUS_CIFRE_SENT"); ?>',
                            '',
                            'success'
                        ).then(() => {
                            window.location = '<?= JUri::base(); ?>espace-personnel'
                        })
                    } else if (action === 'retry') {
                        jQuery('#em-search-item-action-button').html('' +
                            '<a href="#" class="link-block-copy w-inline-block" data-ix="open-box" disabled>\n'+
                                '<div class="cta-end disabled">\n'+
                                    '<h3 class="entrer-en-contact no-dash"><?= JText::_("COM_EMUNDUS_CIFRE_SENT"); ?></h3>\n'+
                                '</div>\n'+
                            '</a>');
                    } else if (action === 'reply') {
                        jQuery('#em-search-item-action-button').html('' +
                            '<a href="#" class="link-block-copy w-inline-block" data-ix="open-box" disabled onclick="breakUp(\'breakup\')">\n'+
                                '<div class="cta-end disabled">\n'+
                                    '<h3 class="entrer-en-contact no-dash"><?= JText::_('COM_EMUNDUS_CIFRE_CUT_CONTACT'); ?></h3>\n'+
                                '</div>\n'+
                            '</a>');
                    }

                } else {
                    let actionText = document.getElementById('em-action-text');
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
        const data = {
            fnum: '<?= $fnum; ?>'
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
                    jQuery('#em-search-item-action-button').html(''+
                        '        <a href="#" class="link-block-copy w-inline-block" disabled data-toggle="modal" data-target="#contactModal">\n' +
                        '                <div class="cta-end">\n' +
                        '                    <h3 class="entrer-en-contact no-dash"><?= JText::_('COM_EMUNDUS_CIFRE_CONTACT_BUTTON'); ?></h3>\n'+
					'                </div>\n'+
					'            </a>\n'+
					'\n'+
					'            <div class="modal fade" id="contactModal" tabindex="-1" role="dialog">\n'+
					'                <div class="modal-dialog" role="document">\n'+
					'                    <div class="modal-content">\n'+
					'                        <div class="headertop">\n'+
					'                            <h3 class="entrer-en-contact no-dash"><?= JText::_('COM_EMUNDUS_CIFRE_CONTACT'); ?></h3>\n'+
					'                        </div>\n'+
					'                        <div class="div-block-4">\n'+
					'                        <?php if ($user->profile == '1006') :?>\n'+
					'                            <p class="paragraph-demande-de-contact"><?= JText::_('COM_EMUNDUS_CIFRE_FUTUR_DOC_FIRST_QUESTION'); ?></p>\n'+
					'                            <textarea id="em-contact-message" class="text-field-bigger-margintop w-input" placeholder="Texte (2000 caractères)" maxlength="2000"></textarea>\n'+
					'                            <p class="paragraph-demande-de-contact"><?= JText::_('COM_EMUNDUS_CIFRE_FUTUR_DOC_SECOND_QUESTION'); ?></p>\n'+
					'                            <textarea id="em-contact-motivation" class="text-field-bigger-margintop w-input" placeholder="Texte (2000 caractères)" maxlength="2000"></textarea>\n'+
					'                        <?php else :?>\n'+
					'                            <p class="paragraph-demande-de-contact"><?= JText::_('COM_EMUNDUS_CIFRE_QUESTION'); ?></p>\n'+
					'                            <textarea id="em-contact-message" class="text-field-bigger-margintop w-input" placeholder="Texte (3000 caractères)" maxlength="3000"></textarea>\n'+
					'                        <?php endif; ?>\n'+
					'\n'+
					'                        <?php if (!empty($offers)) :?>\n'+
					'                            <p class="paragraph-demande-de-contact"><?= JText::_('COM_EMUNDUS_CIFRE_JOIN_CIFRE'); ?></p>\n'+
					'                            <select id="em-join-offer" class="text-field w-select">\n'+
					'                                <option value=""><?= JText::_('COM_EMUNDUS_CIFRE_NO_JOIN_CIFRE'); ?></option>\n'+
					'                                <?php foreach ($offers as $offer) : ?>\n'+
					'                                    <option value="<?= $offer->fnum; ?>"><?= addslashes($offer->titre); ?></option>\n'+
					'                                <?php endforeach; ?>\n'+
					'                            </select>\n'+
					'                        <?php endif; ?>\n'+
					'                        <hr>\n'+
					'                        <div id="em-attachment-list">\n'+
					'                            <?php if ($user->profile == '1006') : ?>\n'+
					'                                <div id="cv-upload_file">\n'+
					'                                    <p class="paragraph-demande-de-contact">\n'+
					'                                        <strong><?= JText::_('COM_EMUNDUS_CIFRE_ADD_CV'); ?></strong>\n'+
					'                                    </p>\n'+
					'                                    <div class="w-clearfix">\n'+
					'                                        <label for="em-cv_to_upload" accept="application/pdf" id="em-cv_to_upload_label" class="ajouter">\n'+
					'                                            <input type="file" id="em-cv_to_upload" onchange="cvAddFile()">\n'+
					'                                        </label>\n'+
					'                                    </div>\n'+
					'                                </div>\n'+
					'                            <?php endif; ?>\n'+
					'\n'+
					'                            <div id="doc-upload_file">\n'+
					'                                <p class="paragraph-demande-de-contact">\n'+
					'                                    <strong><?= JText::_('COM_EMUNDUS_CIFRE_ADD_FILE'); ?></strong>\n'+
					'                                    <?= JText::_('COM_EMUNDUS_CIFRE_SELECT_FILE'); ?>\n'+
					'                                </p>\n'+
					'                                <div class="w-clearfix">\n'+
					'                                    <label for="em-doc_to_upload" id="em-doc_to_upload_label" class="ajouter">\n'+
					'                                        <input type="file" id="em-doc_to_upload" onchange="docAddFile();">\n'+
					'                                    </label>\n'+
					'                                </div>\n'+
					'                            </div>\n'+
					'                        </div>\n'+
					'                        <div class="div-block-5">\n'+
					'                            <a href="#" class="button w-button" data-dismiss="modal" onclick="actionButton(\'contact\')">\n'+
					'                                <?= JText::_('COM_EMUNDUS_CIFRE_SEND_CONTACT'); ?>\n'+
					'                            </a>\n'+
					'                            <a href="#" class="cancel" data-dismiss="modal">\n'+
					'                                <?= JText::_('CANCEL'); ?>\n'+
					'                            </a>\n'+
					'                        </div>\n'+
					'                    </div>\n'+
					'                </div>\n'+
					'            </div>');

                } else {
                    let actionText = document.getElementById('em-action-text');
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
    }

    // Add file to the list being attached.
    function docAddFile() {
        // We need to get the file uploaded by the user.
        var doc = jQuery("#em-doc_to_upload")[0].files[0];
        var docId = jQuery("#doc-upload_file");
        var uploaddoc = new Upload(doc, docId);

        // Verification of style size and type can be done here.
        uploaddoc.doUpload();
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
                title: '<?= JText::_("COM_EMUNDUS_CIFRE_ALERT_PDF"); ?>'
            })
            return false;
        }

        // add assoc key values, this will be posts values
        formData.append("file", this.file, this.getName());
        formData.append("upload_file", true);
        formData.append('filetype', 'pdf');
        formData.append('user', <?= $user->id; ?>);
        formData.append('fnum', '<?= $fnum; ?>');

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
                    jQuery(that.id).append(data.file_name+'<span onClick="removeAttachment(this);" style="cursor: pointer;">&nbsp;<i class="fa fa-times"></i></span><div class="value hidden">' + data.file_path + '</div>');
                } else {
                    jQuery(that.id).append('<span class="alert"><?= JText::_('UPLOAD_FAILED'); ?> </span>')
                }

            },
            error: function (error) {
                // handle error
                this.id.append('<span class="alert"> <?= JText::_('UPLOAD_FAILED'); ?> </span>')
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
        jQuery(element).parent().remove();
    }
</script>

<?php endif;

echo $this->pluginbottom;
echo $this->loadTemplate('actions');
echo '</div>';
echo $form->outro;
echo $this->pluginend;