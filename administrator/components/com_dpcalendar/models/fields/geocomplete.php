<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JFormHelper::loadFieldClass('text');

class JFormFieldGeocomplete extends JFormFieldText
{

	protected $type = 'Geocomplete';

	public function getInput()
	{
		$document = JFactory::getDocument();
		$document->addScript(JURI::root() . 'components/com_dpcalendar/libraries/geocomplete/geocomplete.js');

		$input = parent::getInput();

		$input .= '<button id="' . $this->id . '_find" class="btn hasTooltip" type="button" title="' . JText::_('JSEARCH_FILTER_SUBMIT') .
				 '"><i class="icon-search"></i></button>';

		return $input;
	}
}
