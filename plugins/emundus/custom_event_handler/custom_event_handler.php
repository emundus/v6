<?php
/**
 * @package       eMundus
 * @version       6.6.5
 * @author        eMundus.fr
 * @copyright (C) 2019 eMundus SOFTWARE. All rights reserved.
 * @license       GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Worker;

class plgEmundusCustom_event_handler extends JPlugin
{

	private $hEvents = null;

	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		jimport('joomla.log.log');
		JLog::addLogger(array('text_file' => 'com_emundus.custom_event_handler.php'), JLog::ALL, array('com_emundus.custom_event_handler'));

		require_once(JPATH_SITE . '/components/com_emundus/helpers/events.php');
		$this->hEvents = new EmundusHelperEvents();
	}


	function onCallEventHandler(string $event, array $args = null): array
	{
		$events = [];
		$codes  = [];
		$params = json_decode($this->params);

		if (!empty($params) && !empty($params->event_handlers)) {
			foreach ($params->event_handlers as $event_handler) {
				if ($event_handler->event == $event && $event_handler->published) {
					$events[] = $event_handler->event;
					$codes[]  = $event_handler->code;
				}
			}
		}

		$returned_values = [];

		if (method_exists($this->hEvents, $event)) {
			$this->hEvents->{$event}($args);
		}

		foreach ($events as $index => $caller_index) {
			try {
				$returned_values[$caller_index] = $this->_runPHP($codes[$index], $args);
			}
			catch (ParseError $p) {
				JLog::add('Error while running event ' . $caller_index . ' : "' . $p->getMessage() . '"', JLog::ERROR, 'com_emundus');
				continue;
			}
		}

		return $returned_values;
	}

	private function _runPHP($code = '', $data = null)
	{
		if (class_exists('FabrikWorker')) {
			$w    = new FabrikWorker;
			$code = $w->parseMessageForPlaceHolder($code, $data);

			try {
				$php_result = eval($code);

				// Bail out if code specifically returns false
				if ($php_result === false) {
					return false;
				}

				return $php_result;
			}
			catch (ParseError $p) {
				JLog::add('Error while running event ' . $code . ' : "' . $p->getMessage() . '"', JLog::ERROR, 'com_emundus');

				return false;
			}
		}

		return true;
	}
}
