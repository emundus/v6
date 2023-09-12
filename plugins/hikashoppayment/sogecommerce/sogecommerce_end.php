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
$sogecommerce = new SogecommerceRequest();
$sogecommerce->addExtInfo('payment_method_id', $this->vars['payment_method_id']);
$sogecommerce->setFromArray($this->vars);
?>

<div class="hikashop_sogecommerce_end" id="hikashop_sogecommerce_end">
    <span id="hikashop_sogecommerce_end_message" class="hikashop_sogecommerce_end_message">
        <?php echo JText::_('SOGECOMMERCE_PLEASE_WAIT_BEFORE_REDIRECTION').'<br/>'. JText::_('SOGECOMMERCE_CLICK_ON_BUTTON_IF_NOT_REDIRECTED'); ?>
    </span>
    <span id="hikashop_sogecommerce_end_spinner" class="hikashop_sogecommerce_end_spinner">
        <img src="<?php echo HIKASHOP_IMAGES . 'spinner.gif'; ?>" />
    </span>
    <br/>
    <form id="hikashop_sogecommerce_form" name="hikashop_sogecommerce_form" action="<?php echo $sogecommerce->get('platform_url'); ?>" method="post">
        <div id="hikashop_sogecommerce_end_image" class="hikashop_sogecommerce_end_image">
            <input id="hikashop_sogecommerce_button" type="submit" value="<?php echo JText::_('SOGECOMMERCE_SEND_BTN_VALUE'); ?>" name="" alt="<?php echo JText::_('SOGECOMMERCE_SEND_BTN_ALT'); ?>" />
        </div>
        <?php
        echo $sogecommerce->getRequestHtmlFields();

        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration("window.hikashop.ready( function() { document.getElementById('hikashop_sogecommerce_form').submit(); });");
        hikaInput::get()->set('noform', 1);
        ?>
    </form>
</div>
