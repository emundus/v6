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

if ($this->params->get('show_page_heading', 1)) : ?>
    <div class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>">
        <?php echo $this->escape($this->params->get('page_heading')); ?>
    </div>
<?php endif;


$db = JFactory::getDBO();

$query = $db->getquery('true');
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
    } catch (Exection $e) {
        echo "<pre>";
        var_dump($query->__toString());
        echo "</pre>";
        die();
    }
}



echo $this->plugintop;

echo $this->loadTemplate('relateddata');


$region = "";
$department = "";
$chercheur = "";
$cherches = "";
$themes = "";
$regions = $this->data['data_regions___name_raw'];
$departments = $this->data['data_departements___departement_nom_raw'];
$chercheur = strtolower($this->data['jos_emundus_setup_profiles___label_raw'][0]);
$profile = $this->data['jos_emundus_setup_profiles___id_raw'][0];

require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'cifre.php');
$m_cifre = new EmundusModelCifre();

?>
    <!-- Title -->
    <p class="em-offre-title">
        <?php echo $this->data['jos_emundus_projet___titre_raw'][0]; ?>
    </p>

    <div class="em-offre-meta">
        <p>Sujet déposé le <strong
                    class="em-highlight"><?php echo date('d/m/Y', strtotime($fnumInfos['date_submitted'])); ?></strong>
        </p>

    </div>

    <!-- Author -->
    <div class="em-offre-author">
        <h1 class="em-offre-title">Profil du déposant</h1>
        <div class="em-offre-author-profile">
            <div class="em-offre-author-name"><strong>Type : </strong><?php echo $chercheur; ?></div>
        </div>

        <?php
        // We need to change up the page based on if the person is viewing an offer from a lab, a future PHd, or a municiplaity.
        //// Profile 1006 : Futur doctorant = display no special information.
        //// Profile 1007 : Researcher = display information about his lab.
        //// Profile 1008 : Municipality = display information about the organization.

        if ($profile == '1006') :?>

            <div class="em-offre-inst">
                <div class="em-offre-institution">
                    <strong>Parcours : </strong>
                    <?php $master = $m_cifre->getUserMasters($author->id); ?>
                    <?php echo $master->master_2_intitule . ' - ' . $master->master_2_etablissement . ' (' . $master->master_2_annee . ')'; ?>
                </div>
            </div>

        <?php elseif ($profile == '1007') : ?>
            <?php $laboratoire = $m_cifre->getUserLaboratory($author->id); ?>
            <div class="em-offre-inst">
                <div class="em-offre-institution">
                    <strong>Nom de l'unité de recherche : </strong>
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
            <a class="btn btn-default"
               href="/index.php?option=com_fabrik&task=details.view&formid=308&listid=318&rowid=<?php echo $laboratoire->id; ?>">Cliquez
                ici pour plus d'information</a>
      
                <?php if (!empty($author->titre_ecole_doctorale)) :?>
                    <div class="em-offre-ecole">
                        <div class="em-offre-ecole-doctorale">
                            <strong>École doctorale : </strong><?php echo $author->titre_ecole_doctorale; ?>
                        </div>
                    </div>
                <?php endif; ?>

        <?php elseif ($profile == '1008') : ?>
            <?php
            $institution = $m_cifre->getUserInstitution($author->id);
            ?>
            <div class="em-offre-inst">
                <div class="em-offre-institution">
                    <strong>Nom : </strong>
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
            <a class="btn btn-default"
               href="/index.php?option=com_fabrik&task=details.view&formid=307&listid=317&rowid=<?php echo $institution->id; ?>">Plus
                d'informations</a>
        <?php endif; ?>

        <div class="em-offre-limit-date">
            <strong>Date de disponibilité
                : </strong> <?php echo date('d/m/Y', strtotime($this->data['jos_emundus_projet___limit_date'][0])); ?>
        </div>
    </div>

    <div class="em-offre">
        <h1 class="em-offre-title">Le projet </h1>

        <p class="em-offre-subject-title">
            <strong>Titre : </strong><?php echo $this->data['jos_emundus_projet___titre_raw'][0]; ?>
        </p>

        <!-- THEMES -->
        <div class="em-offre-themes">
            <div class="em-offre-subtitle">Thématiques identifiées :</div>
            <strong class="em-highlight"> <?php echo !empty($this->data['data_thematics___thematic_raw']) ? is_array($this->data['data_thematics___thematic_raw']) ? implode('</strong>; <strong class="em-highlight">', $this->data['data_thematics___thematic_raw']) : $this->data['data_thematics___thematic_raw'] : '<strong class="em-highlight">Aucune thématique</strong>'; ?></strong>
        </div>

        <!-- DISCIPLINES -->
        <div class="em-offre-disciplines">
            <div class="em-offre-subtitle">Disciplines sollicitées :</div>
            <strong class="em-highlight"> <?php echo !empty($this->data['em_discipline___disciplines_raw']) ? is_array($this->data['em_discipline___disciplines_raw']) ? implode('</strong>; <strong class="em-highlight">', $this->data['em_discipline___disciplines_raw']) : $this->data['em_discipline___disciplines_raw'] : '<strong class="em-highlight">Aucune discipline</strong>'; ?></strong>
        </div>

        <?php if ($profile == '1006') : ?>
            <!-- Project context -->
            <p class="em-offre-contexte">
            <div class="em-offre-subtitle">Enjeu et actualité du sujet :
            </div><?php echo $this->data['jos_emundus_projet___contexte_raw'][0]; ?>
            </p>

    <?php elseif ($profile == '1008') : ?>
        <!-- Project context -->
        <p class="em-offre-contexte">
        <div class="em-offre-subtitle">Territoire :
        </div><?php echo $this->data['jos_emundus_projet___contexte_raw'][0]; ?>
        </p>
        <?php endif; ?>





        <!-- Project question -->
        <?php
        if ($profile == '1006')
            $questionText = 'Problématique :';
        elseif ($profile == '1007')
            $questionText = 'Problématique :';
        elseif ($profile == '1008')
            $questionText = 'Grand défi :';
        ?>
        <p class="em-offre-question">
        <div class="em-offre-subtitle"><?php echo $questionText; ?></div><?php echo $this->data['jos_emundus_projet___question_raw'][0]; ?>
        </p>

        <?php if ($profile != '1007') : ?>
            <!-- Project methodology -->
            <p class="em-offre-methodologie">
            <div class="em-offre-subtitle">Méthodologie proposée :
            </div><?php echo $this->data['jos_emundus_projet___methodologie_raw'][0]; ?>
            </p>
        <?php endif; ?>

        <div class="em-regions">
            <strong>Régions : </strong><?php if(!empty($regions)) echo implode(', ', $regions); ?>
        </div>

        <div class="em-departments">
            <strong>Départements : </strong>
                <?php
                    $departmentArray= array();
                    foreach ($this->data["jos_emundus_recherche_630_repeat_repeat_department___department"] as $dep)
                    {
                        $departmentArray[] = getDepartment($dep);
                    }

                    echo implode(', ', $departmentArray);
                ?>
        </div>
    </div>

    <div class="em-partenaires">
        <h1 class="em-partenaires-title">Les partenaires recherchés </h1>

        <?php if ($profile != '1006') : ?>
            <!-- Have futur docs -->
            <p class="em-partenaires-futur-doc">
                <strong>Un futur doctorant
                    : </strong><?php echo $this->data['jos_emundus_recherche___futur_doctorant_yesno']; ?>
            </p>

            <?php if ($this->data["jos_emundus_recherche___futur_doctorant_yesno_raw"] == 0) : ?>
                <p class="em-partenaires-futur-doc-name">
                    <strong>Nom et prénom du future doctorant :
                        <strong><?php echo strtoupper($this->data["jos_emundus_recherche___futur_doctorant_nom"]) . " " . $this->data["jos_emundus_recherche___futur_doctorant_prenom"]; ?>
                </p>
            <?php endif; ?>

        <?php endif; ?>

        <?php if ($profile == '1007') :?>
            <p class="em-partenaires-equipe-recherche">
                <strong>Une équipe de recherche
                    : </strong><?php echo $this->data["jos_emundus_recherche___equipe_de_recherche_codirection_yesno"]; ?>
            </p>
            <?php if ($this->data["jos_emundus_recherche___equipe_de_recherche_codirection_yesno_raw"] == 0) : ?>
                <p class="em-partenaires-equipe-recherche-name">
                    <strong>Nom de l'équipe partenaire
                        : </strong><?php echo $this->data["jos_emundus_recherche___equipe_codirection_nom_du_laboratoire"]; ?>
                </p>
            <?php endif; ?>

        <?php else: ?>
            <p class="em-partenaires-equipe-recherche">
                <strong>Une équipe de recherche
                    : </strong><?php echo $this->data["jos_emundus_recherche___equipe_de_recherche_direction_yesno"]; ?>
            </p>
            <?php if ($this->data["jos_emundus_recherche___equipe_de_recherche_direction_yesno_raw"] == 0) : ?>
                <p class="em-partenaires-equipe-recherche-name">
                    <strong>Nom de l'équipe partenaire
                        : </strong><?php echo $this->data["jos_emundus_recherche___equipe_direction_equipe_de_recherche_raw"]; ?>
                </p>
            <?php endif; ?>
        <?php endif; ?>



        <?php if ($this->data["jos_emundus_setup_profiles___id_raw"][0] != '1008') : ?>
            <p class="em-partenaires-acteur">
                <strong>Un acteur public ou associatif
                    : </strong><?php echo $this->data["jos_emundus_recherche___acteur_public_yesno"]; ?>
            </p>

            <p class="em-partenaires-acteur-type">
                <strong>Type : </strong><?php echo $this->data["jos_emundus_recherche___acteur_public_type_raw"]; ?>
            </p>

            <?php if ($this->data["jos_emundus_recherche___acteur_public_yesno_raw"] == 0) : ?>
                <p class="em-partenaires-acteur-type">
                    <strong>Nom du partenaire
                        : </strong><?php echo $this->data["jos_emundus_recherche___acteur_public_nom_de_structure_raw"]; ?>
                </p>
            <?php endif; ?>
        <?php endif; ?>

    </div>

