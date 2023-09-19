<?php
/**
 * Copyright © Lyra Network.
 * This file is part of Sogecommerce plugin for HikaShop. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra.com/)
 * @copyright Lyra Network
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL v3)
 */

defined('_JEXEC') or die('Restricted access');

require_once rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'components' . DS . 'com_sogecommerce' . DS . 'classes' . DS .
     'sogecommerce_request.php';
$sogecommercemulti = new SogecommerceRequest();
$sogecommercemulti->addExtInfo('payment_method_id', $this->vars['payment_method_id']);
$sogecommercemulti->setFromArray($this->vars);
$sogecommercemulti->setMultiPayment(
    null /* Let API use already set amount. */,
    $this->multivars['first'],
    $this->multivars['count'],
    $this->multivars['period']
);

if (isset($this->multivars['contract']) && $this->multivars['contract']) {
    $sogecommercemulti->set('contracts', $this->multivars['contract']);
}
?>

<div class="hikashop_sogecommercemulti_end" id="hikashop_sogecommercemulti_end">
    <span id="hikashop_sogecommercemulti_end_message" class="hikashop_sogecommercemulti_end_message">
        <?php echo JText::_('SOGECOMMERCEMULTI_PLEASE_WAIT_BEFORE_REDIRECTION') . '<br/>' . JText::_('SOGECOMMERCEMULTI_CLICK_ON_BUTTON_IF_NOT_REDIRECTED'); ?>
    </span>
    <span id="hikashop_sogecommercemulti_end_spinner" class="hikashop_sogecommercemulti_end_spinner">
        <img src="<?php echo HIKASHOP_IMAGES . 'spinner.gif'; ?>" />
    </span>
    <br/>
    <form id="hikashop_sogecommercemulti_form" name="hikashop_sogecommercemulti_form" action="<?php echo $sogecommercemulti->get('platform_url'); ?>" method="post">
        <div id="hikashop_sogecommercemulti_end_image" class="hikashop_sogecommercemulti_end_image">
            <input id="hikashop_sogecommercemulti_button" type="submit" value="<?php echo JText::_('SOGECOMMERCEMULTI_SEND_BTN_VALUE'); ?>" name="" alt="<?php echo JText::_('SOGECOMMERCEMULTI_SEND_BTN_ALT'); ?>" />
        </div>
        <?php
        echo $sogecommercemulti->getRequestHtmlFields();

        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration("window.hikashop.ready( function() { document.getElementById('hikashop_sogecommercemulti_form').submit(); });");
        hikaInput::get()->set('noform', 1);
        ?>
    </form>
</div>
