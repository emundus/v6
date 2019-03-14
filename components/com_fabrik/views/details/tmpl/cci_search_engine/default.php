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
$doc = JFactory::getDocument();

if (empty($this->data['jos_emundus_setup_teaching_unity___id_raw'])) {
	JFactory::getApplication()->redirect("/rechercher");
}

$doc->addStyleSheet('/templates/g5_helium/custom/css/formation.css');
$doc->addStyleSheet('/media/com_emundus/lib/bootstrap-232/css/bootstrap.min.css');


if (empty($this->data['jos_emundus_setup_teaching_unity___id_raw'])) {
	JFactory::getApplication()->redirect("/rechercher");
}


require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'formations.php');

$m_formations = new EmundusModelFormations();
$m_files = new EmundusModelFiles();
$sessions = $m_files->programSessions($this->data['jos_emundus_setup_programmes___id_raw']);
if ($m_formations->checkHRUser($user->id)) {
    $applied = [];
} else {
	$applied = $m_files->getAppliedSessions($this->data['jos_emundus_setup_programmes___code_raw']);
}

if (!$user->guest) {
	require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'programme.php');
	$m_programme = new EmundusModelProgramme();
	$is_favorite = $m_programme->isFavorite($this->data['jos_emundus_setup_programmes___id_raw'], $user->id);
	$doc->addStyleSheet(DS.'media'.DS.'com_emundus'.DS.'lib'.DS.'iconate'.DS.'css'.DS.'iconate.min.css');
	$doc->addScript(DS.'media'.DS.'com_emundus'.DS.'lib'.DS.'iconate'.DS.'js'.DS.'iconate.min.js');
}


$form = $this->form;
$model = $this->getModel();
$groupTmpl = $model->editable ? 'group' : 'group_details';
$active = ($form->error != '') ? '' : ' fabrikHide';

if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>">
	    <?php echo $this->escape($this->params->get('page_heading')); ?>
	</div>
<?php endif;

    $city = $this->data['jos_emundus_setup_teaching_unity___location_city_raw'];
    $zip = $this->data['jos_emundus_setup_teaching_unity___location_zip_raw'];
    $address = $this->data['jos_emundus_setup_teaching_unity___location_address_raw'];
    $addTitle = $this->data['jos_emundus_setup_teaching_unity___location_title_raw'];
    $partenaire = str_replace(' ', '-', trim(strtolower($this->data['jos_emundus_setup_programmes___partner_raw'])));
    $days = trim($this->data['jos_emundus_setup_teaching_unity___days_raw']);
    $certificate = str_replace(' ', '-', trim(strtolower($this->data['jos_emundus_setup_programmes___certificate_raw'])));

    echo $this->plugintop;
    echo $this->loadTemplate('buttons');
    echo $this->loadTemplate('relateddata');

    // GETS all svg icons
    $date_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_dates.svg");
    $diplomant_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_diplomant.svg");
    $duree_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_duree.svg");
    $intervenant_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_intervenant.svg");
    $lieu_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_lieu.svg");
    $objectif_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_objectifs.svg");
    $pointscles_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_pointscles.svg");
    $prerequis_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_prerequis.svg");
    $prix_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_prix.svg");
    $public_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_public.svg");
    $telechargement_svg = file_get_contents(JPATH_BASE.DS."images".DS."custom".DS."ccirs".DS."icons".DS."picto_telechargement.svg");

    $title = $this->data['jos_emundus_setup_teaching_unity___label_raw'];
    $page_title = $this->data['jos_emundus_setup_thematiques___label_raw']." - ".$title;
    $video = $this->data['jos_emundus_setup_programmes___video_raw'];

    $document = JFactory::getDocument();
    $document->setTitle($page_title);
    $document->setDescription(substr(html_entity_decode(strip_tags(html_entity_decode($this->data['jos_emundus_setup_programmes___objectives_raw']))), 0, 200));
?>

<style>
    .em-star-button {
        cursor: pointer;
    }

    .em-star-button:hover,
    .em-star-button:active,
    .em-star-button.fas {
        color: #f5e653;
    }