<?php if (!empty($files)) : ?>
    <div class="em-attached-files">
        <h1 class="em-attached-title">Pièces jointes à l'annonce</h1>

        <?php foreach ($files as $file) : ?>

            <p class="em-attached-element">
            <div class="em-partenaires-subtitle"><?php echo $file["value"] . ' : '; ?></div><a target="_blank"
                                                                                               href="<?php echo JURI::root() . DS . 'images' . DS . 'emundus' . DS . 'files' . DS . $this->data["jos_emundus_campaign_candidature___applicant_id"][0] . DS . $file["filename"]; ?>"><?php echo $file["filename"]; ?></a>
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
            <button type="button" class="btn btn-default" disabled> Offre clôturée</button>
        </div>
    </div>

<?php elseif ($this->data['jos_emundus_campaign_candidature___applicant_id'][0] == JFactory::getUser()->id) : ?>

    <?php if ((isset($d['Status']) && $d['Status'] == 3) || (isset($d['jos_emundus_campaign_candidature___status']) && $d['jos_emundus_campaign_candidature___status'] == 3)) : ?>

        <div class="em-search-item-action">
            <div id="em-search-item-action-button">
                <button type="button" class="btn btn-default" disabled>Offre en attente de validation</button>
            </div>
        </div>

    <?php else : ?>

        <div class="em-search-item-action">
            <div id="em-search-item-action-button">
                <button type="button" class="btn btn-default" disabled>Offre déposée par vous-même</button>
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
            Entrer en contact
        </button>

        <div class="modal fade" id="contactModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Demande de contact</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <?php if ($user->profile == '1006') : ?>
                        <p>Pourquoi ce projet vous semble-t-il intéressant et la structure que vous contactez pertinente pour le traiter ? Quelles orientations méthodologiques et disciplinaires envisagez-vous ?</p>
                        <textarea id="em-contact-message" placeholder="Texte (2000 caractères)" maxlength="2000"></textarea>
                        <p>Pourquoi souhaitez-vous faire une thèse Cifre ? En quoi ce projet est-il en adéquation avec votre parcours académique et professionnel (ce que vous avez fait avant, ce que vous souhaitez faire après) ? </p>
                        <textarea id="em-contact-motivation" placeholder="Texte (2000 caractères)" maxlength="2000"></textarea>
                        <?php if (!empty($offers)) : ?>
                            <p>Si vous le souhaitez : vous pouvez joindre une de vos offres.</p>
                            <select id="em-join-offer">
                                <option value="">Je ne souhaite pas joindre mes offres.</option>
                                <?php foreach ($offers as $offer) : ?>
                                    <option value="<?php echo $offer->fnum; ?>"><?php echo $offer->titre; ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>

                        <hr>
                        <span class="em-upload-explain-text">Sélectionnez votre fichier, puis cliquez sur “Joindre” pour l’attacher à votre demande de contact</span>
                        <!-- Upload a file from computer -->
                        <div id="em-attachment-list">
                            <div id="cv-upload_file">
                                <h4 id="em-filename">Ajoutez votre CV au format .pdf (obligatoire)</h4>
                                <label for="em-cv_to_upload" accept="application/pdf"
                                       id="em-cv_to_upload_label">
                                    <input type="file" id="em-cv_to_upload">
                                </label>
                                <span className="file-name" id="cv-file-name"></span>
                            </div>

                            <span class="input-group-btn">
                                    <a class="btn btn-grey" type="button" id="uploadButton" style="top:13px;"
                                       onClick="cvAddFile();">Joindre</a>
                                </span>

                            <div id="doc-upload_file">
                                <h4 id="em-filename">Ajouter un document (facultatif)</h4>
                                <span class="em-upload-explain-text">Sélectionnez votre fichier, puis cliquez sur “Joindre” pour l’attacher à votre demande de contact</span>
                                <label for="em-doc_to_upload" id="em-doc_to_upload_label">
                                    <input type="file" id="em-doc_to_upload">
                                </label>
                                <span className="file-name" id="other-doc-file-name"></span>
                            </div>

                            <span class="input-group-btn">
                                        <a class="btn btn-grey" type="button" accept="application/pdf" id="uploadButton"
                                           style="top:13px;" onClick="docAddFile();">Joindre</a>
                                    </span>

                            <?php else : ?>

                            <p>Présentez-vous et expliquez en quoi ce projet et la personne que vous contactez sont en adéquation avec ce que vous faites ou souhaitez faire dans votre structure.</p>
                            <textarea id="em-contact-message" placeholder="Texte (3000 caractères)" maxlength="3000"></textarea>
                            <?php if (!empty($offers)) : ?>
                                <p>Vous pouvez joindre une annonce que vous avez publiée sur la plateforme (facultatif).</p>
                                <select id="em-join-offer">
                                    <option value="">Je ne souhaite pas joindre mes offres.</option>
                                    <?php foreach ($offers as $offer) : ?>
                                        <option value="<?php echo $offer->fnum; ?>"><?php echo $offer->titre; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>

                            <hr>
                            <!-- Upload a file from computer -->
                            <div id="em-attachment-list">
                                <div id="doc-upload_file">
                                    <h4 id="em-filename">Ajouter un document (facultatif)</h4>
                                    <span class="em-upload-explain-text">Sélectionnez votre fichier, puis cliquez sur “Joindre” pour l’attacher à votre demande de contact</span>
                                    <label for="em-doc_to_upload" id="em-doc_to_upload_label">
                                        <input type="file" id="em-doc_to_upload">
                                    </label>
                                    <span className="file-name" id="other-doc-file-name"></span>
                                </div>

                                <span class="input-group-btn">
                                        <a class="btn btn-grey" type="button" accept="application/pdf" id="uploadButton"
                                           style="top:13px;" onClick="docAddFile();">Joindre</a>
                                    </span>

                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-dismiss="modal"
                                        onclick="actionButton('contact')">Envoyer la demande de contact
                                </button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <?php elseif ($action_button == 'reply') : ?>
                    <button type="button" class="btn btn-primary" onclick="actionButton('reply')">
                        Répondre
                    </button>
                    <button type="button" class="btn btn-primary" onclick="breakUp('ignore')">
                        Ignorer
                    </button>

                <?php elseif ($action_button == 'retry') : ?>
                    <button type="button" class="btn btn-primary" onclick="actionButton('retry')">
                        Relancer
                    </button>
                    <button type="button" class="btn btn-primary" onclick="breakUp('cancel')">
                        Annuler la demande
                    </button>

                <?php elseif ($action_button == 'breakup') : ?>
                    <button type="button" class="btn btn-primary" onclick="breakUp('breakup')">
                        Couper contact
                    </button>
                <?php endif; ?>

            </div>
        </div>
        <?php echo $this->loadTemplate('buttons'); ?>
    </div>

    <div class="em-modal-sending-emails" id="em-modal-sending-emails">
        <div id="em-sending-email-caption">Envoi en cours ...</div>
        <img class="em-sending-email-img" id="em-sending-email-img" src="/images/emundus/sending-email.gif">
    </div>

    <script>


        jQuery('#em-doc_to_upload').on('change',function(evt) {
            jQuery('#other-doc-file-name').html(evt.target.files[0].name);
        });

        jQuery('#cv_to_upload').on('change',function(evt) {
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

                var ML = jQuery('#lm-upload_file').find('.hidden').text();
                if (ML != null && ML != '' && typeof ML != 'undefined')
                    data.ML = ML;

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
                    if (result.status) {

                        // When we successfully change the status, we simply dynamically change the button.
                        if (action == 'contact') {
                            jQuery('#em-search-item-action-button').html('<button type="button" class="btn btn-primary" onclick="actionButton(\'retry\')">Relancer</button> ' +
                                ' <button type="button" class="btn btn-primary" onclick="breakUp(\'cancel\')">Annuler la demande</button>');
                        } else if (action == 'retry') {
                            jQuery('#em-search-item-action-button').html('<button type="button" class="btn btn-default" disabled > Message envoyé </button>');
                        } else if (action == 'reply') {
                            jQuery('#em-search-item-action-button').html('<button type="button" class="btn btn-danger" onclick="breakUp()"> Couper contact </button>');
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
                            '        Entrer en contact' +
                            '        </button>' +
                            '        <div class="modal fade" id="contactModal" tabindex="-1" role="dialog">' +
                            '            <div class="modal-dialog" role="document">' +
                            '                <div class="modal-content">' +
                            '                    <div class="modal-header">' +
                            '                        <h5 class="modal-title">Demande de contact</h5>' +
                            '                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">' +
                            '                            <span aria-hidden="true">&times;</span>' +
                            '                        </button>' +
                            '                    </div>' +
                            '                    <div class="modal-body">' +
                            <?php if ($user->profile == '1006') : ?>
                            '             <p>Pourquoi ce projet vous semble-t-il intéressant et la structure que vous contactez pertinente pour le traiter ? Quelles orientations méthodologiques et disciplinaires envisagez-vous ?</p>' +
                            '                <textarea id="em-contact-message" placeholder="Texte (2000 caractères)" maxlength="2000"></textarea>' +
                            '            <p>Pourquoi souhaitez-vous faire une thèse Cifre ? En quoi ce projet est-il en adéquation avec votre parcours académique et professionnel (ce que vous avez fait avant, ce que vous souhaitez faire après) ? </p>' +
                            '                <textarea id="em-contact-motivation" placeholder="Texte (2000 caractères)" maxlength="2000"></textarea>' +
                            <?php if (!empty($offers)) : ?>
                            '                 <p>Si vous le souhaitez : vous pouvez joindre une de vos offres.</p>' +
                            '                 <select id="em-join-offer">' +
                            '                     <option value="">Je ne souhaite pas joindre mes offres.</option>' +
                            <?php foreach ($offers as $offer) : ?>
                            '                         <option value="<?php echo $offer->fnum; ?>"><?php echo $offer->titre; ?></option>' +
                            <?php endforeach; ?>
                            '                </select>' +
                            <?php endif; ?>

                            '            <hr>' +
                            '            <span class="em-upload-explain-text">Sélectionnez votre fichier, puis cliquez sur “Joindre” pour l’attacher à votre demande de contact</span>' +
                            '            <div id="em-attachment-list">' +
                            '                <div id="cv-upload_file">' +
                            '                    <h4 id="em-filename">Ajoutez votre CV au format .pdf (obligatoire)</h4>' +
                            '                     <label for="em-cv_to_upload" accept="application/pdf"' +
                            '                            id="em-cv_to_upload_label">' +
                            '                         <input type="file" id="em-cv_to_upload">' +
                            '                    </label>' +
                            '                   <span className="file-name" id="cv-file-name"></span>' +
                            '                </div>' +

                            '               <span class="input-group-btn">' +
                            '       <a class="btn btn-grey" type="button" id="uploadButton" style="top:13px;"' +
                            '           onClick="cvAddFile();">Joindre</a>' +
                            '  </span>' +

                            '   <div id="doc-upload_file">' +
                            '                 <h4 id="em-filename">Ajouter un document (facultatif)</h4>' +
                            '                 <span class="em-upload-explain-text">Sélectionnez votre fichier, puis cliquez sur “Joindre” pour l’attacher à votre demande de contact</span>' +
                            '                 <label for="em-doc_to_upload" id="em-doc_to_upload_label">' +
                            '                     <input type="file" id="em-doc_to_upload">' +
                            '                 </label>' +
                            '                   <span className="file-name" id="other-doc-file-name"></span>' +
                            '             </div>' +

                            '             <span class="input-group-btn">' +
                            '         <a class="btn btn-grey" type="button" accept="application/pdf" id="uploadButton"' +
                            '            style="top:13px;" onClick="docAddFile();">Joindre</a>' +
                            '     </span>' +

                            <?php else : ?>

                            '          <p>Présentez-vous et expliquez en quoi ce projet et la personne que vous contactez sont en adéquation avec ce que vous faites ou souhaitez faire dans votre structure.</p>' +
                            '          <textarea id="em-contact-message" placeholder="Texte (3000 caractères)" maxlength="3000"></textarea>' +
                            <?php if (!empty($offers)) : ?>
                            '              <p>Vous pouvez joindre une annonce que vous avez publiée sur la plateforme (facultatif).</p>' +
                            '              <select id="em-join-offer">' +
                            '                  <option value="">Je ne souhaite pas joindre mes offres.</option>' +
                            <?php foreach ($offers as $offer) : ?>
                            '                      <option value="<?php echo $offer->fnum; ?>"><?php echo $offer->titre; ?></option>' +
                            <?php endforeach; ?>
                            '              </select>' +
                            <?php endif; ?>

                            '         <hr>' +
                            '         <div id="em-attachment-list">' +
                            '             <div id="doc-upload_file">' +
                            '                 <h4 id="em-filename">Ajouter un document (facultatif)</h4>' +
                            '                 <span class="em-upload-explain-text">Sélectionnez votre fichier, puis cliquez sur “Joindre” pour l’attacher à votre demande de contact</span>' +
                            '                 <label for="em-doc_to_upload" id="em-doc_to_upload_label">' +
                            '                     <input type="file" id="em-doc_to_upload">' +
                            '                 </label>' +
                            '                   <span className="file-name" id="other-doc-file-name"></span>' +
                            '             </div>' +

                            '             <span class="input-group-btn">' +
                            '         <a class="btn btn-grey" type="button" accept="application/pdf" id="uploadButton"' +
                            '            style="top:13px;" onClick="docAddFile();">Joindre</a>' +
                            '     </span>' +

                            <?php endif; ?>
                            '                    </div>' +
                            '                    <div class="modal-footer">' +
                            '                        <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="actionButton(\'contact\')">Envoyer la demande de contact</button>' +
                            '                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>' +
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

        // Add file to the list being attached.
        function lmAddFile() {
            // We need to get the file uploaded by the user.
            var lm = jQuery("#em-lm_to_upload")[0].files[0];
            var lmId = jQuery("#lm-upload_file");
            var uploadlm = new Upload(lm, lmId);

            // Verification of style size and type can be done here.
            uploadlm.doUpload();
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
                alert("Type de document non permis. Veuillez uniquement envoyer des fichiers au format PDF.");
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