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

defined('_JEXEC') || die;

jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 */
class JFormFieldOneDrivebtn extends JFormField
{

    /**
     * Type
     *
     * @var string
     */
    protected $type = 'OneDrivebtn';

    /**
     * Get label
     *
     * @return string
     */
    protected function getLabel()
    {
        return '';
    }


    /**
     * Add Connection|Disconnect onedriver button
     *
     * @return string
     */
    protected function getInput()
    {
        $path_dropfilesonedrive = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesOneDrive.php';
        JLoader::register('DropfilesOneDrive', $path_dropfilesonedrive);
        $params = JComponentHelper::getParams('com_dropfiles');
        $onedrive = new DropfilesOneDrive();
        ob_start();
        echo '
            <style>
            .btn-onedrive {
                background: #1d6cb0 none repeat scroll 0 0 !important;
                border: medium none !important;
                border-radius: 2px !important;
                box-shadow: none !important;
                height: auto !important;
                padding: 5px 20px !important;
                text-shadow: none !important;
                width: auto !important;
                color: #fff !important;
            }
            .onedrive_node_head > h3{
                font-weight: bold;
                padding: 8px 0 7px 15px;
                background: ;
                background-color: #23282D;
                border-color: #bce8f1;
                color: #eee;
                font-size: 13px;
            }
            </style>
            ';
        if (!$onedrive->checkAuth()) {
            $url = $onedrive->getAuthorisationUrl();
            ?>
            <a id="ggconnect" class="btn btn-primary btn-onedrive" href="#"
               onclick="window.open('<?php echo $url; ?>','foo','width=600,height=600');return false;"><img
                        src="<?php echo JURI::root(); ?>/components/com_dropfiles/assets/images/icon-onedrive.svg"
                        alt="" width="20px"/> <?php echo JText::_('COM_DROPFILES_ONEDRIVE_CONNECT_PART2_CONNECT'); ?>
            </a>
        <?php } else { ?>
            <?php echo JText::_('COM_DROPFILES_ONEDRIVE_CONNECT_PART3'); ?>
            <a class="btn btn-primary btn-onedrive"
               href="index.php?option=com_dropfiles&task=onedrive.logout">
                <img src="<?php echo JURI::root(); ?>/components/com_dropfiles/assets/images/icon-onedrive.svg" alt="" width="20px"/>
                <?php echo JText::_('COM_DROPFILES_ONEDRIVE_CONNECT_PART3_DISCONNECT'); ?>
            </a>
        <?php } ?>
        <p><?php echo JText::_('COM_DROPFILES_ONEDRIVE_CONNECT_PART2_FIRST'); ?></p>
        <?php
        JText::printf('COM_DROPFILES_ONEDRIVE_CONNECT_PART1_2', JURI::root() . 'administrator/index.php');
        return ob_get_clean();
    }
}
