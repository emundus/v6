<?php
/**
 * @package	eMundus
 * @version	6.6.5
 * @author	eMundus.fr
 * @copyright (C) 2019 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

class plgEmundusCustom_event_handler extends JPlugin {

    function __construct(&$subject, $config) {
        parent::__construct($subject, $config);

        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.custom_event_handler.php'), JLog::ALL, array('com_emundus.custom_event_handler'));
    }


    /**
     * Call event handler set in the params
     *
     * @param String $event
     *
     * @return void
     */
    function callEventHandler(String $event): void {
        $params = json_decode($this->params);
        $event_handlers = json_decode($params->event_handlers);
        $events = array_keys($event_handlers->event, $event);

        foreach ($events as $caller_index) {
            try {
                eval($event_handlers->code[$caller_index]);
            } catch (ParseError $p) {
                JLog::add('Error while running event ' . $event_handlers->event[$caller_index] . ' : "' . $p->getMessage() .'"', JLog::ERROR,'com_emundus');
                continue;
            }
        }
    }
}
