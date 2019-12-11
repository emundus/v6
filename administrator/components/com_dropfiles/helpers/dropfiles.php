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
 * @since     1.6
 */


defined('_JEXEC') || die;

/**
 * DropfilesHelper class
 */
class DropfilesHelper
{

    /**
     * A cache for the available actions.
     *
     * @var JObject
     */
    protected static $actions;


    /**
     * Configure the Linkbar.
     *
     * @param string $vName The name of the active view.
     *
     * @return void
     * @since  1.6
     */
    public static function addSubmenu($vName)
    {
//      JSubMenuHelper::addEntry(
//          JText::_('COM_MESSAGES_ADD'),
//          'index.php?option=com_messages&view=message&layout=edit',
//          $vName == 'message'
//      );
//
//      JSubMenuHelper::addEntry(
//          JText::_('COM_MESSAGES_READ'),
//          'index.php?option=com_messages',
//          $vName == 'messages'
//      );
    }


    /**
     * Gets a list of the actions that can be performed.
     *
     * @return JObject
     *
     * @since 1.6
     * @todo  Refactor to work with notes
     */
    public static function getActions()
    {
        if (empty(self::$actions)) {
            $user = JFactory::getUser();
            self::$actions = new JObject;

            $actions = JAccess::getActions('com_dropfiles');

            foreach ($actions as $action) {
                self::$actions->set($action->name, $user->authorise($action->name, 'com_dropfiles'));
            }
        }

        return self::$actions;
    }


    /**
     * Dropfiles notification send mail
     *
     * @param string $email Email address
     * @param string $title Email title
     * @param string $body  Email body
     *
     * @return void
     * @since  version
     */
    public static function sendMail($email, $title, $body)
    {
        $config    = JFactory::getConfig();
        $from_name = $config->get('fromname');
        $from_mail = $config->get('mailfrom');
        $params    = JComponentHelper::getParams('com_dropfiles');

        if ($params->get('sender_name', 'Dropfiles') !== '') {
            $from_name = $params->get('sender_name', 'Dropfiles');
        }

        if ($params->get('sender_email', '') !== '') {
            $from_mail = $params->get('sender_email', '');
        }
        JFactory::getMailer()->sendMail($from_mail, $from_name, $email, $title, $body, true);
    }


    /**
     * Get super admins
     *
     * @return array|boolean
     * @since  version
     */
    public static function getSuperAdmins()
    {
        $dbo = JFactory::getDbo();
        $query = 'SELECT user_id FROM #__user_usergroup_map as usm JOIN #__users AS us ON usm.user_id = us.id ';
        $query .= ' WHERE usm.group_id=8 AND us.sendEmail = 1';
        $dbo->setQuery($query);
        if (!$dbo->query()) {
            return false;
        }
        return $dbo->loadObjectList();
    }

    /**
     * Get Html email content
     *
     * @param string $fileName File name
     *
     * @return boolean|string
     * @since  version
     */
    public static function getHTMLEmail($fileName)
    {
        $file = JPATH_ROOT . '/administrator/components/com_dropfiles/assets/notifications/' . $fileName;
        return file_get_contents($file);
    }
}
