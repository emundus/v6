<?php
/**
 * @version   $Id: install.script.php 26747 2015-02-20 13:54:20Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

if (!class_exists('PlgSystemroknavmenu_installerInstallerScript'))
{

    class PlgSystemroknavmenu_installerInstallerScript
    {
        /**
         * List of supported versions. Newest version first!
         * @var array
         */
        protected $versions = array(
            'PHP' => array (
                '5.2' => '5.2.1',
                '0' => '5.4.30' // Preferred version
            ),
            'Joomla!' => array (
                '3.4' => '3.4.0-rc',
                '3.3' => '3.3.0',
                '3.2' => '3.2.0',
                '3.1' => '3.2.0',
                '3.0' => '3.2.0',
                '2.5' => '2.5.10',
                '0' => '3.3.6' // Preferred version
            )
        );

        /**
         * @var array
         */
        protected $packages = array();

        /**
         * @var
         */
        protected $sourcedir;

        /**
         * @var
         */
        protected $installerdir;

        /**
         * @var
         */
        protected $manifest;

        /**
         * RokInstaller
         */
        protected $parent;

        /**
         * @param $parent
         *
         * @return bool
         */
        public function install($parent)
        {
            $this->cleanBogusError();

            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.folder');
            jimport('joomla.installer.helper');

            if (!class_exists('RokInstaller'))
            {
                require_once($this->installerdir . '/RokInstaller.php');
            }

            $retval = true;
            ob_get_clean();

            // Cycle through cogs and install each
            if (count($this->manifest->cogs->children()))
            {
                foreach ($this->manifest->cogs->children() as $cog)
                {
                    $folder = $this->sourcedir . '/' . trim($cog);

                    if (is_dir($folder))
                    {
                        // if its actually a directory then fill it up
                        $package                = Array();
                        $package['dir']         = $folder;
                        $package['type']        = JInstallerHelper::detectType($folder);
                        $package['installer']   = new RokInstaller();
                        $package['name']        = (string) $cog->name;
                        $package['state']       = 'Success';
                        $package['description'] = (string) $cog->description;
                        $package['msg']         = '';
                        $package['type']        = ucfirst((string) $cog['type']);

                        $package['installer']->setCogInfo($cog);

                        // add installer to static for possible rollback.
                        $this->packages[] = $package;

                        if (!@$package['installer']->install($package['dir']))
                        {
                            while ($error = JError::getError(true))
                            {
                                $package['msg'] .= $error;
                            }

                            RokInstallerEvents::addMessage($package, RokInstallerEvents::STATUS_ERROR, $package['msg']);
                            break;
                        }

                        if ($package['installer']->getInstallType() == 'install')
                        {
                            RokInstallerEvents::addMessage($package, RokInstallerEvents::STATUS_INSTALLED);
                        }
                        else
                        {
                            RokInstallerEvents::addMessage($package, RokInstallerEvents::STATUS_UPDATED);
                        }
                    }
                    else
                    {
                        $package                = Array();
                        $package['dir']         = $folder;
                        $package['name']        = (string) $cog->name;
                        $package['state']       = 'Failed';
                        $package['description'] = (string) $cog->description;
                        $package['msg']         = '';
                        $package['type']        = ucfirst((string) $cog['type']);

                        RokInstallerEvents::addMessage(
                            $package,
                            RokInstallerEvents::STATUS_ERROR, JText::_('JLIB_INSTALLER_ABORT_NOINSTALLPATH')
                        );
                        break;
                    }
                }
            }
            else
            {
                $parent->getParent()->abort(
                    JText::sprintf(
                        'JLIB_INSTALLER_ABORT_PACK_INSTALL_NO_FILES',
                        JText::_('JLIB_INSTALLER_' . strtoupper($this->route))
                    )
                );
            }

            return $retval;
        }

        /**
         * @param $parent
         *
         * @return bool
         */
        public function update($parent)
        {
            return $this->install($parent);
        }

        /**
         * @param $type
         * @param $parent
         *
         * @return bool
         */
        public function preflight($type, $parent)
        {
            $this->setup($parent);

            //Load Event Handler.
            if (!class_exists('RokInstallerEvents'))
            {
                require_once($this->installerdir . '/RokInstallerEvents.php');

                $dispatcher = JDispatcher::getInstance();
                $plugin = new RokInstallerEvents($dispatcher);
                $plugin->setTopInstaller($this->parent->getParent());
            }

            // Check installer requirements.
            if (($requirements = $this->checkRequirements()) !== true)
            {
                RokInstallerEvents::addMessage(
                    array('name' => ''),
                    RokInstallerEvents::STATUS_ERROR,
                    implode('<br />', $requirements)
                );
                return false;
            }

            if (is_file(dirname(__FILE__) . '/requirements.php'))
            {
                // check to see if requirements are met.
                if (($loaderrors = require_once(dirname(__FILE__) . '/requirements.php')) !== true)
                {
                    $manifest = $parent->get('manifest');
                    $package['name'] = (string)$manifest->description;
                    RokInstallerEvents::addMessage($package, RokInstallerEvents::STATUS_ERROR, implode('<br />', $loaderrors));

                    return false;
                }
            }
        }

        /**
         * @param $type
         * @param $parent
         */
        public function postflight($type, $parent)
        {
            $conf = JFactory::getConfig();
            $conf->set('debug', false);
            $parent->getParent()->abort();
        }

        /**
         * @param null $msg
         * @param null $type
         */
        public function abort($msg = null, $type = null)
        {
            if ($msg)
            {
                JError::raiseWarning(100, $msg);
            }
            foreach ($this->packages as $package)
            {
                $package['installer']->abort(null, $type);
            }
        }

        /**
         * @param $parent
         */
        protected function setup($parent)
        {
            $this->parent       = $parent;
            $this->sourcedir    = $parent->getParent()->getPath('source');
            $this->manifest     = $parent->getParent()->getManifest();
            $this->installerdir = $this->sourcedir . '/installer';
        }

        protected function checkRequirements()
        {
            $errors = array();

            if (($error = $this->checkVersion('PHP', phpversion())) !== true)
            {
                $errors[] = $error;
            }

            if (($error = $this->checkVersion('Joomla!', JVERSION)) !== true)
            {
                $errors[] = $error;
            }

            return $errors ? $errors : true;
        }

        protected function checkVersion($name, $version)
        {
            $major = $minor = 0;
            foreach ($this->versions[$name] as $major => $minor)
            {
                if (!$major || version_compare($version, $major, '<'))
                {
                    continue;
                }

                if (version_compare($version, $minor, '>='))
                {
                    return true;
                }
                break;
            }

            if (!$major)
            {
                $minor = reset($this->versions[$name]);
            }

            $recommended = end($this->versions[$name]);

            if (version_compare($recommended, $minor, '>'))
            {
                return sprintf(
                    '%s %s is not supported. Minimum required version is %s %s, but it is highly recommended to use %s %s or later version.',
                    $name,
                    $version,
                    $name,
                    $minor,
                    $name,
                    $recommended
                );
            }
            else
            {
                return sprintf(
                    '%s %s is not supported. Please update to %s %s or later version.',
                    $name,
                    $version,
                    $name,
                    $minor
                );
            }
        }

        protected function cleanBogusError()
        {
            $errors = array();

            while (($error = JError::getError(true)) !== false)
            {
                if (!($error->get('code') == 1 && $error->get('level') == 2 && $error->get('message') == JText::_('JLIB_INSTALLER_ERROR_NOTFINDXMLSETUPFILE')))
                {
                    $errors[] = $error;
                }
            }

            foreach ($errors as $error)
            {
                JError::addToStack($error);
            }

            $app               = new RokInstallerJAdministratorWrapper(JFactory::getApplication());
            $enqueued_messages = $app->getMessageQueue();
            $other_messages    = array();

            if (!empty($enqueued_messages) && is_array($enqueued_messages))
            {
                foreach ($enqueued_messages as $enqueued_message)
                {
                    if (!($enqueued_message['message'] == JText::_('JLIB_INSTALLER_ERROR_NOTFINDXMLSETUPFILE') && $enqueued_message['type']) == 'error')
                    {
                        $other_messages[] = $enqueued_message;
                    }
                }
            }

            $app->setMessageQueue($other_messages);
        }
    }

    if (!class_exists('RokInstallerJAdministratorWrapper'))
    {
        if (version_compare(JVERSION, '3.2', '>'))
        {
            class RokInstallerJAdministratorWrapper extends JApplicationCms
            {
                protected $app;

                public function __construct(JApplicationCms $app)
                {
                    $this->app = $app;
                }

                public function getMessageQueue()
                {
                    return $this->app->getMessageQueue();
                }

                public function setMessageQueue($messages)
                {
                    $this->app->_messageQueue = $messages;
                }
            }
        }
        else
        {
            class RokInstallerJAdministratorWrapper extends JAdministrator
            {
                protected $app;

                public function __construct(JAdministrator $app)
                {
                    $this->app = $app;
                }

                public function getMessageQueue()
                {
                    return $this->app->getMessageQueue();
                }

                public function setMessageQueue($messages)
                {
                    $this->app->_messageQueue = $messages;
                }
            }
        }
    }
}
