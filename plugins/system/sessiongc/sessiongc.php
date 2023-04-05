<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.sessiongc
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\MetadataManager;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Garbage collection handler for session related data
 *
 * @since  3.8.6
 */
class PlgSystemSessionGc extends CMSPlugin
{
    /**
     * Application object
     *
     * @var    CMSApplication
     * @since  3.8.6
     */
    protected $app;

    /**
     * Database driver
     *
     * @var    \Joomla\Database\DatabaseDriver
     * @since  3.8.6
     */
    protected $db;

    /**
     * Runs after the HTTP response has been sent to the client and performs garbage collection tasks
     *
     * @return  void
     *
     * @since   3.8.6
     */
    public function onAfterRespond()
    {
        if ($this->params->get('enable_session_gc', 1)) {
            $probability = $this->params->get('gc_probability', 1);
            $divisor     = $this->params->get('gc_divisor', 100);

            $random = $divisor * lcg_value();

            if ($probability > 0 && $random < $probability) {
                $this->app->getSession()->gc();
            }
        }

        if ($this->app->get('session_handler', 'none') !== 'database' && $this->params->get('enable_session_metadata_gc', 1)) {
            $probability = $this->params->get('gc_probability', 1);
            $divisor     = $this->params->get('gc_divisor', 100);

            $random = $divisor * lcg_value();

            if ($probability > 0 && $random < $probability) {
                /** @var MetadataManager $metadataManager */
                $metadataManager = Factory::getContainer()->get(MetadataManager::class);
                $metadataManager->deletePriorTo(time() - $this->app->getSession()->getExpire());
            }
        }
    }
}
