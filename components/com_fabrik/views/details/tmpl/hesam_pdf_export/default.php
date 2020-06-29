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
    <div class="componentheading<?= $this->params->get('pageclass_sfx') ?>">
        <?= $this->escape($this->params->get('page_heading')); ?>
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

$db = JFactory::getDBO();
$user = JFactory::getSession()->get('emundusUser');

$query = $db->getquery('true');
$user_to = $m_users->getUserById($this->data["jos_emundus_campaign_candidature___applicant_id_raw"]);


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
    } else {
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

?>
<table class="em-pdf-group">
    <tr>
        <td>
            <img src="images/custom/Hesam/Logo_1000doctorants.JPG" alt="Logo 1000doctorants" style="vertical-align: top;" width="252" height="90">
        </td>
    </tr>

    <tr class="em-pdf-title-div">
        <td colspan="2">
            <h3><?= JText::_('COM_EMUNDUS_FABRIK_RECAP'); ?><a href="<?= JURI::root(); ?>"><?= JURI::root(); ?></a></h3>
        </td>
    </tr>

    <?php if($user->id == $user_to[0]->user_id) :?>

        <tr class="em-pdf-element">

            <td class="em-pdf-element-label">
                <p><?= JText::_('COM_EMUNDUS_FABRIK_DOSSIER'); ?></p>
            </td>

            <td class="em-pdf-element-value">
                <p><?= $this->data["jos_emundus_campaign_candidature___fnum_raw"][0]; ?></p>
            </td>

        </tr>
    <?php endif; ?>

    <tr class="em-pdf-element">

        <td class="em-pdf-element-label">
            <p><?= JText::_('COM_EMUNDUS_FABRIK_DOSSIER_DATE'); ?></p>
        </td>

        <td class="em-pdf-element-value">
            <p><?= JFactory::getDate($this->data["jos_emundus_campaign_candidature___date_submitted_raw"])->format('d/m/Y'); ?></p>
        </td>

    </tr>

</table>


<table class="em-pdf-group">
    <tr class="em-pdf-title-div" width="700px">
        <td colspan="2">
            <h3><?= JText::_('COM_EMUNDUS_FABRIK_AUTHOR'); ?></h3>
        </td>
    </tr>

    <tr class="em-pdf-element">

        <td class="em-pdf-element-label">
            <p><?= JText::_('COM_EMUNDUS_FABRIK_AUTHOR_TYPE'); ?></p>
        </td>

        <td class="em-pdf-element-value">
            <p><?= $this->data["jos_emundus_setup_profiles___label_raw"][0]; ?></p>
        </td>

    </tr>

    <?php if ($this->data["jos_emundus_setup_profiles___id_raw"][0] != '1008') : ?>
        <?php if ($user->id == $user_to[0]->user_id) :?>
            <tr class="em-pdf-element">

                <td class="em-pdf-element-label">
                    <p><?= JText::_('COM_EMUNDUS_FABRIK_AUTHOR_CIVILITY'); ?></p>
                </td>

                <td class="em-pdf-element-value">
                    <p><?= ($user_to[0]->gender == "M") ? "Monsieur" : "Madame"; ?></p>
                </td>

            </tr>

            <tr class="em-pdf-element">

                <td class="em-pdf-element-label">
                    <p><?= JText::_('LAST_NAME'); ?></p>
                </td>

                <td class="em-pdf-element-value">
                    <p><?= ucfirst($user_to[0]->lastname); ?></p>
                </td>

            </tr>

            <tr class="em-pdf-element">

                <td class="em-pdf-element-label">
                    <p><?= JText::_('FIRST_NAME'); ?></p>
                </td>

                <td class="em-pdf-element-value">
                    <p><?= ucfirst($user_to[0]->firstname); ?></p>
                </td>
            </tr>
        <?php endif; ?>
    <?php else: ?>

        <?php $institution = $m_cifre->getUserInstitution($user_to[0]->user_id);?>
        <tr class="em-pdf-element">

            <td class="em-pdf-element-label">
                <p><?= JText::_('COM_EMUNDUS_FABRIK_INSTITUTE_NAME'); ?></p>
            </td>

            <td class="em-pdf-element-value">
                <p><?= $institution->nom_de_structure; ?></p>
            </td>

        </tr>
    <?php endif; ?>

    <?php if ($user->id == $user_to[0]->user_id) :?>
        <tr class="em-pdf-element">

            <td class="em-pdf-element-label">
                <p><?= JText::_('EMAIL_FORM'); ?></p>
            </td>

            <td class="em-pdf-element-value">
                <p><a href="mailto:<?= $user_to[0]->email; ?>"><?= $user_to[0]->email; ?></a></p>
            </td>

        </tr>

        <?php if (!empty($this->data['jos_emundus_projet___contact_tel_raw'][0])) : ?>
            <tr class="em-pdf-element">

                <td class="em-pdf-element-label">
                    <p><?= JText::_('TELEPHONENUMBER'); ?></p>
                </td>
                <td class="em-pdf-element-value">
                    <p><?= $this->data['jos_emundus_projet___contact_tel_raw'][0]; ?></p>
                </td>

            </tr>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($this->data["jos_emundus_setup_profiles___id_raw"][0] == '1007') : ?>

        <?php $laboratoire = $m_cifre->getUserLaboratory($author->id);
        if (!empty($laboratoire)) :?>
            <tr class="em-pdf-element">

                <td class="em-pdf-element-label">
                    <p><?= JText::_('COM_EMUNDUS_FABRIK_AUTHOR_RESEARCH_UNIT'); ?></p>
                </td>

                <td class="em-pdf-element-value">
                    <p><?= $laboratoire->name; ?></p>
                </td>

            </tr>
        <?php endif; ?>

        <?php $ecoleDoctorale = $m_cifre->getDoctorale($laboratoire->id); ?>

        <?php if (!empty($ecoleDoctorale)) :?>
            <tr class="em-pdf-element">

                <td class="em-pdf-element-label">
                    <p><?= JText::_('COM_EMUNDUS_FABRIK_AUTHOR_SCHOOL'); ?></p>
                </td>

                <td class="em-pdf-element-value">
                    <p><?= $ecoleDoctorale; ?></p>
                </td>

            </tr>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($this->data['jos_emundus_projet___limit_date_raw'][0])) : ?>
        <tr class="em-pdf-element">

            <td class="em-pdf-element-label">
                <p><?= JText::_('COM_EMUNDUS_FABRIK_DISPO_DATE'); ?></p>
            </td>

            <td class="em-pdf-element-value">
                <p><?= JFactory::getDate($this->data["jos_emundus_projet___limit_date_raw"][0])->format('d/m/Y'); ?></p>
            </td>

        </tr>
    <?php endif; ?>

