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

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');

$m_users = new EmundusModelUsers();


$form = $this->form;
$model = $this->getModel();

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


$db = JFactory::getDBO();

$query = $db->getquery('true');

$user_to = $m_users->getUserById($this->data["jos_emundus_cifre_links___user_to_raw"]);

//user_to profile
$query
    ->select($db->quoteName('label'))
    ->from($db->quoteName('#__emundus_setup_profiles'))
    ->where($db->quoteName('id') . ' = ' . $user_to[0]->profile);

$db->setQuery($query);

try {
    $user_to_profile = $db->loadResult();
    $query->clear();
} catch (Exection $e) {
    echo "<pre>";
    var_dump($query->__toString());
    echo "</pre>";
    die();
}

$user_from = $m_users->getUserById($this->data["jos_emundus_cifre_links___user_from_raw"]);
//user_from profile
$query
    ->select($db->quoteName('label'))
    ->from($db->quoteName('#__emundus_setup_profiles'))
    ->where($db->quoteName('id') . ' = ' . $user_from[0]->profile);

$db->setQuery($query);

try {
    $user_from_profile = $db->loadResult();
    $query->clear();
} catch (Exection $e) {
    echo "<pre>";
    var_dump($query->__toString());
    echo "</pre>";
    die();
}

//jos_emundus_projet___contact_tel_to
$query
    ->select($db->quoteName('contact_tel'))
    ->from($db->quoteName('#__emundus_projet'))
    ->where($db->quoteName('fnum') . ' LIKE "' . $this->data['jos_emundus_cifre_links___fnum_to_raw'] . '"');

$db->setQuery($query);

try {
    $telephone_to = $db->loadResult();
    $query->clear();
} catch (Exection $e) {
    echo "<pre>";
    var_dump($query->__toString());
    echo "</pre>";
    die();
}



// Get all uploaded files
$query
    ->select($db->quoteName(array('eup.filename', 'sa.value')))
    ->from($db->quoteName('#__emundus_uploads', 'eup'))
    ->join('LEFT', $db->quoteName('#__emundus_setup_attachments', 'sa') . ' ON (' . $db->quoteName('sa.id') . ' = ' . $db->quoteName('eup.attachment_id') . ')')
    ->where($db->quoteName('fnum') . ' LIKE "' . $this->data['jos_emundus_recherche___fnum_raw'] . '" AND eup.can_be_viewed = 1');

$db->setQuery($query);

try {
    $files = $db->loadAssocList();
    $query->clear();
} catch (Exection $e) {
    echo "<pre>";
    var_dump($query->__toString());
    echo "</pre>";
    die();
}
?>


<style>
    .fabrikForm.fabrikDetails {
        display: block;
        width: 90%;
        margin-left: auto;
        margin-right: auto;
    }

    .em-pdf-group {
        margin-bottom: 35px;
    }

    .em-pdf-title-div {
        background-color: #e9E9E9;
        border-top: 1px solid;
        border-bottom: 1px solid;
    }

    .em-pdf-title-div h3 {
        margin: 0px 0px 0px 10px;
    }

    .em-pdf-element {
        font-size: 16px;
        border-bottom: 1px solid;
        display: inline-block;
        width: 100%;
    }

    .em-pdf-element-label {
        float: left;
        display: inline-block;
        width: 35%;
        font-weight: bold;
    }

    .em-pdf-element-label p {
        margin: 0px 0px 0px 10px;
    }

    .em-pdf-element-value {
        display: inline-block;
        width: 64%;
    }


</style>

