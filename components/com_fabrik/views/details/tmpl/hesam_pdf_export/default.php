<?php
/**
 * Bootstrap Details Template
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */
// PDF EXPORT
// No direct access
defined('_JEXEC') or die('Restricted access');

$form = $this->form;
$model = $this->getModel();

$lang = JFactory::getLanguage();
$extension = 'com_emundus';
$base_dir = JPATH_SITE . '/components/com_emundus';
$language_tag = "fr-FR";
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);

$fnum = $this->data["jos_emundus_recherche___fnum_raw"];
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'users.php');
$m_users = new EmundusModelUsers();

require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'cifre.php');
$m_cifre = new EmundusModelCifre();


if ($this->params->get('show_page_heading', 1)) : ?>
    <div class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>">
        <?php echo $this->escape($this->params->get('page_heading')); ?>
    </div>
<?php
endif;
?>

<?php
echo $form->intro;
if ($this->isMambot) :
    echo '<div class="fabrikForm fabrikDetails fabrikIsMambot" id="' . $form->formid . '">';
else :
    echo '<div class="fabrikForm fabrikDetails" id="' . $form->formid . '">';
endif;
echo $this->plugintop;
echo $this->loadTemplate('buttons');
echo $this->loadTemplate('relateddata');

$regions = $this->data['data_regions___name_raw'];

$db = JFactory::getDBO();
$user = JFactory::getSession()->get('emundusUser');

$query = $db->getquery('true');
$user_to = $m_users->getUserById($this->data["jos_emundus_campaign_candidature___applicant_id_raw"][0]);


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



function getDepartment($dept) {
    $db = JFactory::getDBO();

    $query = $db->getquery('true');

    $query->select($db->quoteName('departement_nom'))
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


// GET the acteur public Regions
function getActeurRegions($fnum) {
    $db = JFactory::getDBO();

    $query = $db->getquery('true');

    $query
        ->select($db->quoteName('dr.name'))
        ->from($db->quoteName('#__emundus_recherche', 'er'))
        ->leftJoin($db->quoteName('#__emundus_recherche_744_repeat', 'err'). ' ON '.$db->quoteName('err.parent_id') . ' = ' . $db->quoteName('er.id'))
        ->leftJoin($db->quoteName('data_regions', 'dr'). ' ON '.$db->quoteName('dr.id') . ' = ' . $db->quoteName('err.region'))
        ->where($db->quoteName('er.fnum') . ' LIKE "' . $fnum . '"');

    $db->setQuery($query);
    try {

        return $db->loadAssocList();

    } catch (Exception $e) {
        echo "<pre>";
        var_dump($query->__toString());
        echo "</pre>";
        die();
    }
}

//GET the acteur public Departments
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

        return $db->loadAssocList();

    } catch (Exception $e) {
        echo "<pre>";
        var_dump($query->__toString());
        echo "</pre>";
        die();
    }
}
?>


<div class="em-pdf-group">
    <img src="images/custom/Hesam/Logo_1000doctorants.JPG" alt="Logo 1000doctorants" style="vertical-align: top;"
         width="252" height="90">
    <div class="em-pdf-title-div">
        <h3><?php echo JText::_('COM_EMUNDUS_FABRIK_RECAP'); ?><a href="<?php echo JURI::root(); ?>"><?php echo JURI::root(); ?></a></h3>
    </div>

    <?php if($user->id == $user_to[0]->user_id) :?>

        <div class="em-pdf-element">

            <div class="em-pdf-element-label">
                <p><?php echo JText::_('COM_EMUNDUS_FABRIK_DOSSIER'); ?></p>
            </div>

            <div class="em-pdf-element-value">
                <p><?php echo $this->data["jos_emundus_campaign_candidature___fnum_raw"][0]; ?></p>
            </div>

        </div>
    <?php endif; ?>

    <div class="em-pdf-element">

        <div class="em-pdf-element-label">
            <p><?php echo JText::_('COM_EMUNDUS_FABRIK_DOSSIER'); ?></p>
        </div>

        <div class="em-pdf-element-value">
            <p><?php echo date("d/m/Y", strtotime($this->data["jos_emundus_campaign_candidature___date_submitted_raw"][0])); ?></p>
        </div>

    </div>
</div>


