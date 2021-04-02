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
//defined('_JEXEC') or die('Restricted access');
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'award.php');
$m_award = new EmundusModelAward();
$form = $this->form;

/*
$model = $this->getModel();
if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>">
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</div>
<?php
endif;

if ($this->params->get('show-title', 1)) :?>
<div class="page-header">
	<h1><?php echo $form->label;?></h1>
</div>
<?php
endif; */

//echo $form->intro;
if ($this->isMambot) :
    echo '<div class="fabrikForm fabrikDetails fabrikIsMambot" id="' . $form->formid . '">';
else :
    echo '<div class="fabrikForm fabrikDetails" id="' . $form->formid . '">';
endif;
echo $this->plugintop;
echo $this->loadTemplate('buttons');
echo $this->loadTemplate('relateddata');
$document = JFactory::getDocument();

$document->addScript('/projet/templates/emundus_vanilla/js/vyv-inner-project.js');



/*foreach ($this->groups as $group) :
	$this->group = $group;
	?>

		<div class="<?php echo $group->class; ?>" id="group<?php echo $group->id;?>" style="<?php echo $group->css;?>">

		<?php
		if ($group->showLegend) :?>
			<h3 class="legend">
				<span><?php echo $group->title;?></span>
			</h3>
		<?php endif;

		if (!empty($group->intro)) : ?>
			<div class="groupintro"><?php echo $group->intro ?></div>
		<?php
		endif;

		// Load the group template - this can be :
		//  * default_group.php - standard group non-repeating rendered as an unordered list
		//  * default_repeatgroup.php - repeat group rendered as an unordered list
		//  * default_repeatgroup_table.php - repeat group rendered in a table.

		$this->elements = $group->elements;
		echo $this->loadTemplate($group->tmpl);

		if (!empty($group->outro)) : ?>
			<div class="groupoutro"><?php echo $group->outro ?></div>
		<?php
		endif;
		?>
	</div>
<?php
endforeach;*/


$user = JFactory::getUser();
$table = $form->db_table_name;

$thematique = $this->data[$table.'___thematique_projet_raw'];
$fnum = $this->data[$table.'___fnum_raw'];

$student_id = $this->data[$table.'___user_raw'];
$cid = $m_award->getCampaignId($fnum);
$attachment_id = $m_award->getFabrikElement('visuel');
$filename = $m_award->getUpload($fnum,$cid,$attachment_id);
$countThematique = $m_award->CountThematique($user->id, $thematique);
$countVote = $m_award->CountVote($fnum,$user->id);
$VoteTotal = $m_award->CountVotes($user->id);
$uid= $user->id;

/*foreach ($this->groups as $group) :
    $this->elements = $group->elements;
    $fnum = $this->elements['fnum']->element_raw;
    $uid = $this->elements['user']->element_raw;
    $cid = $m_award->getCampaignId($fnum);


    $thematique = $this->elements['thematique_projet']->value[0];

    $filename1 = $m_award->getUpload($fnum,$cid);
    ?>
<?php endforeach;


<?= $this->elements['titre_projet']->element; ?>

*/ ?>
<input type="hidden" value="<?= $fnum ?>" id="fnum">
<div class="navbar">
    <div class="navbar-content">
        <div class="wrapper-menu-item" data-ix="menu-item-wrapper"><a href="/index.html" class="nav-link" data-ix="ia-navlink">LE CHALLENGE</a><a href="/projet/index.php?option=com_fabrik&view=list&listid=359" class="nav-link" data-ix="ia-navlink">PROJETS</a><a href="/reglement.html" class="nav-link" data-ix="ia-navlink">RÈGLEMENT</a><a href="/a-propos.html" class="nav-link margin" data-ix="ia-navlink">À PROPOS</a></div>
        <a href="#" class="burger w-inline-block" data-ix="burger">
            <div data-ix="center" class="line1 orange"></div>
            <div data-ix="center" class="line2 orange"></div>
            <div data-ix="center" class="line3 orange"></div>
        </a>
    </div>
