<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

//-- No direct access
defined('_JEXEC') || die('=;)');
use Joomla\CMS\Router\Router;

/**
 * Content Plugin.
 */
class PlgSystemDropfiles extends JPlugin
{

    /**
     * After Initialise
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function onAfterInitialise()
    {
        $app = JFactory::getApplication();
        // get the router
        if ($app->isClient('site')) {
            $router = $app->getRouter();
            $router->attachParseRule(array($this, 'replaceRoute'), Router::PROCESS_BEFORE);
        }
    }

    /**
     * Replace route
     *
     * @param array $router Route string
     * @param JURI  $uri    The URI to parse
     *
     * @return array $vars The array of processed URI variables
     *
     * @internal param JRouterSite $router The Joomla Site Router
     *
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function replaceRoute(&$router, &$uri)
    {
        $array              = array();
        $params             = JComponentHelper::getParams('com_dropfiles');
        $dropfilesUri       = $params->get('uri', 'files');
        $dropfilesUriSegs   = count(explode('/', $dropfilesUri));
        $dropfilesSegs      = explode('/', $dropfilesUri);
        $path               = explode('/', $uri->getPath());
        $totalDropfilesSegs = count($dropfilesSegs);
        if ($totalDropfilesSegs < count($path)) {
            for ($index = $dropfilesUriSegs - 1; $index < $totalDropfilesSegs; $index++) {
                if ($dropfilesSegs[$index] !== $path[$index]) {
                    return $array;
                }
            }
            if (!isset($path[1]) || $path[1] === '') {
                return $array;
            }
            $uri->setVar('option', 'com_dropfiles');
            $uri->setVar('format', '');
            $uri->setVar('task', 'frontfile.download');
            $uri->setVar('catid', $path[$dropfilesUriSegs]);
            $uri->setVar('id', $path[$dropfilesUriSegs + 2]);
            $uri->setVar('Itemid', 1000000000000);
            $app = JFactory::getApplication();
            $app->redirect($uri->base() . 'index.php?' . $uri->getQuery());
        }
        return $array;
    }
}
