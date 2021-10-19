<?php
/**
 * @package	eMundus
 * @version	6.6.5
 * @author	eMundus.fr
 * @copyright (C) 2019 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');
use Fabrik\Helpers\Worker;
class plgEmundusCustom_event_handler extends JPlugin {

    function __construct(&$subject, $config) {
        parent::__construct($subject, $config);
        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.custom_event_handler.php'), JLog::ALL, array('com_emundus.custom_event_handler'));
    }


    function callEventHandler(String $event, array $args = null): void {
        $params = json_decode($this->params);
        $event_handlers = json_decode($params->event_handlers);
        $events = array_keys($event_handlers->event, $event);

        
        foreach ($events as $caller_index) {
            try {
                $this->_runPHP($event_handlers->code[$caller_index], $args);
            } catch (ParseError $p) {
                JLog::add('Error while running event ' . $event_handlers->event[$caller_index] . ' : "' . $p->getMessage() .'"', JLog::ERROR,'com_emundus');
                continue;
            }
        }
    }

    private function _runPHP($code = '', $data = null) {

		$w = new FabrikWorker;
        $code = $w->parseMessageForPlaceHolder($code, $data);

        try {
            $php_result = eval($code);

            // Bail out if code specifically returns false
            if ($php_result === false) {
                return false;
            }
            return true;
        } catch (ParseError $p) {
            JLog::add('Error while running event ' . $code . ' : "' . $p->getMessage() .'"', JLog::ERROR,'com_emundus');
            return false;
        }
	}
}