</div>
<div class="em-wrappernavbar">
    <div class="em-navbar w-container">
        <div class="em-wrappermenu"><a href="/index.html" class="em-logonavbar w-inline-block"><img src="/projet/images/custom/Groupe_VYV_Q.png" alt="Logo Groupe VYV"></a>
            <div class="em-miniwrappermenu">
                <div class="em-wrapperitemmenu"><a href="/index.html" class="em-itemmenu">Le challenge</a><a href="/projet/index.php?option=com_fabrik&view=list&listid=359" class="em-itemmenu">PROJETS</a><a href="/reglement.html" class="em-itemmenu">règlement</a><a href="/a-propos.html" class="em-itemmenu">à propos</a></div>
            </div>
            <a href="/projet/index.php?option=com_fabrik&view=list&listid=359" class="em-button-nav w-inline-block" data-ix="arrowcta-menu">
                <div class="em-containerarrow"><img src="/projet/images/custom/5e049464ed2a2711565ccae1_arrow.svg" alt="" class="em-arrowcta-purple"><img src="/projet/images/custom/arrow.svg" alt="" class="em-arrowcta-white"></div>
                <div class="em-textcta">VOTER</div>
                <div class="em-overlay"></div>
            </a>
        </div>
    </div>
</div>
<div class="em-sectionprojet">
    <div class="em-flexproject w-row">
        <div class="em-colprojecttitle w-col w-col-6">
            <div class="em-wrapperprojecttext">
                <h2 class="em-titleprojet"><?= $this->data[$table.'___titre_projet_raw'];?></h2>
                <h3 class="em-projectcategory"><?= $this->data[$table.'___thematique_projet'];?></h3>
                <p class="em-paragrapheprojet"><?= $this->data[$table.'___description_projet_raw'];?><br></p>
            </div>
        </div>
        <div class="em-columnrightproject w-col w-col-6">
            <div class="em-backgroudhero w-clearfix">
                <div class="em-containerimageproject"></div><?= !empty($filename) ? '<img src="'.JUri::base() .'images'.DS.'emundus'.DS.'files'.DS.$student_id.DS.$filename.'" alt="Challenge solidaire" sizes="(max-width: 479px) 86vw, (max-width: 767px) 87vw, (max-width: 991px) 43vw, 36vw" class="em-imageproject">':''?></div>
        </div>
    </div>
</div>
<div class="em-sectionenjeux">
    <div class="em-wrapper-project-row2">
        <div class="em-colprojet w-row">
            <div class="em-col0 w-col em-enjeuxadroite w-col-6"></div>
            <div class="w-col w-col-6">
                <div class="em-wrapperwhitetext">
                    <h2 class="em-titlewhitecolor">Les enjeux du projet</h2>
                    <p class="em-paragraphewhitecolor"><?= $this->data[$table.'___enjeux_projet_raw'];?><br></p>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="em-vote">
    <div class="em-wrapper-project-row2">
        <div class="em-containercheckbox">
            <p class="em-question">Souhaitez-vous vous engager pour donner du temps pour ce projet ? (Bénévolat, Mécénat de compétences…)<br></p>
            <div class="w-form">
                <form id="email-form" name="email-form" data-name="Email Form" class="em-formyesno">
                    <label class="em-center w-radio">
                        <div class="w-form-formradioinput w-form-formradioinput--inputType-custom em-buttonradioyesno w-radio-input"></div>
                        <input type="radio" data-name="Radio" id="radio" name="radio" value="1" style="opacity:0;position:absolute;z-index:-1">
                        <span class="em-labelyesno w-form-label">Oui</span></label><label class="em-center w-radio">
                        <div class="w-form-formradioinput w-form-formradioinput--inputType-custom em-buttonradioyesno w-radio-input w--redirected-checked"></div>
                        <input type="radio" data-name="Radio 2" id="radio-2" name="radio" value="0" style="opacity:0;position:absolute;z-index:-1">
                        <span class="em-labelyesno w-form-label">Non</span>
                    </label>
                </form>
                <div class="w-form-done">
                    <div>Thank you! Your submission has been received!</div>
                </div>
                <div class="w-form-fail">
                    <div>Oops! Something went wrong while submitting the form.</div>
                </div>
            </div>
            <a href="#" class="em-button-finalvote w-inline-block" data-ix="arrowcta-menu-3" onclick="addVote(<?=$uid?>,<?=$thematique;?>,<?=$cid;?>,<?=$student_id;?>)">
                <div class="em-containerarrow2"><img src="/projet/images/custom/arrow.svg" alt="" class="em-arrowcta-white2"></div>
                <div class="em-textcta" >Valider et voter</div>
                <div class="em-overlay2"></div>
            </a>
            <input type="hidden" name="choiceLabel" id="choiceLabel" value="0">
        </div>
        <?php

        if($countVote == 0 && $VoteTotal < 6) {
            ?>
            <div class="em-containervotetext">
                <h2 class="em-h2vote">Vous voulez soutenir ce projet ?</h2>
                <p class="em-soutenir-le-projet">Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. <br></p>
                <a class="em-button-vote w-inline-block" data-ix="arrowcta-menu-4">
                    <div class="em-containerarrow2"><img src="/projet/images/custom/arrow.svg" alt="" class="em-arrowcta-white2"></div>
                    <div class="em-textcta">JE VOTE POUR CE PROJET</div>
                    <div class="em-overlay2"></div>
                </a>
            </div>


        <?php }

        else {  ?>
            <div class="em-cardContainer-card-vote">
                <p><?= JText::_('ALREADY_VOTE'); ?></p>
            </div>
        <?php } ?>

    </div>
