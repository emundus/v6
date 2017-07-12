<?php
/**
 * @package   AdminTools
 * @copyright 2010-2017 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Form\Field;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Model\AutoBannedAddresses;
use FOF30\Date\Date;
use FOF30\Form\Field\Text;

class LocalisedDate extends Text
{
	public function getRepeatable()
	{
		static $tz = null;
		$container = $this->form->getContainer();

		if (is_null($tz))
		{
			$timezone = $container->platform->getUser()->getParam('timezone', $container->platform->getConfig()->get('offset', 'GMT'));
			$tz = new \DateTimeZone($timezone);
		}

		$date      = $container->platform->getDate($this->value, 'UTC');
		$date->setTimezone($tz);
		
		$format = isset($this->element['format']) ? $this->element['format'] : 'DATE_FORMAT_LC2';
		$localise = isset($this->element['localise']) ? $this->element['localise'] :true;
		$localise = in_array($localise, array('1', 'true', 'on', 'yes'));
		$localTZ = isset($this->element['local_timezone']) ? $this->element['local_timezone'] :true;
		$localTZ = in_array($localTZ, array('1', 'true', 'on', 'yes'));

		if ($localise)
		{
			$format = \JText::_($format);
		}

		return $date->format($format, $localTZ);
	}
}