<div class="em-pdf-group">
    <div class="em-pdf-title-div">
        <h3><?php echo JText::_('COM_EMUNDUS_FABRIK_AUTHOR'); ?></h3>
    </div>

    <div class="em-pdf-element">

        <div class="em-pdf-element-label">
            <p><?php echo JText::_('COM_EMUNDUS_FABRIK_AUTHOR_TYPE'); ?></p>
        </div>

        <div class="em-pdf-element-value">
            <p><?php echo $this->data["jos_emundus_setup_profiles___label_raw"][0]; ?></p>
        </div>

    </div>


    <?php if ($this->data["jos_emundus_setup_profiles___id_raw"][0] != '1008') : ?>
        <?php if($user->id == $user_to[0]->user_id) :?>
            <div class="em-pdf-element">

                <div class="em-pdf-element-label">
                    <p><?php echo JText::_('COM_EMUNDUS_FABRIK_AUTHOR_CIVILITY'); ?></p>
                </div>

                <div class="em-pdf-element-value">
                    <p><?php echo ($user_to[0]->gender == "M") ? "Monsieur" : "Madame"; ?></p>
                </div>

            </div>

            <div class="em-pdf-element">

                <div class="em-pdf-element-label">
                    <p><?php echo JText::_('LAST_NAME'); ?></p>
                </div>

                <div class="em-pdf-element-value">
                    <p><?php echo ucfirst($user_to[0]->lastname); ?></p>
                </div>

            </div>

            <div class="em-pdf-element">

                <div class="em-pdf-element-label">
                    <p><?php echo JText::_('FIRST_NAME'); ?></p>
                </div>

                <div class="em-pdf-element-value">
                    <p><?php echo ucfirst($user_to[0]->firstname); ?></p>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>

        <?php $institution = $m_cifre->getUserInstitution($user_to[0]->user_id);?>
        <div class="em-pdf-element">

            <div class="em-pdf-element-label">
                <p><?php echo JText::_('COM_EMUNDUS_FABRIK_INSTITUTE_NAME'); ?></p>
            </div>

            <div class="em-pdf-element-value">
                <p><?php echo $institution->nom_de_structure; ?></p>
            </div>

        </div>
    <?php endif; ?>

    <?php if($user->id == $user_to[0]->user_id) :?>
        <div class="em-pdf-element">

            <div class="em-pdf-element-label">
                <p><?php echo JText::_('EMAIL_FORM'); ?></p>
            </div>

            <div class="em-pdf-element-value">
                <p><a href="mailto:<?php echo $user_to[0]->email; ?>"><?php echo $user_to[0]->email; ?></a></p>
            </div>

        </div>

        <?php if (!empty($this->data['jos_emundus_projet___contact_tel_raw'][0])) : ?>
            <div class="em-pdf-element">

                <div class="em-pdf-element-label">
                    <p><?php echo JText::_('TELEPHONENUMBER'); ?></p>
                </div>
                <div class="em-pdf-element-value">
                    <p><?php echo $this->data['jos_emundus_projet___contact_tel_raw'][0]; ?></p>
                </div>

            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($this->data["jos_emundus_setup_profiles___id_raw"][0] == '1007') : ?>

        <?php $laboratoire = $m_cifre->getUserLaboratory($author->id);
            if (!empty($laboratoire)) :?>
                <div class="em-pdf-element">

                    <div class="em-pdf-element-label">
                        <p><?php echo JText::_('COM_EMUNDUS_FABRIK_AUTHOR_RESEARCH_UNIT'); ?></p>
                    </div>

                    <div class="em-pdf-element-value">
                        <p><?php echo $laboratoire->name; ?></p>
                    </div>

                </div>
        <?php endif; ?>

        <?php $ecoleDoctorale = $m_cifre->getDoctorale($laboratoire->id); ?>

        <?php if (!empty($ecoleDoctorale)) :?>
            <div class="em-pdf-element">

                <div class="em-pdf-element-label">
                    <p><?php echo JText::_('COM_EMUNDUS_FABRIK_AUTHOR_SCHOOL'); ?></p>
                </div>

                <div class="em-pdf-element-value">
                    <p><?php echo $ecoleDoctorale; ?></p>
                </div>

            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($this->data['jos_emundus_projet___limit_date_raw'][0])) : ?>
        <div class="em-pdf-element">

            <div class="em-pdf-element-label">
                <p><?php echo JText::_('COM_EMUNDUS_FABRIK_DISPO_DATE'); ?></p>
            </div>

            <div class="em-pdf-element-value">
                <p><?php echo $this->data["jos_emundus_projet___limit_date_raw"][0]; ?></p>
            </div>

        </div>
    <?php endif; ?>

