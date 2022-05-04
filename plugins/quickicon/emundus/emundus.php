<?php

/**
 * @package   eMundus
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2021 RocketTheme, LLC
 * @license   GNU/GPLv2 and later
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die;

use Gantry\Component\Filesystem\Streams;
use Gantry\Framework\Gantry;
use Gantry\Framework\Platform;
use Gantry5\Loader;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Event\DispatcherInterface;

// Quick check to prevent fatal error in unsupported Joomla admin.
if (!class_exists(CMSPlugin::class)) {
    return;
}

/**
 * Class plgQuickiconGantry5
 */
class plgQuickiconEmundus extends CMSPlugin
{
    /** @var CMSApplication */
    protected $app;

    /**
     * plgQuickiconGantry5 constructor.
     * @param DispatcherInterface $subject
     * @param array $config
     */
    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        // Get the application if not done by JPlugin. This may happen during upgrades from Joomla 2.5.
        if (!$this->app) {
            $this->app = Factory::getApplication();
        }

        // Always load language.
        $language = $this->app->getLanguage();

        $language->load('com_emundus.sys')
        || $language->load('com_emundus.sys', JPATH_ADMINISTRATOR . '/components/com_emundus');

        $this->loadLanguage('plg_quickicon_emundus.sys');
    }

    /**
     * Display Gantry 5 backend icon
     *
     * @param string $context
     * @return array|null
     */
    public function onGetIcons($context)
    {
        $user = $this->app->getIdentity();

        if ($context !== $this->params->get('context', 'mod_quickicon')
            || !$user || !$user->authorise('core.manage', 'com_emundus')) {
            return null;
        }

        return array(
            array(
                'link' => Route::_('index.php?option=com_config&view=component&component=com_emundus'),
                'image' => 'em-tchooz',
                'text' => 'eMundus',
                'group' => 'MOD_QUICKICON_EXTENSIONS',
                'access' => array('core.admin', 'com_emundus')
            )
        );
    }
}
