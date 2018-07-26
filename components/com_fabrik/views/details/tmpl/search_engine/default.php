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
$author = JFactory::getUser((int)substr($fnum,-7));

$form = $this->form;
$model = $this->getModel();
$groupTmpl = $model->editable ? 'group' : 'group_details';
$active = ($form->error != '') ? '' : ' fabrikHide';

if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
	</div>
<?php
endif;


echo $this->plugintop;
echo $this->loadTemplate('buttons');
echo $this->loadTemplate('relateddata');


$region=""; $department=""; $chercheur=""; $cherches=""; $themes="";
$regions 	= $this->data['data_regions___name_raw'];
$departments= $this->data['data_departements___departement_nom_raw'];
$chercheur 	= strtolower($this->data['jos_emundus_setup_profiles___label_raw'][0]);
$profile    = $this->data['jos_emundus_setup_profiles___id_raw'][0];

?><div class="em-offre"><?php

    // Build a natural sentence
    // TODO : The information here comes out as jos_emundus_recherche___futur_doctorant_yesno_raw. This is not OK.
    if (count(array_keys($this->data, "oui")) > 1) {
        $cherches = implode(",", array_keys($this->data, "oui"));
        $cherches = strtolower(str_replace(","," </b></i>et<i><b> ", $cherches));
    } else {
        $cherches = strtolower(array_search("oui", $this->data));
    }


    // Build some of the text to make for a more natural sentence on the front end.
    $themes = $this->data['jos_emundus_projet_620_repeat___themes_raw'];
    if (sizeof($themes) > 1)
        $themes = ' les themes <i><b>'.implode(', ',$themes);
    else
        $themes = ' le theme <i><b>'.$themes[0];

    if (sizeof($regions) > 1)
        $regions = 'Dans les régions <i><b>'.implode(' et ',$regions);
    else
        $regions = 'En région <i><b>'.$regions[0];

    if (sizeof($departments) > 1)
        $departments = ' dans les départements <i><b>'.implode(' et ',$departments);
    else
        $departments = ' dans le département <i><b>'.$departments[0];
    ?>

    <!-- INTRO TEXT -->
    <div class="em-offre-summary">
        <p><?php echo $regions; ?></b></i>,<?php echo $departments; ?></b></i> sur <?php echo $themes; ?></b></i></p>
    </div>

    <hr>

    <!-- Title -->
    <p class="em-offre-title">
        <strong>Sujet de thèse : </strong><?php echo $this->data['jos_emundus_projet___titre_raw'][0]; ?>
    </p>

    <!-- Author -->
    <!-- TODO: Add more information, not just the author's name but also something more like 'la communauté de communes de :' -->
    <div class="em-offre-author">
        <br><strong>Sujet proposé par : </strong><?php echo $author->name; ?>

	    <?php
	    // We need to change up the page based on if the person is viewing an offer from a lab, a future PHd, or a municiplaity.
	    //// Profile 1006 : Futur doctorant = display no special information.
	    //// Profile 1007 : Researcher = display information about his lab.
	    //// Profile 1008 : Municipality = display information about the organization.
	    if ($profile == '1007') :?>
            <?php
                require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'cifre.php');
                $m_cifre = new EmundusModelCifre();
                $laboratoire = $m_cifre->getUserLaboratory($author->id);
            ?>
            du laboratoire
                <?php
                    if (!empty($laboratoire->website)) {
                        $parse =parse_url($laboratoire->website, PHP_URL_SCHEME) === null ? 'http://' . $laboratoire->website: $laboratoire->website;
                        echo '<a target="_blank " href="'.$parse.'">';
                    }
                        
                    echo $laboratoire->name;
                    if (!empty($laboratoire->website))
                        echo '</a>';
                ?>
                <a class="btn btn-default" href="/index.php?option=com_fabrik&task=details.view&formid=308&listid=318&rowid=<?php echo $laboratoire->id; ?>">Cliquez ici pour plus d'information</a>
        <?php elseif ($profile == '1008') :?>
		    <?php
                require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'cifre.php');
                $m_cifre = new EmundusModelCifre();
                $institution = $m_cifre->getUserInstitution($author->id);
		    ?>
            de la structure
		    <?php
            
		    if (!empty($institution->website)) {
                $parse =parse_url($institution->website, PHP_URL_SCHEME) === null ? 'http://' . $institution->website: $institution->website;
                echo '<a target="_blank " href="'.$parse.'">';
            }
		    echo $institution->nom_de_structure;
		    if (!empty($institution->website))
			    echo '</a>';
		    ?>
            <a class="btn btn-default" href="/index.php?option=com_fabrik&task=details.view&formid=307&listid=317&rowid=<?php echo $institution->id; ?>">Cliquez ici pour plus d'information</a>
        <?php endif; ?>
    </div>

    <hr>
    <!-- Presentation of the project -->
    <div class="em-offre-presentation">

        <strong>Presentation du projet</strong>

        <!-- Project context -->
        <p class="em-offre-contexte">
            <strong>Contexte : </strong><?php echo $this->data['jos_emundus_projet___contexte_raw'][0]; ?>
        </p>

        <!-- Project question -->
        <p class="em-offre-question">
            <strong>Grande question posée : </strong><?php echo $this->data['jos_emundus_projet___question_raw'][0]; ?>
        </p>

        <!-- Project methodology -->
        <p class="em-offre-methodologie">
            <strong>Méthodologie proposée : </strong><?php echo $this->data['jos_emundus_projet___methodologie_raw'][0]; ?>
        </p>

        <!-- Project disciplines -->
        <div class="em-offre-disciplines">
            <!-- TODO: Add the list of disciplines requested. -->
        </div>

    </div>

    <hr>
    <!-- Contact information -->
    <div class="em-offre-contact">
        <strong> Informations de contact </strong>
        <p class="em-contact-item"><strong>Nom : </strong><?php echo !empty($this->data['jos_emundus_projet___contact_nom_raw'][0])?$this->data['jos_emundus_projet___contact_nom_raw'][0]:'Aucun nom renseigné'; ?></p>
        <p class="em-contact-item"><strong>Mail : </strong><?php echo !empty($this->data['jos_emundus_projet___contact_mail_raw'][0])?$this->data['jos_emundus_projet___contact_mail_raw'][0]:'Aucun mail renseingé'; ?></p>
        <p class="em-contact-item"><strong>Tel : </strong><?php echo !empty($this->data['jos_emundus_projet___contact_tel_raw'][0])?$this->data['jos_emundus_projet___contact_tel_raw'][0]:'Aucun numéro renseigné'; ?></p>
    </div>

