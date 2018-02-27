<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Form\Input;
use CCL\Content\Element\Basic\Container;
use CCL\Content\Element\Basic\Paragraph;
use CCL\Content\Element\Basic\Element;

// The params
$params  = $this->params;

// The limit input
$this->root->addChild(new Input('limitstart', 'hidden', 'limitstart'));

// The pagination container
$c = $this->root->addChild(new Container('limitstart', array('pagination', 'noprint')));
$c->setProtectedClass('pagination');
$c->setProtectedClass('noprint');

$c->addChild(new Paragraph('counter'))->setContent($this->pagination->getPagesCounter());
$c->addChild(new Element('links'))->setContent($this->pagination->getPagesLinks());