</div>

<div class="em-pdf-group breaker">
    <div class="em-pdf-title-div">
        <h3><?php echo JText::_('COM_EMUNDUS_FABRIK_PROJECT_TITLE'); ?></h3>
    </div>

    <div class="em-pdf-element">

        <div class="em-pdf-element-label">
            <p><?php echo JText::_('COM_EMUNDUS_FABRIK_PROJECT_NAME'); ?></p>
        </div>

        <div class="em-pdf-element-value">
            <p><?php echo $this->data["jos_emundus_projet___titre_raw"][0]; ?></p>
        </div>

    </div>

    <?php if ($this->data["jos_emundus_setup_profiles___id_raw"][0] != '1008') :?>
        <?php if (!empty($this->data['jos_emundus_projet___contexte_raw'][0])) :?>
            <div class="em-pdf-element">

                <div class="em-pdf-element-label">
                    <p><?php echo JText::_('COM_EMUNDUS_FABRIK_ENJEU'); ?></p>
                </div>

                <div class="em-pdf-element-value">
                    <p><?php echo $this->data["jos_emundus_projet___contexte_raw"][0]; ?></p>
                </div>

            </div>
        <?php endif; ?>
    <?php else :?>

        <?php if (!empty($this->data['jos_emundus_projet___contexte_raw'][0])) :?>
            <div class="em-pdf-element">

                <div class="em-pdf-element-label">
                    <p><?php echo JText::_('COM_EMUNDUS_FABRIK_TERRITOIRE'); ?></p>
                </div>

                <div class="em-pdf-element-value">
                    <p><?php echo $this->data["jos_emundus_projet___contexte_raw"][0]; ?></p>
                </div>

            </div>
        <?php endif; ?>
    <?php endif; ?>


    <?php if (!empty($this->data['jos_emundus_projet___question_raw'][0])) :?>
        <?php
            if ($this->data["jos_emundus_setup_profiles___id_raw"][0] == '1006')
                $questionText = JText::_('COM_EMUNDUS_FABRIK_PROBLEMATIQUE_FUTURE_DOC');
            elseif ($this->data["jos_emundus_setup_profiles___id_raw"][0] == '1007')
                $questionText = JText::_('COM_EMUNDUS_FABRIK_PROBLEMATIQUE_CHERCHEUR');
            elseif ($this->data["jos_emundus_setup_profiles___id_raw"][0] == '1008')
                $questionText = JText::_('COM_EMUNDUS_FABRIK_GRAND_DEFI');
        ?>
        <div class="em-pdf-element">

            <div class="em-pdf-element-label">
                <p><?php echo $questionText; ?></p>
            </div>

            <div class="em-pdf-element-value">
                <p><?php echo $this->data["jos_emundus_projet___question_raw"][0]; ?></p>
            </div>

        </div>
    <?php endif; ?>


    <?php if (!empty($this->data['jos_emundus_projet___methodologie_raw'][0])) : ?>
        <div class="em-pdf-element">

            <div class="em-pdf-element-label">
                <p><?php echo JText::_('COM_EMUNDUS_FABRIK_METHODOLOGIE'); ?></p>
            </div>

            <div class="em-pdf-element-value">
                <p><?php echo $this->data["jos_emundus_projet___methodologie_raw"][0]; ?></p>
            </div>

        </div>

    <?php endif; ?>



    <?php if (!empty($this->data['data_thematics___thematic_raw'][0])) : ?>

        <div class="em-pdf-element">

            <div class="em-pdf-element-label">
                <p><?php echo JText::_('COM_EMUNDUS_FABRIK_THEMES'); ?></p>
            </div>

            <div class="em-pdf-element-value">
                <p><?php echo implode(", ", $this->data["data_thematics___thematic_raw"]); ?></p>
            </div>

        </div>

    <?php endif; ?>

    <?php if (!empty($this->data["em_discipline___disciplines_raw"])) : ?>
        <div class="em-pdf-element">

            <div class="em-pdf-element-label">
                <p><?php echo JText::_('COM_EMUNDUS_FABRIK_DISCIPLINES'); ?></p>
            </div>

            <div class="em-pdf-element-value">
                <p><?php echo is_array($this->data["em_discipline___disciplines_raw"]) ? implode(', ', $this->data["em_discipline___disciplines_raw"]) : $this->data["em_discipline___disciplines_raw"]; ?></p>
            </div>

        </div>
    <?php endif; ?>


    <div class="em-pdf-element">

        <div class="em-pdf-element-label">
            <p><?php echo JText::_('COM_EMUNDUS_FABRIK_REGIONS'); ?></p>
        </div>

        <div class="em-pdf-element-value">
            <p>
                <?php 
                    if ($this->data["jos_emundus_setup_profiles___id_raw"][0] != '1008') {
                        echo !empty($regions) ? implode(', ', array_unique($regions)) : JText::_('COM_EMUNDUS_FABRIK_NO_REGIONS');
                    }
                    else {
                        $regions = getActeurRegions($fnum);
                        echo !empty($regions) ? implode(', ', array_unique(array_column(getActeurRegions($fnum), 'name'))) : JText::_('COM_EMUNDUS_FABRIK_NO_REGIONS');
                    }
                        
                ?>
            </p>
        </div>

    </div>

    <div class="em-pdf-element">

        <div class="em-pdf-element-label">
            <p><?php echo JText::_('COM_EMUNDUS_FABRIK_DEPARTMENTS'); ?></p>
        </div>

        <div class="em-pdf-element-value">
            <p><?php
                if ($this->data["jos_emundus_setup_profiles___id_raw"][0] != '1008') {
                    if (!empty($this->data["jos_emundus_recherche_630_repeat_repeat_department___department"])) {
                        $departmentArray = array();
                        foreach ($this->data["jos_emundus_recherche_630_repeat_repeat_department___department"] as $dep) {
                            $departmentArray[] = getDepartment($dep);
                        }
                        echo implode(', ', array_unique($departmentArray));
                    }
                    else {
                        echo JText::_('COM_EMUNDUS_FABRIK_NO_DEPARTMENTS');
                    }
                }
                else {
                    if (!empty(getActeurDepartments($fnum))) {
                        echo implode(', ', array_unique(array_column(getActeurDepartments($fnum), 'departement_nom')));
                    }
                    else {
                        echo JText::_('COM_EMUNDUS_FABRIK_NO_DEPARTMENTS');
                    }
                }

                ?>
            </p>
        </div>

    </div>