</div>
<div class="em-sectionfooter">
    <div class="em-divcontainerfooter">
        <div class="em-rowfooter"><a href="/protections-des-donnees.html" class="em-protections-des-donnees">Protection des données</a><a href="/mentions-legales.html" class="em-mentionslegales">Mentions légales</a>
            <div class="div-block-2">
                <div class="em-wrappermenufooter"><a href="/index.html" class="em-menufooter">Le challenge</a><a href="/projet/index.php?option=com_fabrik&view=list&listid=349" class="em-menufooter">PROJETS</a><a href="/reglement.html" class="em-menufooter">règlement</a><a href="/a-propos.html" class="em-menufooter">à propos</a></div>
            </div>
            <div><img src="/projet/images/custom/Composite_Grpe-VYVEMV_9entites_Q-VF.jpg" alt="VYV groupe logo" srcset="/projet/images/custom/Composite_Grpe-VYVEMV_9entites_Q-VF-p-500.jpeg 500w, /projet/images/custom/Composite_Grpe-VYVEMV_9entites_Q-VF-p-800.jpeg 800w, /projet/images/custom/Composite_Grpe-VYVEMV_9entites_Q-VF.jpg 1000w" sizes="(max-width: 479px) 94vw, (max-width: 767px) 81vw, (max-width: 991px) 58vw, 63vw" class="em-logofooter"></div>
        </div>
    </div>
</div>
<script src="https://d3e54v103j8qbb.cloudfront.net/js/jquery-3.4.1.min.220afd743d.js" type="text/javascript" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>

<!-- [if lte IE 9]><script src="https://cdnjs.cloudflare.com/ajax/libs/placeholders/3.0.2/placeholders.min.js"></script><![endif] -->





<script>
    var radios = document.getElementsByName('radio');

    for (var i = 0; i < radios.length; i++) {

        radios[i].onclick = function(){
            document.getElementById('choiceLabel').value = this.value;
        }
    }


    var fnum = document.getElementById('fnum').value;

    function addVote(user, thematique, cid, sid) {
        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?option=com_emundus&controller=award&task=addvote',
            data: ({
                fnum: fnum,
                user: user,
                thematique: thematique,
                engagement: document.getElementById('choiceLabel').value,
                campaign_id: cid,
                student_id: sid
            }),
            success: function (result) {

                if (result.status) {

                    Swal.fire({
                        type: 'success',
                        title: "<?php echo JText::_('COM_EMUNDUS_VOTE_ACCEPTED'); ?>"
                    }).then((result) => {
                        window.location.href = 'index.php?option=com_fabrik&view=list&listid=359';
                    });

                } else {
                    Swal.fire({
                        type: 'error',
                        text: "<?php echo JText::_('COM_EMUNDUS_VOTE_NON_ACCEPTED'); ?>"
                    });
                }
            },
            error: function (jqXHR, textStatus) {
                console.log(textStatus);
                Swal.fire({
                    type: 'error',
                    text: "<?php echo JText::_('COM_EMUNDUS_VOTE_NON_ACCEPTED'); ?>"
                });
            }
        });
    }

</script>