<div class="em-pdf-group">
    <img src="images/custom/Hesam/Logo_1000doctorants.JPG" alt="Logo 1000doctorants" style="vertical-align: top;"
         width="252" height="90">
    <div class="em-pdf-title-div">
        <h3>Récapitulatif de la demande de mise en relation sur <a href="<?php echo JURI::root(); ?>"><?php echo JURI::root(); ?></a></h3>
    </div>

    <div class="em-pdf-element">
        <div class="em-pdf-element-label">
            <p>Le projet</p>
        </div>

        <div class="em-pdf-element-value">
            <p><?php echo ($this->data["jos_emundus_cifre_links___fnum_to"]); ?></p>
        </div>
    </div>

    <?php if(!empty($this->data["jos_emundus_cifre_links___time_date_created"])) :?>
    <div class="em-pdf-element">
        <div class="em-pdf-element-label">
            <p>Date de premier contact</p>
        </div>

        <div class="em-pdf-element-value">
            <p><?php echo date("d/m/Y", strtotime($this->data["jos_emundus_cifre_links___time_date_created_raw"])); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <?php if(!empty($this->data["jos_emundus_cifre_links___state_raw"])) :?>
        <div class="em-pdf-element">
            <div class="em-pdf-element-label">
                <p>Etat</p>
            </div>

            <div class="em-pdf-element-value">
                <?php echo $this->data["jos_emundus_cifre_links___state"]; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if(!empty($this->data["jos_emundus_cifre_links___time_date_modified"]) && $this->data["jos_emundus_cifre_links___state_raw"] =="2") :?>
    <div class="em-pdf-element">
        <div class="em-pdf-element-label">
            <p>Date de mise en relation</p>
        </div>

        <div class="em-pdf-element-value">
            <p><?php echo date("d/m/Y", strtotime($this->data["jos_emundus_cifre_links___time_date_modified_raw"])); ?></p>
        </div>
    </div>
    <?php endif; ?>
</div>


<div class="em-pdf-group">
    <div class="em-pdf-title-div">
        <h3>Auteur de l'annonce</h3>
    </div>

    <div class="em-pdf-element">

        <div class="em-pdf-element-label">
            <p>Type</p>
        </div>

        <div class="em-pdf-element-value">
            <p><?php echo ucfirst($user_to_profile); ?></p>
        </div>

    </div>

    <div class="em-pdf-element">

        <div class="em-pdf-element-label">
            <p>Civilité</p>
        </div>

        <div class="em-pdf-element-value">
            <p><?php echo ($user_to[0]->gender == "M") ? "Monsieur" : "Madame"; ?></p>
        </div>

    </div>

    <div class="em-pdf-element">

        <div class="em-pdf-element-label">
            <p>Nom</p>
        </div>

        <div class="em-pdf-element-value">
            <p><?php echo ucfirst($user_to[0]->lastname); ?></p>
        </div>

    </div>

    <div class="em-pdf-element">

        <div class="em-pdf-element-label">
            <p>Prénom</p>
        </div>

        <div class="em-pdf-element-value">
            <p><?php echo ucfirst($user_to[0]->firstname); ?></p>
        </div>

    </div>

    <?php if(!empty($user_to[0]->email)) :?>
    <div class="em-pdf-element">

        <div class="em-pdf-element-label">
            <p>Email</p>
        </div>

        <div class="em-pdf-element-value">
            <p><a href="mailto:<?php echo $user_to[0]->email; ?>"><?php echo $user_to[0]->email; ?></a></p>
        </div>

    </div>
    <?php endif; ?>

    <?php if (!empty($telephone)) :?>
        <div class="em-pdf-element">

            <div class="em-pdf-element-label">
                <p>Numéro à contacter</p>
            </div>

            <div class="em-pdf-element-value">
                <p><?php echo $telephone; ?></p>
            </div>

        </div>
    <?php endif; ?>

</div>

