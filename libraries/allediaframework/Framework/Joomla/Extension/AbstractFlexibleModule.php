<?php
/**
 * @package   AllediaFramework
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC., All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Framework\Joomla\Extension;

defined('_JEXEC') or die();

use Joomla\Registry\Registry;
use JModuleHelper;

abstract class AbstractFlexibleModule extends Licensed
{
    /**
     * @var int
     */
    public $id = null;

    /**
     * @var string
     */
    public $title = null;

    /**
     * @var
     */
    public $module = null;

    /**
     * @var
     */
    public $position = null;

    /**
     * @var string
     */
    public $content = null;

    /**
     * @var bool
     */
    public $showtitle = null;

    /**
     * @var int
     */
    public $menuid = null;

    /**
     * @var string
     */
    public $name = null;

    /**
     * @var string
     */
    public $style = null;

    /**
     * @var Registry
     */
    public $params = null;

    /**
     * Class constructor that instantiate the free and pro library, if installed
     *
     * @param string $namespace Namespace
     * @param object $module    The base module, instance of stdClass
     */
    public function __construct($namespace, $module = null)
    {
        parent::__construct($namespace, 'module');

        $this->loadLibrary();

        if (is_object($module)) {
            $properties = array(
                'id',
                'title',
                'module',
                'position',
                'content',
                'showtitle',
                'menuid',
                'name',
                'style',
                'params'
            );
            foreach ($properties as $property) {
                if (isset($module->$property)) {
                    $this->$property = $module->$property;
                }
            }
            if (!$this->params instanceof Registry) {
                $this->params = new Registry($this->params);
            }
        }
    }

    /**
     * Method to initialize the module
     */
    public function init()
    {
        require JModuleHelper::getLayoutPath('mod_' . $this->element, $this->params->get('layout', 'default'));
    }
}