</div>

<div class="em-pdf-group breaker">

    <div class="em-pdf-title-div">
        <h3><?php echo JText::_('COM_EMUNDUS_FABRIK_PARTENAIRES'); ?></h3>
    </div>

    <?php if ($this->data["jos_emundus_setup_profiles___id_raw"][0] != '1006') : ?>

        <div class="em-pdf-element">

            <div class="em-pdf-element-label">
                <p><?php echo JText::_('COM_EMUNDUS_FABRIK_FUTUR_DOC'); ?></p>
            </div>

            <div class="em-pdf-element-value">
                <p><?php echo $this->data["jos_emundus_recherche___futur_doctorant_yesno"]; ?></p>
            </div>

        </div>


        <?php if ($this->data["jos_emundus_recherche___futur_doctorant_yesno"] == 0 && !empty($this->data["jos_emundus_recherche___futur_doctorant_nom_raw"]) && !empty($this->data["jos_emundus_recherche___futur_doctorant_prenom_raw"])) : ?>
            <?php if($this->data["jos_emundus_setup_profiles___id_raw"][0] == '1008' && $user->id == $user_to[0]->user_id) :?>
            <div class="em-pdf-element">

                <div class="em-pdf-element-label">
                    <p><?php echo JText::_('COM_EMUNDUS_FABRIK_FUTUR_DOC_NAME'); ?></p>
                </div>

                <div class="em-pdf-element-value">
                    <p><?php echo strtoupper($this->data["jos_emundus_recherche___futur_doctorant_nom"]) . " " . $this->data["jos_emundus_recherche___futur_doctorant_prenom"]; ?></p>
                </div>

            </div>
            <?php endif; ?>
        <?php endif; ?>


    <?php endif; ?>

    <?php if ($this->data["jos_emundus_setup_profiles___id_raw"][0] != '1007') : ?>
        <div class="em-pdf-element">

            <div class="em-pdf-element-label">
                <p><?php echo JText::_('COM_EMUNDUS_FABRIK_EQUIPE_RECHERCHE'); ?></p>
            </div>

            <div class="em-pdf-element-value">
                <p><?php echo $this->data["jos_emundus_recherche___equipe_de_recherche_direction_yesno"]; ?></p>
            </div>

        </div>

        <?php if ($this->data["jos_emundus_recherche___equipe_de_recherche_direction_yesno"] == 0 && !empty($this->data["jos_emundus_recherche___equipe_direction_equipe_de_recherche_raw"])) : ?>
            <?php if($this->data["jos_emundus_setup_profiles___id_raw"][0] == '1008' && $user->id == $user_to[0]->user_id) :?>
                <div class="em-pdf-element">

                    <div class="em-pdf-element-label">
                        <p><?php echo JText::_('COM_EMUNDUS_FABRIK_EQUIPE_RECHERCHE_NAME'); ?></p>
                    </div>

                    <div class="em-pdf-element-value">
                        <p><?php echo $this->data["jos_emundus_recherche___equipe_direction_equipe_de_recherche_raw"]; ?></p>
                    </div>

                </div>
            <?php endif; ?>
        <?php endif; ?>

    <?php endif; ?>



    <?php if ($this->data["jos_emundus_setup_profiles___id_raw"][0] != '1008') : ?>
        <div class="em-pdf-element">

            <div class="em-pdf-element-label">
                <p><?php echo JText::_('COM_EMUNDUS_FABRIK_EQUIPE_ACTEUR_PUB'); ?></p>
            </div>

            <div class="em-pdf-element-value">
                <p><?php echo $this->data["jos_emundus_recherche___acteur_public_yesno"]; ?></p>
            </div>

        </div>

        <?php if ($this->data["jos_emundus_recherche___acteur_public_yesno_raw"] == 0 && $user->id == $user_to[0]->user_id) : ?>

            <?php if (!empty($this->data["jos_emundus_recherche___acteur_public_type_raw"])) : ?>
                <div class="em-pdf-element">

                    <div class="em-pdf-element-label">
                        <p><?php echo JText::_('COM_EMUNDUS_FABRIK_EQUIPE_ACTEUR_PUB_TYPE'); ?></p>
                    </div>

                    <div class="em-pdf-element-value">
                        <p><?php echo $this->data["jos_emundus_recherche___acteur_public_type_raw"]; ?></p>
                    </div>

                </div>
            <?php endif; ?>

            <?php if (!empty($this->data["jos_emundus_recherche___acteur_public_nom_de_structure_raw"])) : ?>

                <div class="em-pdf-element">

                    <div class="em-pdf-element-label">
                        <p><?php echo JText::_('COM_EMUNDUS_FABRIK_EQUIPE_ACTEUR_PUB_NAME'); ?></p>
                    </div>

                    <div class="em-pdf-element-value">
                        <p><?php echo $this->data["jos_emundus_recherche___acteur_public_nom_de_structure_raw"]; ?></p>
                    </div>

                </div>
            <?php endif; ?>

        <?php endif; ?>

    <?php endif; ?>
</div>

<div class="em-pdf-group breaker">
    <?php if (!empty($files)) : ?>
        <div class="em-pdf-title-div">
            <h3><?php echo JText::_('COM_EMUNDUS_FABRIK_ATTACHED_FILES'); ?></h3>
        </div>

        <?php foreach ($files as $file) : ?>
            <div class="em-pdf-element">

                <div class="em-pdf-element-label">
                    <p><?php echo $file["value"]; ?></p>
                </div>

                <div class="em-pdf-element-value">
                    <p>
                        <a target="_blank" href="<?php echo JURI::root().'images'.DS.'emundus'.DS.'files'.DS.$this->data["jos_emundus_campaign_candidature___applicant_id_raw"][0].DS.$this->data["jos_emundus_cifre_links___document_raw"]; ?>"><?php echo $file["filename"]; ?></a>
                    </p>
                </div>

            </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

<button onclick="window.history.back();" >Retour</button>