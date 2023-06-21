<?php
/**
 * Bootstrap Details Template
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$form  = $this->form;
$model = $this->getModel();

if ($this->params->get('show_page_heading', 1)) : ?>
    <div class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>">
		<?php echo $this->escape($this->params->get('page_heading')); ?>
    </div>
<?php
endif;

?>

<div id="fabrikDetailsContainer_<?php echo $form->id ?>">
	<?php if ($this->params->get('show-title', 1)) : ?>
        <div class="page-header em-mb-12 em-flex-row em-flex-space-between">
            <h1><?php echo $form->label; ?></h1>

			<?php if (!empty($model->data['rowid'])) : ?>
                <button class="em-secondary-button em-w-auto">
                    <a target="_blank"
                       href="index.php?option=com_fabrik&view=form&formid=<?php echo $form->id ?>&rowid=<?php echo $model->data['rowid'] ?>">
						<?php echo JText::_('COM_FABRIK_EDIT'); ?>
                    </a>
                </button>
			<?php endif; ?>
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
	foreach ($this->groups as $group) :
		$this->group = $group;
		?>

        <div class="em-mt-16 <?php echo $group->class; ?>" id="group<?php echo $group->id; ?>"
             style="<?php echo $group->css; ?>">

			<?php
			if ($group->showLegend) :?>
                <h3 class="legend em-mb-8">
                    <span><?php echo $group->title; ?></span>
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
	endforeach;

	echo $this->pluginbottom;
	echo $this->loadTemplate('actions');
	echo '</div>';
	echo $form->outro;
	echo $this->pluginend; ?>
</div>
