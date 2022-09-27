<?php
/**
 * Copyright © Lyra Network.
 * This file is part of Systempay plugin for HikaShop. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra.com/)
 * @copyright Lyra Network
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL v3)
 */

defined('_JEXEC') or die('Restricted access');

require_once rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'components' . DS . 'com_systempay' . DS . 'classes' . DS .
     'systempay_request.php';
$systempay = new SystempayRequest();
$systempay->addExtInfo('payment_method_id', $this->vars['payment_method_id']);
$systempay->setFromArray($this->vars);
?>

<div class="hikashop_systempay_end" id="hikashop_systempay_end">
    <span id="hikashop_systempay_end_message" class="hikashop_systempay_end_message">
        <?php echo JText::_('SYSTEMPAY_PLEASE_WAIT_BEFORE_REDIRECTION').'<br/>'. JText::_('SYSTEMPAY_CLICK_ON_BUTTON_IF_NOT_REDIRECTED'); ?>
    </span>
    <span id="hikashop_systempay_end_spinner" class="hikashop_systempay_end_spinner">
        <img src="<?php echo HIKASHOP_IMAGES . 'spinner.gif'; ?>" />
    </span>
    <br/>
    <form id="hikashop_systempay_form" name="hikashop_systempay_form" action="<?php echo $systempay->get('platform_url'); ?>" method="post">
        <div id="hikashop_systempay_end_image" class="hikashop_systempay_end_image">
            <input id="hikashop_systempay_button" type="submit" value="<?php echo JText::_('SYSTEMPAY_SEND_BTN_VALUE'); ?>" name="" alt="<?php echo JText::_('SYSTEMPAY_SEND_BTN_ALT'); ?>" />
        </div>
        <?php
        echo $systempay->getRequestHtmlFields();

        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration("window.hikashop.ready( function() { document.getElementById('hikashop_systempay_form').submit(); });");
        hikaInput::get()->set('noform', 1);
        ?>
    </form>
</div>
