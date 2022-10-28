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
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Component\ComponentHelper;

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
            // $actions = JAccess::getActions('com_dropfiles');
            self::$actions = ContentHelper::getActions('com_dropfiles', 'category');
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
        if (!$dbo->execute()) {
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

    /**
     * Ordering multi category files
     *
     * @param array  $files     Files List
     * @param string $ordering  Ordering
     * @param string $direction Ordering Direction
     *
     * @return array
     * @since  version
     */
    public static function orderingMultiCategoryFiles($files, $ordering, $direction)
    {
        if (!empty($files)) {
            $ordering = strtolower($ordering);
            $direction = strtolower($direction);
            switch ($ordering) {
                case 'ext':
                    usort($files, function ($first, $second) {
                        return strtolower($first->ext) < strtolower($second->ext);
                    });
                    break;
                case 'size':
                    usort($files, function ($first, $second) {
                        return (int)$first->size < (int)$second->size;
                    });
                    break;
                case 'created_time':
                    usort($files, function ($first, $second) {
                        return new DateTime($first->created_time) < new DateTime($second->created_time);
                    });
                    break;
                case 'modified_time':
                    usort($files, function ($first, $second) {
                        return new DateTime($first->modified_time) < new DateTime($second->modified_time);
                    });
                    break;
                case 'version':
                    usort($files, function ($first, $second) {
                        return $first->version < $second->version;
                    });
                    break;
                case 'hits':
                    usort($files, function ($first, $second) {
                        return (int)$first->hits < (int)$second->hits;
                    });
                    break;
                case 'title':
                default:
                    usort($files, function ($first, $second) {
                        return strtolower($first->title) < strtolower($second->title);
                    });
                    break;
            }
            if ($direction === 'asc') {
                $files = array_reverse($files);
            }
        }

        return $files;
    }

    /**
     * Render a select html
     *
     * @param array   $options  Options array
     * @param string  $name     Name
     * @param string  $select   Select
     * @param string  $attr     Attr
     * @param boolean $disabled Disable
     *
     * @return string
     */
    public static function dropfilesSelect(array $options = array(), $name = '', $select = '', $attr = '', $disabled = false)
    {
        $html = '';
        $html .= '<select';
        if ($name !== '') {
            $html .= ' name="' . $name . '"';
        }
        if ($attr !== '') {
            $html .= ' ' . $attr;
        }
        $html .= '>';
        if (!empty($options)) {
            foreach ($options as $key => $value) {
                $select_option = '';
                if (is_array($select)) {
                    if (in_array($key, $select)) {
                        $select_option = 'selected="selected"';
                    } elseif ((string)$key === (string)$disabled) {
                        $select_option = self::disabled($disabled, $key, false);
                    } else {
                        $select_option = '';
                    }
                } else {
                    if ($disabled) {
                        $select_option = self::disabled($disabled, $key, false);
                    } else {
                        $select_option = self::selected($select, $key, false);
                    }
                }
                $html .= '<option value="' . $key . '" ' . $select_option . '>' . $value . '</option>';
            }
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Outputs the html disabled attribute.
     *
     * Compares the first two arguments and if identical marks as disabled
     *
     * @param mixed   $disabled One of the values to compare
     * @param mixed   $current  The other value to compare if not just true
     * @param boolean $echo     Whether to echo or just return the string
     *
     * @return string Html attribute or empty string
     *
     * @since 3.0.0
     */
    public static function disabled($disabled, $current = true, $echo = true)
    {
        return self::checkedSelectedHelper($disabled, $current, $echo, 'disabled');
    }

    /**
     * Outputs the html selected attribute.
     *
     * Compares the first two arguments and if identical marks as selected
     *
     * @param mixed   $selected One of the values to compare
     * @param mixed   $current  The other value to compare if not just true
     * @param boolean $echo     Whether to echo or just return the string
     *
     * @return string Html attribute or empty string
     *
     * @since 1.0.0
     */
    public static function selected($selected, $current = true, $echo = true)
    {
        return self::checkedSelectedHelper($selected, $current, $echo, 'selected');
    }

    /**
     * Private helper function for checked, selected, disabled and readonly.
     *
     * Compares the first two arguments and if identical marks as $type
     *
     * @param mixed   $helper  One of the values to compare
     * @param mixed   $current The other value to compare if not just true
     * @param boolean $echo    Whether to echo or just return the string
     * @param string  $type    The type of checked|selected|disabled|readonly we are doing
     *
     * @return string Html attribute or empty string
     *
     * @since  2.8.0
     * @access private
     */
    public static function checkedSelectedHelper($helper, $current, $echo, $type)
    {
        if ((string) $helper === (string) $current) {
            $result =  $type.'="'. $type .'"';
        } else {
            $result = '';
        }

        if ($echo) {
            echo $result;
        }

        return $result;
    }

    /**
     * Load script, style for media field
     *
     * @return void
     */
    public static function mediaFieldAssets()
    {
        $doc = JFactory::getDocument();
        $wam = JFactory::getDocument()->getWebAssetManager();
        $wam->useScript('webcomponent.media-select');
        $wam->useStyle('webcomponent.field-media')
            ->useScript('webcomponent.field-media');


        if (count($doc->getScriptOptions('media-picker')) === 0) {
            $imagesExt = array_map(
                'trim',
                explode(
                    ',',
                    ComponentHelper::getParams('com_media')->get(
                        'image_extensions',
                        'bmp,gif,jpg,jpeg,png,webp'
                    )
                )
            );
            $audiosExt = array_map(
                'trim',
                explode(
                    ',',
                    ComponentHelper::getParams('com_media')->get(
                        'audio_extensions',
                        'mp3,m4a,mp4a,ogg'
                    )
                )
            );
            $videosExt = array_map(
                'trim',
                explode(
                    ',',
                    ComponentHelper::getParams('com_media')->get(
                        'video_extensions',
                        'mp4,mp4v,mpeg,mov,webm'
                    )
                )
            );
            $documentsExt = array_map(
                'trim',
                explode(
                    ',',
                    ComponentHelper::getParams('com_media')->get(
                        'doc_extensions',
                        'doc,odg,odp,ods,odt,pdf,ppt,txt,xcf,xls,csv'
                    )
                )
            );
            $doc->addScriptOptions('media-picker', array(
                'images' => $imagesExt,
                'audios' => $audiosExt,
                'videos' => $videosExt,
                'documents' => $documentsExt
            ));
        }
    }

    /**
     * Validate front task
     *
     * @param string $task Task
     *
     * @return boolean
     */
    public static function validateFrontTask($task)
    {
        if (strpos($task, 'googledrive.') === 0 || strpos($task, 'dropbox.') === 0
            || strpos($task, 'onedrive.') === 0 ||  strpos($task, 'onedrivebusiness.') === 0
            || strpos($task, 'frontdropbox.') === 0 ||  strpos($task, 'frontgoogle.') === 0
            || strpos($task, 'frontonedirve.') === 0 ||  strpos($task, 'frontonedrivebusiness.') === 0
            || strpos($task, 'categories.') === 0 ||  strpos($task, 'category.') === 0
            || strpos($task, 'files.') === 0 || strpos($task, 'file.') === 0 || strpos($task, 'frontfile.') === 0
        ) {
            return true;
        }
        return false;
    }
}
