<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Multifactorauth.yubikey
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') || die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Multifactorauth\Yubikey\Extension\Yubikey;

return new class implements ServiceProviderInterface
{
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since 4.2.0
     */
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $config  = (array) PluginHelper::getPlugin('multifactorauth', 'yubikey');
                $subject = $container->get(DispatcherInterface::class);

                $plugin = new Yubikey($subject, $config);
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
