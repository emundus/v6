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

foreach ($this->groups as $group) :
    $this->elements = $group->elements;
    $filename1 = $m_award->getUpload($this->elements['fnum']->element,$this->elements['attachment_id']->element);
    $filename2 = $m_award->getUpload($this->elements['fnum']->element,$this->elements['attachment_id_2']->element);
    ?>

<section class="award">
    <div class="em-cardContainer">
        <div class="em-cardContainer-card">
            <div class="em-cardContainer-card-image">

                <img src="<?= JUri::base() .'images'.DS.'emundus'.DS.'files'.DS.$uid.DS.$this->picture; ?>" alt="">
            </div>
            <div class="em-cardContainer-card-content">
                <h1><?= $this->elements['titre']->element; ?></h1>
<p><?= $this->elements['description']->element; ?></p>

</div>
<div class="em-cardContainer-card-galerie">
    <?php foreach ($this->pictureSecondary as $image){ ?>
        <img src="<?= JUri::base() .'images'.DS.'emundus'.DS.'files'.DS.$uid.DS.$image; ?>" alt="">
    <?php } ?>
</div>
<div class="em-cardContainer-card-vote">
    <hr>
    <p id="em-vote">Votez: <?= $this->nb_vote ;?></p>
    <div class="em-cardContainer-card-vote-button">
        <a onclick="addVote('<?= $fnum;?>')">YES <i class="fas fa-thumbs-up"></i></a>
        <a onclick="deleteVote('<?= $fnum;?>')">NO <i class="fas fa-thumbs-down"></i></a>
    </div>
</div>
</div>
</div>
</section>
<?php endforeach; ?>
<script>
    var button = document.querySelector('.em-cardContainer-card-vote-button a');
    button.addEventListener('click', () => {button.classList.add("active")});

    function addVote(fnum) {
        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?option=com_emundus&controller=award&task=addvote',
            data: ({
                fnum: fnum
            }),
            success: function (result) {

                if (result.status) {

                    Swal.fire({
                        type: 'success',
                        title: "<?php echo JText::_('COM_EMUNDUS_VOTE_ACCEPTED'); ?>"
                    });
                    jQuery('#em-vote').html('Votez: '+result.nb_vote);
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
    function deleteVote(fnum) {
        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?option=com_emundus&controller=award&task=deletevote',
            data: ({
                fnum: fnum
            }),
            success: function (result) {
                if (result.status) {

                    Swal.fire({
                        type: 'success',
                        title: "<?php echo JText::_('COM_EMUNDUS_VOTE_ACCEPTED'); ?>"
                    });
                    jQuery('#em-vote').html('Votez: '+result.nb_vote);
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


