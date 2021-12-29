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
$payzenmulti = new PayzenRequest();
$payzenmulti->addExtInfo('payment_method_id', $this->vars['payment_method_id']);
$payzenmulti->setFromArray($this->vars);
$payzenmulti->setMultiPayment(
    null /* Let API use already set amount. */,
    $this->multivars['first'],
    $this->multivars['count'],
    $this->multivars['period']
);

if (isset($this->multivars['contract']) && $this->multivars['contract']) {
    $payzenmulti->set('contracts', $this->multivars['contract']);
}
?>

<div class="hikashop_payzenmulti_end" id="hikashop_payzenmulti_end">
    <span id="hikashop_payzenmulti_end_message" class="hikashop_payzenmulti_end_message">
        <?php echo JText::_('PAYZENMULTI_PLEASE_WAIT_BEFORE_REDIRECTION') . '<br/>' . JText::_('PAYZENMULTI_CLICK_ON_BUTTON_IF_NOT_REDIRECTED'); ?>
    </span>
    <span id="hikashop_payzenmulti_end_spinner" class="hikashop_payzenmulti_end_spinner">
        <img src="<?php echo HIKASHOP_IMAGES . 'spinner.gif'; ?>" />
    </span>
    <br/>
    <form id="hikashop_payzenmulti_form" name="hikashop_payzenmulti_form" action="<?php echo $payzenmulti->get('platform_url'); ?>" method="post">
        <div id="hikashop_payzenmulti_end_image" class="hikashop_payzenmulti_end_image">
            <input id="hikashop_payzenmulti_button" type="submit" value="<?php echo JText::_('PAYZENMULTI_SEND_BTN_VALUE'); ?>" name="" alt="<?php echo JText::_('PAYZENMULTI_SEND_BTN_ALT'); ?>" />
        </div>
        <?php
        echo $payzenmulti->getRequestHtmlFields();

        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration("window.addEvent('domready', function() { document.getElementById('hikashop_payzenmulti_form').submit(); });");
        JRequest::setVar('noform', 1);
        ?>
    </form>
</div>