</style>

<!-- Title -->
    <div class="em-themes em-theme-title em-theme-<?php echo $this->data['jos_emundus_setup_thematiques___color_raw']; ?>">
        <a href="/formations/<?php echo str_replace(['é','è','ê'],'e', html_entity_decode(mb_strtolower(str_replace('---','-', $this->data['jos_emundus_setup_thematiques___title_raw']))));?>"><?php echo $this->data['jos_emundus_setup_thematiques___label_raw']; ?></a>
    </div>

    <div class="g-block size-95">
        <h1><?php echo $title; ?>
            <?php if (!$user->guest) :?>
                <?php if ($is_favorite) :?>
                    <i class="fas fa-star em-star-button" rel="tooltip" title="<?php echo JText::_('FAVORITE_CLICK_HERE'); ?>" id="em-favorite" onclick="unfavorite(<?php echo $this->data['jos_emundus_setup_programmes___id_raw']; ?>)"></i>
                <?php else :?>
                    <i class="far fa-star em-star-button" rel="tooltip" title="<?php echo JText::_('FAVORITE_CLICK_HERE'); ?>" id="em-favorite" onclick="favorite(<?php echo $this->data['jos_emundus_setup_programmes___id_raw']; ?>)"></i>
                <?php endif; ?>
            <?php endif; ?>
        </h1>
            <p><?php echo JText::_('REF'). str_replace('FOR', '', $this->data['jos_emundus_setup_programmes___code_raw']) ;?><br>
            <?php if (!empty($this->data['jos_emundus_setup_programmes___numcpf_raw'])) { echo JText::_('CODE')." : " . $this->data['jos_emundus_setup_programmes___numcpf_raw']; } ?></p>
    </div>

        <div class="em-details g-block size-95 em-details-<?php echo $this->data['jos_emundus_setup_thematiques___color_raw']; ?>">

            <div class="duree-div">
                <div class="em-duree-icon em-icon-<?php echo $this->data['jos_emundus_setup_thematiques___color_raw']; ?>">
                    <?php echo $duree_svg; ?>
                </div>
                <div class="em-days">
                    <p id="days">
                        <?php
                        if (floatval($days) > 1) {
                            echo $days." ".JText::_('DAYS');
                        } elseif (floatval($days) == 1) {
                            echo $days." ".JText::_('DAY');
                        }
                        ?>
                    </p>
                </div>
            </div>

            <div class="prerequisite-div">
                <div class="em-details-icon em-icon-<?php echo $this->data['jos_emundus_setup_thematiques___color_raw']; ?>">
                    <?php echo $prerequis_svg; ?>
                </div>
                <div class="em-reqs">
                    <?php
                    if (trim($this->data['jos_emundus_setup_programmes___prerequisite_raw']) == '') {
	                    echo "<p>".JText::_('NO_PREREC')."</p>";
                    } else {
	                    echo html_entity_decode($this->data['jos_emundus_setup_programmes___prerequisite_raw']);
                    }
                    ?>
                </div>
            </div>

            <div class="doc-div" onclick="getProductPDF('<?php echo $this->data['jos_emundus_setup_programmes___code_raw']; ?>')">
                <div class="em-doc-icon em-icon-<?php echo $this->data['jos_emundus_setup_thematiques___color_raw']; ?>">
                    <?php echo $telechargement_svg; ?>
                </div>
                <div class="em-docs">
                    <p><?php echo JText::_('PEDAGO_FICHE'); ?></p>
                </div>
            </div>

            <?php if (!empty($partenaire)) :?>
                <div class="partner">
                    <b><?php echo JText::_('OUR_EXPERT'); ?></b>
                    <img src="images/custom/ccirs/partenaires/<?php echo $partenaire; ?>.png" alt="Logo partenaire <?php echo $partenaire; ?>">
                </div>
            <?php endif; ?>

        </div>

        <div class="em-offer">

            <div id="public">

                <div class="offer-icon em-icon-<?php echo $this->data['jos_emundus_setup_thematiques___color_raw']; ?>">
                    <?php echo $public_svg; ?>
                </div>
                <div id="offer-details">
                    <h2><?php echo JText::_('AUDIENCE'); ?></h2>
                    <?php
                    if (trim($this->data['jos_emundus_setup_programmes___audience_raw']) != '') {
	                    echo html_entity_decode($this->data['jos_emundus_setup_programmes___audience_raw']);
                    } else {
	                    echo "<p>".JText::_('NO_AUDIENCE')."</p>";
                    }
                    ?>
                </div>

            </div>

            <?php if (trim($this->data['jos_emundus_setup_programmes___objectives_raw']) != '') :?>
                <div id="objectif">

                <div class="offer-icon em-icon-<?php echo $this->data['jos_emundus_setup_thematiques___color_raw']; ?>">
                    <?php echo $objectif_svg; ?>
                </div>

                <div id="objectif-details">
                    <h2><?php echo JText::_('OBJECTIVES'); ?></h2>
                    <?php echo html_entity_decode($this->data['jos_emundus_setup_programmes___objectives_raw']); ?>
                </div>

            </div>
            <?php endif; ?>

            <?php if (trim($this->data['jos_emundus_setup_programmes___content_raw']) != '') :?>
                <div id="keys">
                    <div class="offer-icon em-icon-<?php echo $this->data['jos_emundus_setup_thematiques___color_raw']; ?>">
                        <?php echo $pointscles_svg; ?>
                    </div>

                    <div id="key-details">
                        <h2><?php echo JText::_('KEY_POINTS'); ?></h2>
	                    <?php echo html_entity_decode($this->data['jos_emundus_setup_programmes___content_raw']); ?>
                    </div>

                </div>
            <?php endif; ?>


            <?php if (!empty($certificate)) :?>
                <div id="certificate">
                    <div class="offer-icon em-icon-<?php echo $this->data['jos_emundus_setup_thematiques___color_raw']; ?>">
                        <?php echo $diplomant_svg; ?>
                    </div>

                    <div id="certificate-details">
                        <h3><?php echo JText::_('CERTIFICATE'); ?></h3>
                        <img src="images/custom/ccirs/certifications/<?php echo $certificate; ?>.png" alt="Logo certificat <?php echo $certificate; ?>">
                    </div>
                </div>
            <?php endif; ?>


        <div style="display: none;">
            <div class="em-options" id="em-formation-options">

                <div class="em-option-menu active" id="em-option-menu-inter">
                    <b><?php echo JText::_('INTER'); ?></b>
                </div>

                <div class="em-option-menu" id="em-option-menu-intra">
                    <b><?php echo JText::_('INTRA'); ?></b>
                </div>

                <div class="em-option-menu" id="em-option-menu-sur-mesure">
                    <b><?php echo JText::_('SUR_MESURE'); ?></b>
                </div>

                <div class="em-option" id="em-option-inter">
                    <div class="em-option-details">
                        <b><?php echo JText::_('NEXT_SESSIONS'); ?></b>
                    </div>

                    <?php foreach ($sessions as $session) :?>

                        <div class="formation">
                            <b><?php
                                $town = preg_replace('/[0-9]+/', '',  str_replace(" cedex", "", ucfirst(strtolower($session['location_city']))));
                                $town =  ucwords(strtolower($town), '\',. ');
                                $beforeComma = strpos($town, "D'");
                                if (!empty($beforeComma)) {
                                    $replace = strpbrk($town, "D'");
                                    $town = substr_replace($town,lcfirst($replace), $beforeComma);
                                }
                                setlocale(LC_ALL, 'fr_FR.utf8');
                                $start_day = date('d',strtotime($session['date_start']));
                                $end_day = date('d',strtotime($session['date_end']));
                                $start_month = date('m',strtotime($session['date_start']));
                                $end_month = date('m',strtotime($session['date_end']));
                                $start_year = date('y',strtotime($session['date_start']));
                                $end_year = date('y',strtotime($session['date_end']));

                                if ($start_day == $end_day && $start_month == $end_month && $start_year == $end_year) {
                                    echo strftime('%e',strtotime($session['date_start'])) . " " . strftime('%B',strtotime($session['date_end'])) . " " . date('Y',strtotime($session['date_end']));
                                } elseif ($start_month == $end_month && $start_year == $end_year) {
                                    echo strftime('%e',strtotime($session['date_start'])) . " au " . strftime('%e',strtotime($session['date_end'])) . " " . strftime('%B',strtotime($session['date_end'])) . " " . date('Y',strtotime($session['date_end']));
                                } elseif ($start_month != $end_month && $start_year == $end_year) {
                                    echo strftime('%e',strtotime($session['date_start'])) . " " . strftime('%B',strtotime($session['date_start'])) . " au " . strftime('%e',strtotime($session['date_end'])) . " " . strftime('%B',strtotime($session['date_end'])) . " " . date('Y',strtotime($session['date_end']));
                                } elseif (($start_month != $end_month && $start_year != $end_year) || ($start_month == $end_month && $start_year != $end_year)) {
                                    echo strftime('%e',strtotime($session['date_start'])) . " " . strftime('%B',strtotime($session['date_start'])) . " " . date('Y',strtotime($session['date_start'])) . " au " . strftime('%e',strtotime($session['date_end'])) . " " . strftime('%B',strtotime($session['date_end'])) . " " . date('Y',strtotime($session['date_end']));
                                }
                            ?>
                            </b>
                            <p><?php echo $town ;?></p>

                                <p>
                                    <?php
                                        $TTC = floatval($session['price'])+(floatval($session['price'])*floatval($session['tax_rate']));
                                        if (!empty($session['tax_rate'])) {
	                                        echo $TTC . " € TTC";
                                        } else {
	                                        echo intval($session['price']) . " € net";
                                        }
                                    ?>
                                </p>

                            <?php
                                if (($session['max_occupants'] - $session['occupants']) <= 3 && ($session['max_occupants'] - $session['occupants']) > 0) {
                                    echo "<p class='places'>".JText::_('LAST_SPOTS_LEFT')."</p>";
                                }
                            ?>

                                <?php if ($session['occupants'] < $session['max_occupants']) :?>

                                    <?php if (in_array($session['session_code'], $applied)) :?>
                                        <div class="em-option-buttons">
                                            <button class="em-option-complet" disabled><?php echo JText::_('ALREADY_SIGNED_UP'); ?></button>
                                        </div>
                                    <?php else: ?>

                                        <?php
                                        if ($user->guest) {
                                            $formUrl = base64_encode('/inscription?session='.$session['session_code']);
                                        } else {
	                                        $formUrl = base64_encode('/inscrire-des-collaborateurs?session='.$session['session_code']);
                                        }
                                        ?>

                                        <div class="em-option-buttons">
                                            <a href="/demande-de-contact" class="em-option-contact"><?php echo JText::_('BE_CONTACTED'); ?></a>
                                            <?php $register_url = "inscription?session=".$session['session_code']."&redirect=".$formUrl; ?>
                                            <a href="<?php echo $register_url; ?>" class="em-option-login"><?php echo JText::_('SIGNUP'); ?></a>
                                        </div>

                                    <?php endif; ?>

                                <?php else: ?>
                                    <div class="em-option-buttons">
                                        <button class="em-option-complet" disabled><?php echo JText::_('FULL'); ?></button>
                                    </div>
                                <?php endif; ?>
                        </div>

                    <?php endforeach; ?>

                    <ul id="pagin"></ul>
                </div>

                <div class="em-option hide" id="em-option-intra">
                    <div class="em-option-location">
                        <div class="location-icon">
                            <?php echo $lieu_svg; ?>
                        </div>

                        <div class="location-details">
                            <p><?php echo JText::_('IN_YOUR_COMPANY'); ?></p>
                            <p><?php echo JText::_('FOR'); ?> <?php echo $this->data['jos_emundus_setup_teaching_unity___min_occupants_raw'];?> <?php echo JText::_('PEOPLE_MINIMUM'); ?></p>
                        </div>
                    </div>

                    <div class="em-option-price">
                        <div class="price-icon">
                            <?php echo $prix_svg; ?>
                        </div>

                        <div class="price-details">
                            <p>
                                <?php
                                if (!empty($session['tax_rate'])) {
	                                echo intval($session['price'])." € ".JText::_('HT');
                                } else {
	                                echo intval($session['price'])." € ".JText::_('NET');
                                }
                                ?>
                            </p>
                            <p><?php echo JText::_('PER_PERSON'); ?></p>
                        </div>
                    </div>

                    <div class="em-option-buttons">
                        <a href="/demande-de-pre-inscription?session=<?php echo $session['code']; ?>" class="em-option-login"><?php echo JText::_('ASK_FOR_QUOTE'); ?></a>
                        <a href="/demande-de-contact" class="em-option-contact"><?php echo JText::_('BE_CONTACTED'); ?></a>
                    </div>

                </div>

                <div class="em-option hide" id="em-option-sur-mesure">
                   
                    <div class="em-option-details" id="sur-mesure-details">
                        <div class="top-paragraph">
                            <b><?php echo JText::_('ARE_YOU_INTERESTED'); ?></b>
                        </div>

                        <div class="bottom-paragraph">
                            <b><?php echo JText::_('WE_CAN_CUSTOM'); ?></b>
                        </div>
                    </div>

                    <a href="/demande-de-contact" class="em-option-contact"><?php echo JText::_('BE_CONTACTED'); ?></a>

                </div>
            </div>
        </div>


    <div class="modal fade" id="gmaps" tabindex="-1" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="back" >
                <div class="modal-header">
                    <h4><?php echo $addTitle . ' ' . $address . ' ' . $zip . ' ' . $city; ?><h4>
                </div>
                <div class="modal-body">
                    <div id="map"></div>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-default" data-dismiss="modal">Close</a>
                </div>
            </div>
        </div>
