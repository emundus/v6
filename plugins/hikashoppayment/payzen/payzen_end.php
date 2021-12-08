<?php
/**
 * Copyright © Lyra Network.
 * This file is part of PayZen plugin for HikaShop. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra.com/)
 * @copyright Lyra Network
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL v3)
 */

defined('_JEXEC') or die('Restricted access');

require_once rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'components' . DS . 'com_payzen' . DS . 'classes' . DS .
     'payzen_request.php';
$payzen = new PayzenRequest();
$payzen->addExtInfo('payment_method_id', $this->vars['payment_method_id']);
$payzen->setFromArray($this->vars);
?>

<div class="hikashop_payzen_end" id="hikashop_payzen_end">
    <span id="hikashop_payzen_end_message" class="hikashop_payzen_end_message">
        <?php echo JText::_('PAYZEN_PLEASE_WAIT_BEFORE_REDIRECTION').'<br/>'. JText::_('PAYZEN_CLICK_ON_BUTTON_IF_NOT_REDIRECTED'); ?>
    </span>
    <span id="hikashop_payzen_end_spinner" class="hikashop_payzen_end_spinner">
        <img src="<?php echo HIKASHOP_IMAGES . 'spinner.gif'; ?>" />
    </span>
    <br/>
    <form id="hikashop_payzen_form" name="hikashop_payzen_form" action="<?php echo $payzen->get('platform_url'); ?>" method="post">
        <div id="hikashop_payzen_end_image" class="hikashop_payzen_end_image">
            <input id="hikashop_payzen_button" type="submit" value="<?php echo JText::_('PAYZEN_SEND_BTN_VALUE'); ?>" name="" alt="<?php echo JText::_('PAYZEN_SEND_BTN_ALT'); ?>" />
        </div>
        <?php
        echo $payzen->getRequestHtmlFields();

        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration("window.addEvent('domready', function() { document.getElementById('hikashop_payzen_form').submit(); });");
        JRequest::setVar('noform', 1);
        ?>
    </form>
</div>
