<?php
/**
 * @package   AllediaFramework
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC., All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Framework\Joomla\View;

defined('_JEXEC') or die();

use Alledia\Framework\Factory;
use Alledia\Framework\Joomla\Extension\Helper as ExtensionHelper;
use JFile;

class Admin extends Base
{
    protected $option;

    protected $extension;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $app          = Factory::getApplication();
        $this->option = $app->input->get('option');

        $info            = ExtensionHelper::getExtensionInfoFromElement($this->option);
        $this->extension = Factory::getExtension($info['namespace'], $info['type']);
    }

    public function display($tpl = null)
    {
        // Add default admin CSS
        $cssPath = JPATH_SITE . "/media/{$this->option}/css/admin-default.css";
        if (file_exists($cssPath)) {
            $doc = Factory::getDocument();
            $doc->addStyleSheet($cssPath);
        }

        parent::display($tpl);

        $this->displayFooter();
    }

    protected function displayFooter()
    {
        $output = '';

        $layoutPath = $this->extension->getExtensionPath() . '/views/footer/tmpl/default.php';
        if (!JFile::exists($layoutPath)) {
            $layoutPath = $this->extension->getExtensionPath() . '/alledia_views/footer/tmpl/default.php';

            if (!JFile::exists($layoutPath)) {
                $layoutPath = null;
            }
        }

        if (!is_null($layoutPath)) {
            // Start capturing output into a buffer
            ob_start();

            // Include the requested template filename in the local scope
            // (this will execute the view logic).
            include $layoutPath;

            // Done with the requested template; get the buffer and
            // clear it.
            $output = ob_get_contents();
            ob_end_clean();
        }

        echo $output;
    }
}
