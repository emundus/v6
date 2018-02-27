<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Form;
use CCL\Content\Element\Basic\Form\Label;
use CCL\Content\Element\Basic\Form\Select;
use CCL\Content\Element\Basic\Heading;
use CCL\Content\Element\Basic\Container;

// Check if sharing is activated
if (!$this->params->get('profile_show_sharing', '1'))
{
	return;
}

// Load the needed JS libraries
DPCalendarHelper::loadLibrary(array('jquery' => true, 'chosen' => true, 'dpcalendar' => true));
JHtml::_('script', 'com_dpcalendar/dpcalendar/views/profile/default.js', ['relative' => true], ['defer' => true]);

// Text when select box is empty
JText::script('COM_DPCALENDAR_VIEW_DAVCALENDAR_NONE_SELECTED_LABEL');

// The sharing heading
$root = $this->root->addChild(new Container('share'));
$root->addChild(new Heading('heading', 3))->setContent(JText::_('COM_DPCALENDAR_VIEW_PROFILE_SHARING'));

// The form element
$form = $this->root->addChild(
	new Form(
		'form',
		JRoute::_('index.php?option=com_dpcalendar&view=profile' . $itemId),
		'adminForm',
		'POST',
		array('form-validate')
	)
);

// Set up the read container
$c = $form->addChild(new Container('read'));

// Create the label
$l = $c->addChild(new Label('label', $c->getId() . '-users'));
$l->setContent(JText::_('COM_DPCALENDAR_VIEW_PROFILE_READ_USERS_LABEL'));

// Set up the select box with the available users
$select = $c->addChild(new Select('users', 'read-users', true));
foreach ($this->users as $user)
{
	// Add the option
	$select->addOption($user->text, $user->value, in_array($user, $this->readMembers));
}

// Set up the write container
$c = $form->addChild(new Container('write'));

// Create the label
$l = $c->addChild(new Label('label', $c->getId() . '-users'));
$l->setContent(JText::_('COM_DPCALENDAR_VIEW_PROFILE_WRITE_USERS_LABEL'));

// Set up the select box with the available users
$select = $c->addChild(new Select('users', 'write-users', true));
foreach ($this->users as $user)
{
	// Add the option
	$select->addOption($user->text, $user->value, in_array($user, $this->writeMembers));
}