</div>


<?php
// Log the action of opening the persons form.
require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
EmundusModelLogs::log($user->id, $author->id, $fnum, 33, 'r', 'COM_EMUNDUS_LOGS_OPEN_OFFER');

// Action button types:
// // NO BUTTON : if the offer belongs to the user.
// // ENTREZ EN CONTACT : If the user has not already contacted.
// // REPONDRE : If the user has already been contacted for this offer but has not answered.
// // RELANCE : If the user has contacted but not been answered yet.
// // BREAK UP : If the user is collaborating with the other.
require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'controllers'.DS.'cifre.php');
$c_ciffe = new EmundusControllerCifre();
$action_button = $c_ciffe->getActionButton($fnum);
?>

<!-- Button used for matching with the offer -->
<div class="em-search-item-action">

    <span class="alert alert-danger hidden" id="em-action-text"></span>

    <div id="em-search-item-action-button">

    <?php if ($action_button == 'contact') :?>

        <?php $offers = $c_ciffe->getOwnOffers($fnum); ?>

        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#contactModal">
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
                        <p>Veuillez confirmer que vous souhaitez contacter le créateur de cette offre.</p>
                        <?php if (!empty($offers)) :?>
                            <p>Si vous le souhaitez: vous pouvez joindre une de vos offres.</p>
                            <select id="em-join-offer">
                                <option value="">Je ne souhaite pas joindre mes offres</option>
                                <?php foreach ($offers as $offer) :?>
                                    <option value="<?php echo $offer->fnum; ?>"><?php echo $offer->titre; ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="actionButton('contact')">Evoyer la demande de contact</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($action_button == 'reply') :?>
        <button type="button" class="btn btn-primary" onclick="actionButton('reply')">
            Répondre
        </button>
        <button type="button" class="btn btn-primary" onclick="breakUp()">
            Ignorer
        </button>

    <?php elseif ($action_button == 'retry') :?>
        <button type="button" class="btn btn-primary" onclick="actionButton('retry')">
            Relancer
        </button>

    <?php elseif ($action_button == 'breakup') :?>
        <button type="button" class="btn btn-primary" onclick="breakUp()">
            Couper contact
        </button>
    <?php endif; ?>

    </div>

