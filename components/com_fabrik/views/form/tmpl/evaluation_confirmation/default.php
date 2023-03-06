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

$doc = JFactory::getDocument();
$doc->addStyleSheet( 'media/com_emundus/lib/bootstrap-232/css/bootstrap.min.css' );

$form = $this->form;
$model = $this->getModel();
$groupTmpl = $model->editable ? 'group' : 'group_details';
$active = ($form->error != '') ? '' : ' fabrikHide';

?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<?php

if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="componentheading= $this->params->get('pageclass_sfx')?>">
		<?= $this->escape($this->params->get('page_heading')); ?>
	</div>
<?php
endif;

if ($this->params->get('show-title', 1)) :?>
<div class="page-header">
	<h1><?php $title = explode(' - ', $form->label); echo !empty($title[1])?JText::_(trim($title[1])):JText::_(trim($title[0])); ?></h1>
</div>
<?php
endif;

echo $form->intro;
?>
<form method="post" <?= $form->attribs?>>
<?= $this->plugintop; ?>

<div class="fabrikMainError alert alert-error fabrikError<?= $active?>">
	<button class="close" data-dismiss="alert">×</button>
	<?= $form->error; ?>
</div>

<div class="row-fluid nav">
	<div class="<?= FabrikHelperHTML::getGridSpan(6); ?> pull-right">
		<?= $this->loadTemplate('buttons'); ?>
	</div>
	<div class="<?= FabrikHelperHTML::getGridSpan(6); ?>">
		<?= $this->loadTemplate('relateddata'); ?>
	</div>
</div>

<?php
foreach ($this->groups as $group) :
	$this->group = $group;
	?>

	<fieldset class="<?= $group->class; ?>" id="group<?= $group->id;?>" style="<?= $group->css;?>">
		<?php
		if ($group->showLegend) :?>
			<legend class="legend"><?= $group->title;?></legend>
		<?php
		endif;

		if (!empty($group->intro)) : ?>
			<div class="groupintro"><?= $group->intro ?></div>
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
			<div class="groupoutro"><?= $group->outro ?></div>
		<?php endif; ?>
	</fieldset>
<?php
endforeach;
if ($model->editable) : ?>
<div class="fabrikHiddenFields">
	<?= $this->hiddenFields; ?>
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
