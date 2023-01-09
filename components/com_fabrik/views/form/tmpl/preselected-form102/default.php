<?php
/**
 * Bootstrap Form Template
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
$doc = JFactory::getDocument();
$doc->addStyleSheet( 'media/com_emundus/lib/bootstrap-232/css/bootstrap.min.css' );

// Get campaign ID and course from url
$jinput = JFactory::getApplication()->input;

$campaign = $jinput->get->get('cid');
$course   = $jinput->get->get('course');

$m_campain =  new EmundusModelCampaign();
$myCamps = $m_campain->getMyCampaign();

foreach ($myCamps as $camp) {
    if($camp->id == $campaign) {
        JFactory::getApplication()->enqueueMessage(JText::_('ALREADY_APPLIED_TO_PROGRAM'), 'warning');
        JFactory::getApplication()->redirect(JRoute::_('/'));
    }
}




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

if ($this->params->get('show-title', 1)) :?>
<div class="page-header">
	<h1><?php $title = explode(' - ', $form->label); echo !empty($title[1])?$title[1]:$title[0]; ?></h1>
</div>
<?php
endif;

echo $form->intro;
?>
<form method="post" <?php echo $form->attribs?>>
<?php
echo $this->plugintop;
?>

<div class="fabrikMainError alert alert-error fabrikError<?php echo $active?>">
	<button class="close" data-dismiss="alert">×</button>
	<?php echo $form->error; ?>
</div>

<div class="row-fluid nav">
	<div class="<?php echo FabrikHelperHTML::getGridSpan(6); ?> pull-right">
		<?php
		echo $this->loadTemplate('buttons');
		?>
	</div>
	<div class="<?php echo FabrikHelperHTML::getGridSpan(6); ?>">
		<?php
		echo $this->loadTemplate('relateddata');
		?>
	</div>
</div>

<?php
foreach ($this->groups as $group) :
	$this->group = $group;
	?>

	<fieldset class="<?php echo $group->class; ?>" id="group<?php echo $group->id;?>" style="<?php echo $group->css;?>">
		<?php
		if ($group->showLegend) :?>
			<legend class="legend"><?php echo $group->title;?></legend>
		<?php
		endif;

		if (!empty($group->intro)) : ?>
			<div class="groupintro"><?php echo $group->intro ?></div>
		<?php
		endif;

		/* Load the group template - this can be :
		 *  * default_group.php - standard group non-repeating rendered as an unordered list
		 *  * default_repeatgroup.php - repeat group rendered as an unordered list
		 *  * default_repeatgroup_table.php - repeat group rendered in a table.
		 */
		$this->elements = $group->elements;
		echo $this->loadTemplate($group->tmpl);

		if (!empty($group->outro)) : ?>
			<div class="groupoutro"><?php echo $group->outro ?></div>
		<?php
		endif;
	?>
	</fieldset>
<?php
endforeach;
if ($model->editable) : ?>
<div class="fabrikHiddenFields">
	<?php echo $this->hiddenFields; ?>
</div>
<?php
endif;

echo $this->pluginbottom;
echo $this->loadTemplate('actions');
?>
</form>
<?php
echo $form->outro;
echo $this->pluginend;
echo FabrikHelperHTML::keepalive();

?>
<script type="text/javascript">
    jQuery(document).ready(function() {

        var courseInURL = "<?php echo (isset($course)) ? 'true' : 'null'; ?>";
        var cidInUrl 	= "<?php echo (isset($campaign)) ? 'true' : 'null' ?>";

        if (courseInURL == 'true' && cidInUrl == 'true') {
            var campaign = document.getElementById('jos_emundus_campaign_candidature___campaign_id');
            if (campaign.options.length > 0) {
                var cText = campaign.options[1].text;
                campaign.selectedIndex = 1;
                campaign.style.display = 'none';

                var newItem = document.createElement("p");       // Create a <li> node
                var textnode = document.createTextNode(cText);  // Create a text node
                newItem.appendChild(textnode);

                campaign.parentNode.insertBefore(newItem, campaign);
            }
        }

        var campaign_id = "<?php echo $campaign ?>";
        var campaign = document.getElementById('jos_emundus_campaign_candidature___campaign_id');
        if (campaign_id != "") {
            for (var i=0 ; i<campaign.options.length ; ++i) {
                if(campaign.options[i].value == campaign_id) {
                    campaign.options[i].selected=true;
                }
            }
        } else {
            campaign.options[0].selected=true;
        }



    });
</script>