</div>

<script>

    function actionButton(action) {

        var fnum = '<?php echo $fnum; ?>';
        var data = {
            fnum : fnum
        };

        if (action == 'contact') {
            if (document.getElementById('em-join-offer') != null) {
                // Get the offer selected from the dropdown by the user.
                var linkedOffer = document.getElementById('em-join-offer').value;
                if (linkedOffer != null && linkedOffer != '' && typeof linkedOffer != 'undefined')
                    data.linkedOffer = linkedOffer;
                
            }
        }

        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?option=com_emundus&controller=cifre&task='+action,
            data: data,
            beforeSend: function () {
                jQuery('#em-search-item-action-button').html('<button type="button" class="btn btn-default" disabled> ... </button>');
            },
            success: function(result) {
                if (result.status) {


                    // When we successfully change the status, we simply dynamically change the button.

                    if (action == 'contact') {
                        jQuery('#em-search-item-action-button').html('<button type="button" class="btn btn-primary" onclick="actionButton(\'retry\')"> Relancer </button>');
                    }

                    else if (action == 'retry') {
                        jQuery('#em-search-item-action-button').html('<button type="button" class="btn btn-default" disabled > Méssage envoyé </button>');
                    }

                    else if (action == 'reply') {
                        jQuery('#em-search-item-action-button').html('<button type="button" class="btn btn-danger" onclick="breakUp()"> Couper contact </button>');
                    }

                    else if (action == 'breakup') {
                        jQuery('#em-search-item-action-button').html('' +
                            '<button type="button" class="btn btn-success" data-toggle="modal" data-target="#contactModal">' +
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
                            '                        <p>Veuillez confirmer que vous souhaitez contacter le créateur de cette offre.</p>' +
                                                        <?php if (!empty($offers)) :?>
                            '                            <p>Si vous le souhaitez: vous pouvez joindre une de vos offres.</p>' +
                            '                            <select id="em-join-offer">' +
                                                            <?php foreach ($offers as $offer) :?>
                            '                                    <option value="<?php echo $offer->fnum; ?>"><?php echo $offer->titre; ?></option>' +
                                                            <?php endforeach; ?>
                            '                            </select>' +
                                                        <?php endif; ?>
                            '                    </div>' +
                            '                    <div class="modal-footer">' +
                            '                        <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="actionButton(\'contact\')">Evoyer la demande de contact</button>' +
                            '                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>' +
                            '                    </div>' +
                            '                </div>' +
                            '            </div>');
                    }

                } else {
                    var actionText = document.getElementById('em-action-text');
                    actionText.classList.remove('hidden');
                    actionText.innerHTML = result.msg;
                }
            },
            error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        });
    }

    function breakUp() {
        var data = {
            fnum : '<?php echo $fnum; ?>'
        };

        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?option=com_emundus&controller=cifre&task=breakup',
            data: data,
            success: function(result) {
                if (result.status) {

                    // Dynamically change the button back to the state of not having a link.
                    jQuery('#em-search-item-action-button').html('' +
                        '<button type="button" class="btn btn-success" data-toggle="modal" data-target="#contactModal">' +
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
                        '                        <p>Veuillez confirmer que vous souhaitez contacter le créateur de cette offre.</p>' +
		                                            <?php if (!empty($offers)) :?>
                        '                            <p>Si vous le souhaitez: vous pouvez joindre une de vos offres.</p>' +
                        '                            <select id="em-join-offer">' +
		                                                <?php foreach ($offers as $offer) :?>
                        '                                    <option value="<?php echo $offer->fnum; ?>"><?php echo $offer->titre; ?></option>' +
		                                                <?php endforeach; ?>
                        '                            </select>' +
		                                            <?php endif; ?>
                        '                    </div>' +
                        '                    <div class="modal-footer">' +
                        '                        <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="actionButton(\'contact\')">Evoyer la demande de contact</button>' +
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
            error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        });
    }

</script>

<?php
echo $this->pluginbottom;
echo $this->loadTemplate('actions');
echo '</div>';
echo $form->outro;
echo $this->pluginend;
