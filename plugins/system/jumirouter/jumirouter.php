<?php
/**
* @version   $Id$
* @package   Jumi
* @copyright (C) 2008 - 2011 Edvard Ananyan
* @license   GNU/GPL v3 http://www.gnu.org/licenses/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.plugin.plugin' );

/**
 * JumiRouter plugin
 *
 */
class  plgSystemJumiRouter extends JPlugin {
    function __construct(& $subject, $config) {
        // check to see if we are on frontend to execute plugin
        $mainframe = JFactory::getApplication();
        if($mainframe->isAdmin())
            return;

        parent::__construct($subject, $config);
    }

    /**
     * Routes URLs
     *
     * @access public
     */
    function onAfterInitialise() {
        $mainframe = JFactory::getApplication();

        $uri    = JURI::getInstance();
        $router = $mainframe->getRouter();

        $router->attachParseRule('parseJumiRouter');

    }
}

/**
 * SEF url parser
 *
 * @access public
 * @static
 * @param $router object of JRouter class
 * @param $uri object of JURI class
 */
function parseJumiRouter(& $router, & $uri) {
    if($router->getMode() == JROUTER_MODE_RAW)
        return array();

    $db = JFactory::getDBO();
    $db->setQuery('select id, title, alias from #__jumi where published = 1');
    $apps = $db->loadRowList();
    $alias = array();
    foreach($apps as $i=>$app) {
        if(empty($app[2]))
            $apps[$i][2] = JFilterOutput::stringURLSafe($app[1]);
        $alias[$i] = $apps[$i][2];
    }

    $segments = explode('/', $uri->getPath());
    foreach($segments as $i => $segment)
        if(($j = array_search($segment, $alias)) !== false) {
            unset($segments[$i]);
            $uri->setVar('option', 'com_jumi');
            $uri->setVar('fileid', $apps[$j][0]);
        }

    $uri->setPath(implode('/', $segments));

    return array();
}