</div>

<script>

    var sessions = <?php echo json_encode($sessions); ?>;

    //Pagination
    pageSize = 4;

    var pageCount =  sessions.length / pageSize;

    if (pageCount > 1) {
        for (var i = 0 ; i<pageCount;i++) {
            jQuery("#pagin").append('<li><p>'+(i+1)+'</p></li> ');
        }
    }

    jQuery("#pagin li").first().find("p").addClass("current");
    showPage = function(page) {
        jQuery(".formation").hide();
        jQuery(".formation").each(function(n) {
            if (n >= pageSize * (page - 1) && n < pageSize * page)
                jQuery(this).show();
        });
    };

    showPage(1);

    jQuery("#pagin li p").click(function() {
        jQuery("#pagin li p").removeClass("current");
        jQuery(this).addClass("current");
        showPage(parseInt(jQuery(this).text()))
    });


    jQuery(document).ready(function() {

        var options = document.getElementById("formation-options");
        options.appendChild(document.getElementById("em-formation-options"));

        <?php if (!empty($video)) :?>
            var video = '<h4>Conseil de pro</h4><iframe width="560" height="315" src="<?php echo $video; ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
            jQuery('.em-category-search-module').prepend(video);
        <?php endif; ?>

    });

    document.getElementById("em-option-menu-inter").addEventListener('click', function (e) {
        e.stopPropagation();
        var intraMenu = document.getElementById("em-option-menu-intra");
        var mesureMenu = document.getElementById("em-option-menu-sur-mesure");
        var intra = document.getElementById("em-option-intra");
        var mesure = document.getElementById("em-option-sur-mesure");

        if (!$(this).classList.contains('active')) {
            if (intraMenu || mesureMenu) {
                intraMenu.classList.remove('active');
                mesureMenu.classList.remove('active');
                intra.classList.add('hide');
                mesure.classList.add('hide');
            }
            $(this).classList.add('active');
            document.getElementById("em-option-inter").classList.remove('hide');
        }
    });

    document.getElementById("em-option-menu-intra").addEventListener('click', function (e) {
        e.stopPropagation();
        var interMenu = document.getElementById("em-option-menu-inter");
        var mesureMenu = document.getElementById("em-option-menu-sur-mesure");
        var inter = document.getElementById("em-option-inter");
        var mesure = document.getElementById("em-option-sur-mesure");

        if (!$(this).classList.contains('active')) {
            if (interMenu || mesureMenu) {
                interMenu.classList.remove('active');
                mesureMenu.classList.remove('active');
                inter.classList.add('hide');
                mesure.classList.add('hide');
            }
            $(this).classList.add('active');
            document.getElementById("em-option-intra").classList.remove('hide');
        }
    });

    document.getElementById("em-option-menu-sur-mesure").addEventListener('click', function (e) {
        e.stopPropagation();
        var interMenu = document.getElementById("em-option-menu-inter");
        var intraMenu = document.getElementById("em-option-menu-intra");
        var inter = document.getElementById("em-option-inter");
        var intra = document.getElementById("em-option-intra");

        if (!$(this).classList.contains('active')) {
            if (interMenu || intraMenu) {
                interMenu.classList.remove('active');
                intraMenu.classList.remove('active');
                inter.classList.add('hide');
                intra.classList.add('hide');
            }
            $(this).classList.add('active');
            document.getElementById("em-option-sur-mesure").classList.remove('hide');
        }
    });

    function getProductPDF(code) {
        jQuery.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=files&task=getproductpdf',
            async: false,
            data: {
                product_code: code
            },
            success: function (result) {
                result = JSON.parse(result);
                if (result.status) {
                    var win = window.open(result.filename, '_blank');
                    win.focus();
                } else {
                    alert(result.msg);
                }
            },
            error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        });
    }

    <?php if (!$user->guest) :?>

    jQuery(document).ready(function () {
        jQuery("[rel=tooltip]").tooltip();
    });


    function favorite(programme_id) {
        jQuery.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=programme&task=favorite',
            data: {
                programme_id: programme_id,
                user_id: <?php echo $user->id; ?>
            },
            beforeSend: function() {
                document.getElementById('em-favorite').classList.add('fa-spin');
                setTimeout(function() {
                    document.getElementById('em-favorite').classList.remove('fa-spin');
                }, 800);
            },
            success: function(result) {
                result = JSON.parse(result);
                if (result.status) {
                    iconate(document.getElementById('em-favorite'), {
                        from: 'fa-star',
                        to: 'fa-star',
                        animation: 'tada'
                    });
                    document.getElementById('em-favorite').classList.replace('far','fas');
                    document.getElementById('em-favorite').setAttribute('onclick', 'unfavorite('+programme_id+')');
                } else {
                    document.getElementById('em-favorite').style.color = '#d91e18';
                }
            },
            error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        });
    }


    function unfavorite(programme_id) {
        jQuery.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=programme&task=unfavorite',
            data: {
                programme_id: programme_id,
                user_id: <?php echo $user->id; ?>
            },
            beforeSend: function() {
                document.getElementById('em-favorite').classList.add('fa-spin');
                setTimeout(function(){
                    document.getElementById('em-favorite').classList.remove('fa-spin');
                }, 800);
            },
            success: function(result) {
                result = JSON.parse(result);
                if (result.status) {
                    iconate(document.getElementById('em-favorite'), {
                        from: 'fa-star',
                        to: 'fa-star',
                        animation: 'tada'
                    });
                    document.getElementById('em-favorite').classList.replace('fas','far');
                    document.getElementById('em-favorite').setAttribute('onclick', 'favorite('+programme_id+')');
                } else {
                    document.getElementById('em-favorite').style.color = '#d91e18';
                }
            },
            error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        });
    }
    <?php endif; ?>

</script>

<?php
echo $this->pluginbottom;
echo $this->loadTemplate('actions');
echo '</div>';
echo $form->outro;
echo $this->pluginend;
