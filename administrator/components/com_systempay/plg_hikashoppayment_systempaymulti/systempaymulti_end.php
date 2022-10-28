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
$systempaymulti = new SystempayRequest();
$systempaymulti->addExtInfo('payment_method_id', $this->vars['payment_method_id']);
$systempaymulti->setFromArray($this->vars);
$systempaymulti->setMultiPayment(
    null /* Let API use already set amount. */,
    $this->multivars['first'],
    $this->multivars['count'],
    $this->multivars['period']
);

if (isset($this->multivars['contract']) && $this->multivars['contract']) {
    $systempaymulti->set('contracts', $this->multivars['contract']);
}
?>

<div class="hikashop_systempaymulti_end" id="hikashop_systempaymulti_end">
    <span id="hikashop_systempaymulti_end_message" class="hikashop_systempaymulti_end_message">
        <?php echo JText::_('SYSTEMPAYMULTI_PLEASE_WAIT_BEFORE_REDIRECTION') . '<br/>' . JText::_('SYSTEMPAYMULTI_CLICK_ON_BUTTON_IF_NOT_REDIRECTED'); ?>
    </span>
    <span id="hikashop_systempaymulti_end_spinner" class="hikashop_systempaymulti_end_spinner">
        <img src="<?php echo HIKASHOP_IMAGES . 'spinner.gif'; ?>" />
    </span>
    <br/>
    <form id="hikashop_systempaymulti_form" name="hikashop_systempaymulti_form" action="<?php echo $systempaymulti->get('platform_url'); ?>" method="post">
        <div id="hikashop_systempaymulti_end_image" class="hikashop_systempaymulti_end_image">
            <input id="hikashop_systempaymulti_button" type="submit" value="<?php echo JText::_('SYSTEMPAYMULTI_SEND_BTN_VALUE'); ?>" name="" alt="<?php echo JText::_('SYSTEMPAYMULTI_SEND_BTN_ALT'); ?>" />
        </div>
        <?php
        echo $systempaymulti->getRequestHtmlFields();

        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration("window.hikashop.ready( function() { document.getElementById('hikashop_systempaymulti_form').submit(); });");
        hikaInput::get()->set('noform', 1);
        ?>
    </form>
</div>
