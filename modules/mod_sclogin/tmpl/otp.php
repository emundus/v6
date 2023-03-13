<?php
/**
 * @package         SCLogin
 * @copyright (c)   2009-2021 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v9.0.215
 * @build-date      2022/09/06
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$input = Factory::getApplication()->input;
$modId = $input->getInt('mod_id');
?>
<div class="sclogin-joomla-login">
    <?php echo Text::sprintf('MOD_SCLOGIN_SECRETKEY_LABEL', $input->post->get('u', '')); ?>
    <form method="post" id="sclogin-form-otp-<?php echo $modId ?>">
        <fieldset class="userdata <?php echo $helper->colClass;?>12">
            <div class="control-group <?php echo $helper->pullClass;?>left" id="form-sclogin-secretkey">
                <div class="controls">
                    <div class="input-append">
                        <input name="secretkey" tabindex="1" id="sclogin-input-secretkey" alt="secretkey" type="text" class="input-medium"
                               placeholder="<?php echo Text::_('MOD_SCLOGIN_SECRETKEY'); ?>">
                    </div>
                </div>
            </div>
        </fieldset>
        <button type="submit" name="Submit" class="btn btn-primary otp"><?php echo Text::_('MOD_SCLOGIN_LOGIN') ?></button>
        <button type="button" name="Cancel" class="btn cancel" onclick="sclogin.otp.reset('<?php echo $modId; ?>');">Cancel</button>
    </form>

</div>