<div class="em-pdf-group">
    <div class="em-pdf-title-div">
        <h3>Demandeur de l'annonce</h3>
    </div>

    <div class="em-pdf-element">

        <div class="em-pdf-element-label">
            <p>Type</p>
        </div>

        <div class="em-pdf-element-value">
            <p><?php echo ucfirst($user_from_profile); ?></p>
        </div>

    </div>

    <div class="em-pdf-element">

        <div class="em-pdf-element-label">
            <p>Civilité</p>
        </div>

        <div class="em-pdf-element-value">
            <p><?php echo ($user_to[0]->gender == "M") ? "Monsieur" : "Madame"; ?></p>
        </div>

    </div>

    <div class="em-pdf-element">

        <div class="em-pdf-element-label">
            <p>Nom</p>
        </div>

        <div class="em-pdf-element-value">
            <p><?php echo ucfirst($user_from[0]->lastname); ?></p>
        </div>

    </div>

    <div class="em-pdf-element">

        <div class="em-pdf-element-label">
            <p>Prénom</p>
        </div>

        <div class="em-pdf-element-value">
            <p><?php echo ucfirst($user_from[0]->firstname); ?></p>
        </div>

    </div>

    <?php if(!empty($user_from[0]->email)) :?>
        <div class="em-pdf-element">

            <div class="em-pdf-element-label">
                <p>Email</p>
            </div>

            <div class="em-pdf-element-value">
                <p><a href="mailto:<?php echo $user_from[0]->email; ?>"><?php echo $user_from[0]->email; ?></a></p>
            </div>

        </div>
    <?php endif; ?>

    <?php if (!empty($this->data["jos_emundus_cifre_links___fnum_from_raw"])) :?>
        <div class="em-pdf-element">

            <div class="em-pdf-element-label">
                <p>Offre jointe</p>
            </div>

            <div class="em-pdf-element-value">
                <p><?php echo $this->data["jos_emundus_cifre_links___fnum_from"]; ?></p>
            </div>

        </div>

    <?php endif; ?>
    

    <?php if ($user_from[0]->profile != '1006') :?>
        <?php if (!empty($this->data["jos_emundus_cifre_links___message_raw"])) :?>
            <div class="em-pdf-element">

                <div class="em-pdf-element-label">
                    <p>Présentation en quoi ce projet est en adéquation avec ce que vous faites ou souhaitez faire dans votre structure</p>
                </div>

                <div class="em-pdf-element-value">
                    <p><?php echo $this->data["jos_emundus_cifre_links___message_raw"]; ?></p>
                </div>

            </div>
        <?php endif; ?>

    <?php else: ?>

        <?php if (!empty($this->data["jos_emundus_cifre_links___motivation_raw"])) :?>
            <div class="em-pdf-element">

                <div class="em-pdf-element-label">
                    <p>Pourquoi ce projet vous semble-t-il intéressant et la structure que vous contactez pertinente pour le traiter ? Quelles orientations méthodologiques et disciplinaires envisagez-vous ?</p>
                </div>

                <div class="em-pdf-element-value">
                    <p><?php echo $this->data["jos_emundus_cifre_links___motivation_raw"]; ?></p>
                </div>

            </div>
        <?php endif; ?>

        <?php if (!empty($this->data["jos_emundus_cifre_links___message_raw"])) :?>
            <div class="em-pdf-element">

                <div class="em-pdf-element-label">
                    <p>Pourquoi souhaitez-vous faire une thèse Cifre ? En quoi ce projet est-il en adéquation avec votre parcours académique et professionnel (ce que vous avez fait avant, ce que vous souhaitez faire après) ? </p>
                </div>

                <div class="em-pdf-element-value">
                    <p><?php echo $this->data["jos_emundus_cifre_links___message_raw"]; ?></p>
                </div>

            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($user_from[0]->profile == '1006' && !empty($this->data["jos_emundus_cifre_links___cv_raw"])): ?>

        <div class="em-pdf-element">

            <div class="em-pdf-element-label">
                <p>CV</p>
            </div>

            <div class="em-pdf-element-value">
                <p>
                    <a target="_blank" href="<?php echo JURI::root().$this->data["jos_emundus_cifre_links___cv_raw"];?>">CV</a>
                </p>
            </div>

        </div>

    <?php endif; ?>

    <?php if (!empty($this->data["jos_emundus_cifre_links___document_raw"])): ?>

        <div class="em-pdf-element">

            <div class="em-pdf-element-label">
                <p>Document lié</p>
            </div>

            <div class="em-pdf-element-value">
                <p><a target="_blank" href="<?php echo JURI::root().$this->data["jos_emundus_cifre_links___document_raw"];?>"><?php echo pathinfo($this->data["jos_emundus_cifre_links___document_raw"])['filename'];?></a></p>
            </div>

        </div>

    <?php endif; ?>
</div>

<div>
    <a href="javascript:history.go(-1)">Retour</a>
</div>