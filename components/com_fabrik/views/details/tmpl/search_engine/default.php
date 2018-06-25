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

$user = JFactory::getUser();
if ($user->guest) {
	JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode(JFactory::getURI())), JText::_('JGLOBAL_YOU_MUST_LOGIN_FIRST'), 'warning');
	return;
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
foreach ($this->groups as $group) :
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
endforeach;

// This is currently the only way of getting the fnum.
// CHANGING THE NAME OF THE GROUP 'search engine data' WILL BREAK THIS LOGGING.
$fnum = $this->groups['search engine data']->elements['fnum']->value;

// Log the action of opening the persons form.
require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
EmundusModelLogs::log($user->id, (int)substr($fnum,-7), $fnum, 33, 'r', 'COM_EMUNDUS_LOGS_OPEN_OFFER');

echo $this->pluginbottom;
echo $this->loadTemplate('actions');
echo '</div>';
echo $form->outro;
echo $this->pluginend;
