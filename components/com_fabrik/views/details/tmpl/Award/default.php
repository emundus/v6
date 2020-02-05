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
endif;

echo $form->intro;
if ($this->isMambot) :
	echo '<div class="fabrikForm fabrikDetails fabrikIsMambot" id="' . $form->formid . '">';
else :
	echo '<div class="fabrikForm fabrikDetails" id="' . $form->formid . '">';
endif;
echo $this->plugintop;
echo $this->loadTemplate('buttons');
echo $this->loadTemplate('relateddata');


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
$cid = $m_award->getCampaignId($fnum);
$filename1 = $m_award->getUpload($fnum,$cid);
$countThematique = $m_award->CountThematique($user->id, $thematique);
$countVote = $m_award->CountVote($fnum,$user->id);

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

<section class="award">
    <div class="em-cardContainer">
        <div class="em-cardContainer-card">
            <div class="em-cardContainer-card-image">

                <img src="<?= JUri::base() .'images'.DS.'emundus'.DS.'files'.DS.$uid.DS.$filename1; ?>" alt="">
            </div>
            <div class="em-cardContainer-card-content">
                <h1><?= $this->data[$table.'___titre_projet']; ?></h1>
                <p><?= $this->data[$table.'___description_projet']; ?></p>
            </div>
    <?php
    if ($countThematique == 0) {
    if($countVote == 0) {
         ?>
            <div class="em-cardContainer-card-vote">
                <div class="em-engagement">
                    <div class="fabrikgrid_radio span2">
                        <label for="engagement" class="radio">
                        <input type="checkbox" onClick="checkboxEngagement()" class="fabrik" id="engagement" name="engagement" value="1">
                        <?= JText::_('ENGAGEMENT'); ?></label>

                        <label for="engagement_financier" class="radio">
                        <input type="checkbox" class="fabrik" onClick="checkboxEngagementFinancier()" id="engagement_financier" name="engagement_financier" value="2">
                        <?= JText::_('ENGAGEMENT_FINANCIER'); ?></label>

                        <label for="engagement_materiel" class="radio">
                        <input type="checkbox" class="fabrik" onClick="checkboxEngagementMateriel()" id="engagement_materiel"  name="engagement_materiel" value="3">
                        <?= JText::_('ENGAGEMENT_MATERIEL'); ?></label>
                    </div>
                </div>


                <a class="btn" onclick="addVote('<?= $fnum; ?>','<?= $user->id; ?>','<?= $thematique; ?>')" ><?= JText::_('VOTE'); ?></a>
            </div>
        <?php }
    }
    else {  ?>
    <div class="em-cardContainer-card-vote">
        <p><?= JText::_('ALREADY_VOTE'); ?></p>
    </div>
    <?php } ?>

</div>
</div>
</section>



<script>


    var engagement='';
    var engagement_financier='';
    var engagement_materiel ='';

    function checkboxEngagement() {
        if (document.getElementById('engagement').checked == true) {
            document.getElementById('engagement').value = true;
        } else {
            document.getElementById('engagement').value = false;
        }
         return engagement = document.getElementById('engagement').value;
    }
    function checkboxEngagementFinancier() {
        if (document.getElementById('engagement_financier').checked == true) {
            document.getElementById('engagement_financier').value = true;
        } else {
            document.getElementById('engagement_financier').value = false;
        }
        return engagement_financier = document.getElementById('engagement_financier').value;
    }
    function checkboxEngagementMateriel() {
        if (document.getElementById('engagement_materiel').checked == true) {
            document.getElementById('engagement_materiel').value = true;
        } else {
            document.getElementById('engagement_materiel').value = false;
        }
         return engagement_materiel = document.getElementById('engagement_materiel').value;
    }

    function addVote(fnum, user, thematique) {

        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?option=com_emundus&controller=award&task=addvote',
            data: ({
                fnum: fnum,
                user: user,
                thematique: thematique,
                engagement: engagement,
                engagement_financier: engagement_financier,
                engagement_materiel: engagement_materiel
            }),
            success: function (result) {

                if (result.status) {

                    Swal.fire({
                        type: 'success',
                        title: "<?php echo JText::_('COM_EMUNDUS_VOTE_ACCEPTED'); ?>"
                    }).then((result) => {
                        window.location.href="index.php";
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