</table>

<table class="em-pdf-group breaker">
    <tr class="em-pdf-title-div">
        <td colspan="2">
            <h3><?= JText::_('COM_EMUNDUS_FABRIK_PROJECT_TITLE'); ?></h3>
        </td>
    </tr>

    <tr class="em-pdf-element">

        <td class="em-pdf-element-label">
            <p style="width: 50px;"><?= JText::_('COM_EMUNDUS_FABRIK_PROJECT_NAME'); ?></p>
        </td>

        <td class="em-pdf-element-value">
            <p><?= $this->data["jos_emundus_projet___titre_raw"][0]; ?></p>
        </td>

    </tr>

    <?php if ($this->data["jos_emundus_setup_profiles___id_raw"][0] != '1008') :?>
        <?php if (!empty($this->data['jos_emundus_projet___contexte_raw'][0])) :?>
            <tr class="em-pdf-element">

                <td class="em-pdf-element-label" style="width: 20%;">
                    <p><?= JText::_('COM_EMUNDUS_FABRIK_ENJEU'); ?></p>
                </td>

                <td class="em-pdf-element-value" style="width: 75%;">
                    <p><?= $this->data["jos_emundus_projet___contexte_raw"][0]; ?></p>
                </td>

            </tr>
        <?php endif; ?>
    <?php else :?>

        <?php if (!empty($this->data['jos_emundus_projet___contexte_raw'][0])) :?>
            <tr class="em-pdf-element">

                <td class="em-pdf-element-label">
                    <p><?= JText::_('COM_EMUNDUS_FABRIK_TERRITOIRE'); ?></p>
                </td>

                <td class="em-pdf-element-value">
                    <p><?= $this->data["jos_emundus_projet___contexte_raw"][0]; ?></p>
                </td>

            </tr>
        <?php endif; ?>
    <?php endif; ?>


    <?php if (!empty($this->data['jos_emundus_projet___question_raw'][0])) :?>
        <?php
        if ($this->data["jos_emundus_setup_profiles___id_raw"][0] == '1006') {
            $questionText = JText::_('COM_EMUNDUS_FABRIK_PROBLEMATIQUE_FUTURE_DOC');
        } elseif ($this->data["jos_emundus_setup_profiles___id_raw"][0] == '1007') {
            $questionText = JText::_('COM_EMUNDUS_FABRIK_PROBLEMATIQUE_CHERCHEUR');
        } elseif ($this->data["jos_emundus_setup_profiles___id_raw"][0] == '1008') {
            $questionText = JText::_('COM_EMUNDUS_FABRIK_GRAND_DEFI');
        }
        ?>
        <tr class="em-pdf-element">

            <td class="em-pdf-element-label">
                <p><?= $questionText; ?></p>
            </td>

            <td class="em-pdf-element-value">
                <p><?= $this->data["jos_emundus_projet___question_raw"][0]; ?></p>
            </td>
        </tr>
    <?php endif; ?>


    <?php if (!empty($this->data['jos_emundus_projet___methodologie_raw'][0])) : ?>
        <tr class="em-pdf-element">

            <td class="em-pdf-element-label">
                <p><?= JText::_('COM_EMUNDUS_FABRIK_METHODOLOGIE'); ?></p>
            </td>

            <td class="em-pdf-element-value">
                <p><?= $this->data["jos_emundus_projet___methodologie_raw"][0]; ?></p>
            </td>

        </tr>
    <?php endif; ?>



    <?php if (!empty($this->data['data_thematics___thematic_raw'][0])) : ?>
        <tr class="em-pdf-element">
            <td class="em-pdf-element-label">
                <p><?= JText::_('COM_EMUNDUS_FABRIK_THEMES'); ?></p>
            </td>

            <td class="em-pdf-element-value">
                <p><?= !empty(getProjectThematics($fnum)) ? implode(', ', getProjectThematics($fnum)) : JText::_('COM_EMUNDUS_FABRIK_NO_THEMES'); ?></p>
            </td>
        </tr>
    <?php endif; ?>

    <?php if (!empty($this->data["em_discipline___disciplines_raw"])) : ?>
        <tr class="em-pdf-element">

            <td class="em-pdf-element-label">
                <p><?= JText::_('COM_EMUNDUS_FABRIK_DISCIPLINES'); ?></p>
            </td>

            <td class="em-pdf-element-value">
                <p><?= !empty(getProjectDisciplines($fnum)) ? implode(', ', getProjectDisciplines($fnum)) : JText::_('COM_EMUNDUS_FABRIK_NO_DISCIPLINES'); ?></p>
            </td>

        </tr>
    <?php endif; ?>

    <tr class="em-pdf-element">

        <td class="em-pdf-element-label">
            <p><?= JText::_('COM_EMUNDUS_FABRIK_REGIONS'); ?></p>
        </td>

        <td class="em-pdf-element-value">
            <p>
                <?php
                if ($this->data["jos_emundus_recherche___all_regions_depatments_raw"] == "non") {
                    $regions = getRegions($fnum,$this->data["jos_emundus_setup_profiles___id_raw"][0]);
                    echo !empty($regions) ? implode(', ', array_unique($regions)) : JText::_('COM_EMUNDUS_FABRIK_NO_REGIONS');
                } else {
                    echo JText::_('COM_EMUNDUS_FABRIK_ALL_REGIONS');
                }

                ?>
            </p>
        </td>

    </tr>

    <tr class="em-pdf-element">

        <td class="em-pdf-element-label">
            <p><?= JText::_('COM_EMUNDUS_FABRIK_DEPARTMENTS'); ?></p>
        </td>

        <td class="em-pdf-element-value">
            <p><?php
                if ($this->data["jos_emundus_recherche___all_regions_depatments_raw"] == "non") {
                    $departments = getDepartments($fnum, $this->data["jos_emundus_setup_profiles___id_raw"][0]);
                    echo !empty($departments) ? implode(', ', array_unique($departments)) : JText::_('COM_EMUNDUS_FABRIK_NO_DEPARTMENTS');
                } else {
                    echo JText::_('COM_EMUNDUS_FABRIK_ALL_DEPARTMANTS');
                }
                ?>
            </p>
        </td>

    </tr>

