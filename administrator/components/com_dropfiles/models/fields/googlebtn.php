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
class JFormFieldGooglebtn extends JFormField
{

    /**
     * Type
     *
     * @var string
     */
    protected $type = 'Googlebtn';

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
     * Add field connection Google drive
     *
     * @return string
     */
    protected function getInput()
    {
        $path_dropfilesgoogle = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
        JLoader::register('DropfilesGoogle', $path_dropfilesgoogle);

        $params = JComponentHelper::getParams('com_dropfiles');
        $google_watch_changes = $params->get('google_watch_changes', 1);
        $watchData = $params->get('dropfiles_google_watch_data', '');
        $errorMessage = '';
        if ($watchData !== '') {
            $watchData = json_decode($watchData, true);
            if (is_array($watchData) && isset($watchData['error'])) {
                if ((int) $watchData['error'] === 401) {
                    // Unauthorized domain
                    $errorMessage = JText::_('COM_DROPFILES_GOOGLEDRIVE_WEBHOOK_DOMAIN_NOT_AUTHORIZED');
                } else {
                    // Site not used https
                    $errorMessage = $watchData['message'];
                }
            }
        }

        $google = new DropfilesGoogle();
        ob_start();
        echo '
            <style>
            .btn-google {
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
            .google_node_head > h3{
                font-weight: bold;
                padding: 8px 0 7px 15px;
                background: ;
                background-color: #23282D;
                border-color: #bce8f1;
                color: #eee;
                font-size: 13px;
            }
            .btn-google-changes {
                font-weight: bold;
            }
            .btn-google-changes:hover {
                background-color: #f90900;
            }
            .btn-google-changes.error {
                background-color: #ea6815;
            }
            .btn-google-changes span[class^=icon] {
                background-color: transparent;
                border-right: 1px solid #ffffff;
                height: auto;
                line-height: inherit;
                margin: 0 6px 0 -10px;
                opacity: 1;
                text-shadow: none;
                width: 28px;
                z-index: -1;
            }
            </style>
            ';
        if (!$google->checkAuth()) {
            $url = $google->getAuthorisationUrl();
            if ($url) : ?>
        <a id="ggconnect" class="btn btn-primary btn-google" href="#"
           onclick="window.open('<?php echo $url; ?>','foo','width=600,height=600');return false;"><img
                src="<?php echo JURI::root(); ?>/components/com_dropfiles/assets/images/drive-icon-colored.png"
                alt="" width="13"/> <?php echo JText::_('COM_DROPFILES_GOOGLEDRIVE_CONNECT_PART2_CONNECT'); ?>
        </a>
                <?php
            endif;
        } else { ?>
                <?php echo JText::_('COM_DROPFILES_GOOGLEDRIVE_CONNECT_PART3'); ?>
            <a class="btn btn-primary btn-google"
               href="index.php?option=com_dropfiles&task=googledrive.logout">
                <img src="<?php echo JURI::root(); ?>/components/com_dropfiles/assets/images/drive-icon-colored.png" alt="" width="13"/>
                <?php echo JText::_('COM_DROPFILES_GOOGLEDRIVE_CONNECT_PART3_DISCONNECT'); ?></a>
            <?php
            $errorBgColor = '';
            if ($errorMessage !== '') {
                $errorBgColor = ' error';
            } ?>
            <a id="dropfiles_btn_google_changes"
               class="btn btn-success btn-google-changes<?php echo $errorBgColor; ?>"
               href="#" data-csrf="<?php echo JSession::getFormToken(); ?>"
                title="<?php echo JText::_('COM_DROPFILES_GOOGLEDRIVE_WATCH_CHANGES_TOOLTIP'); ?>">
                <?php
                if ($google_watch_changes) {
                    $icon = 'cancel';
                    $text = JText::_('COM_DROPFILES_GOOGLEDRIVE_STOP_WATCH_CHANGES');
                } else {
                    $icon = 'arrow-right-4';
                    $text = JText::_('COM_DROPFILES_GOOGLEDRIVE_WATCH_CHANGES');
                } ?>
                <span class="icon-<?php echo $icon; ?>" aria-hidden="true"></span>
                    <?php echo $text; ?>
            </a>
                <?php if ($errorMessage !== '') : ?>
            <div style="padding:0;margin-top:0;min-height: 0;">
                <div class="alert alert-error">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <div class="alert-message"><strong>Error: </strong><?php echo $errorMessage; ?></div>
                </div>
            </div>
                <?php endif; ?>
        <?php } ?>
        <p><?php echo JText::_('COM_DROPFILES_GOOGLEDRIVE_CONNECT_PART2_FIRST'); ?></p>
        <div>
            <?php JText::printf(
                'COM_DROPFILES_GOOGLEDRIVE_CONNECT_PART1_2',
                JURI::root(),
                JURI::root() . 'administrator/index.php?option=com_dropfiles&task=googledrive.authenticate'
            ); ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