</table>

<table class="em-pdf-group breaker">

    <tr class="em-pdf-title-div">
        <td colspan="2">
            <h3><?= JText::_('COM_EMUNDUS_FABRIK_PARTENAIRES'); ?></h3>
        </td>
    </tr>

    <?php if ($this->data["jos_emundus_setup_profiles___id_raw"][0] != '1006') : ?>

        <tr class="em-pdf-element">
            <td class="em-pdf-element-label">
                <p><?= JText::_('COM_EMUNDUS_FABRIK_FUTUR_DOC'); ?></p>
            </td>

            <td class="em-pdf-element-value">
                <p><?= $this->data["jos_emundus_recherche___futur_doctorant_yesno"]; ?></p>
            </td>
        </tr>


        <?php if ($this->data["jos_emundus_recherche___futur_doctorant_yesno"] == 0 && !empty($this->data["jos_emundus_recherche___futur_doctorant_nom_raw"]) && !empty($this->data["jos_emundus_recherche___futur_doctorant_prenom_raw"])) : ?>
            <?php if($this->data["jos_emundus_setup_profiles___id_raw"][0] == '1008' && $user->id == $user_to[0]->user_id) :?>
                <tr class="em-pdf-element">

                    <td class="em-pdf-element-label">
                        <p><?= JText::_('COM_EMUNDUS_FABRIK_FUTUR_DOC_NAME'); ?></p>
                    </td>

                    <td class="em-pdf-element-value">
                        <p><?= strtoupper($this->data["jos_emundus_recherche___futur_doctorant_nom"]) . " " . $this->data["jos_emundus_recherche___futur_doctorant_prenom"]; ?></p>
                    </td>

                </tr>
            <?php endif; ?>
        <?php endif; ?>


    <?php endif; ?>

    <?php if ($this->data["jos_emundus_setup_profiles___id_raw"][0] != '1007') : ?>
        <tr class="em-pdf-element">

            <td class="em-pdf-element-label">
                <p><?= JText::_('COM_EMUNDUS_FABRIK_EQUIPE_RECHERCHE'); ?></p>
            </td>

            <td class="em-pdf-element-value">
                <p><?= $this->data["jos_emundus_recherche___equipe_de_recherche_direction_yesno"]; ?></p>
            </td>

        </tr>

        <?php if ($this->data["jos_emundus_recherche___equipe_de_recherche_direction_yesno"] == 0 && !empty($this->data["jos_emundus_recherche___equipe_direction_equipe_de_recherche_raw"])) : ?>
            <?php if($this->data["jos_emundus_setup_profiles___id_raw"][0] == '1008' && $user->id == $user_to[0]->user_id) :?>
                <tr class="em-pdf-element">

                    <td class="em-pdf-element-label">
                        <p><?= JText::_('COM_EMUNDUS_FABRIK_EQUIPE_RECHERCHE_NAME'); ?></p>
                    </td>

                    <td class="em-pdf-element-value">
                        <p><?= $this->data["jos_emundus_recherche___equipe_direction_equipe_de_recherche_raw"]; ?></p>
                    </td>

                </tr>
            <?php endif; ?>
        <?php endif; ?>

    <?php endif; ?>



    <?php if ($this->data["jos_emundus_setup_profiles___id_raw"][0] != '1008') : ?>
        <tr class="em-pdf-element">

            <td class="em-pdf-element-label">
                <p><?= JText::_('COM_EMUNDUS_FABRIK_EQUIPE_ACTEUR_PUB'); ?></p>
            </td>

            <td class="em-pdf-element-value">
                <p><?= $this->data["jos_emundus_recherche___acteur_public_yesno"]; ?></p>
            </td>

        </tr>

        <?php if ($this->data["jos_emundus_recherche___acteur_public_yesno_raw"] == 0 && $user->id == $user_to[0]->user_id) : ?>

            <?php if (!empty($this->data["jos_emundus_recherche___acteur_public_type_raw"])) : ?>
                <tr class="em-pdf-element">

                    <td class="em-pdf-element-label">
                        <p><?= JText::_('COM_EMUNDUS_FABRIK_EQUIPE_ACTEUR_PUB_TYPE'); ?></p>
                    </td>

                    <td class="em-pdf-element-value">
                        <p><?= $this->data["jos_emundus_recherche___acteur_public_type_raw"]; ?></p>
                    </td>

                </tr>
            <?php endif; ?>

            <?php if (!empty($this->data["jos_emundus_recherche___acteur_public_nom_de_structure_raw"])) : ?>

                <tr class="em-pdf-element">

                    <td class="em-pdf-element-label">
                        <p><?= JText::_('COM_EMUNDUS_FABRIK_EQUIPE_ACTEUR_PUB_NAME'); ?></p>
                    </td>

                    <td class="em-pdf-element-value">
                        <p><?= $this->data["jos_emundus_recherche___acteur_public_nom_de_structure_raw"]; ?></p>
                    </td>

                </tr>
            <?php endif; ?>

        <?php endif; ?>

    <?php endif; ?>
</table>
<table class="em-pdf-group breaker">
    <?php if (!empty($files)) : ?>
        <tr class="em-pdf-title-div">
            <td colspan="2">
                <h3><?= JText::_('COM_EMUNDUS_FABRIK_ATTACHED_FILES'); ?></h3>
            </td>
        </tr>

        <?php foreach ($files as $file) : ?>
            <tr class="em-pdf-element">

                <td class="em-pdf-element-label">
                    <p><?= $file["value"]; ?></p>
                </td>

                <td class="em-pdf-element-value">
                    <p>
                        <a target="_blank" href="<?= JURI::root().'images'.DS.'emundus'.DS.'files'.DS.$this->data["jos_emundus_campaign_candidature___applicant_id_raw"][0].DS.$file['filename']; ?>"><?= $file["value"]; ?></a>
                    </p>
                </td>

            </tr>
        <?php endforeach; ?>

    <?php endif; ?>

</table>
