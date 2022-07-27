<?php
if (empty($user) || empty($payment) || empty($product) || empty($countries) || empty($campaign)) {
    $app = JFactory::getApplication();
    $app->enqueueMessage(JText::_('MOD_EMUNDUS_PAYMENT_ERROR_MISSING_DATA'), 'error');
    return false;
}

$document = JFactory::getDocument();
$document->addScript('https://payment.flywire.com/assets/js/checkout.js');
$document->addScript(JUri::base(). '/modules/mod_emundus_payment/assets/js/flywire-init.js');

$sort_price = str_replace(',', '', $product->product_sort_price);
$price = number_format((double)$sort_price, 2, '.', ' ');
$lang = JFactory::getLanguage()->getTag();
?>
<div id="flywire-payment" data-fnum="<?= $user->fnum; ?>">
    <?php if (strpos(JUri::getInstance(), 'payment_method') !== false) { ?>
        <a class="em-mb-16" href="<?= str_replace('&payment_method=flywire' , '', JUri::getInstance()) ?>">
            <?= JText::_('MOD_EMUNDUS_PAYMENT_GO_BACK_TO_METHOD_CHOICE') ?>
        </a>
    <?php } ?>
    <div class="view-form">
        <div class="span12" style="margin:0px;width: 100%;">
            <form id="payer-infos" class="fabrikForm">
                <fieldset class="fabrikGroup">
                    <legend><?= JText::_('MOD_EMUNDUS_PAYMENT_FLYWIRE_INFORMATIONS') ?></legend>
                    <br/>
                    <?php if (!empty($config) && $config['flywire_status'] == 'cancelled') { ?>
                        <section class="em-mb-24">
                            <p><?= JText::_('MOD_EMUNDUS_PAYMENT_ALREADY_TRIED_PAYMENT_BUT_CANCELLED') ?></p>
                        </section>
                    <?php } ?>

                    <section id="recap_payment" class="em-mb-24">
                        <p><?= JText::_('MOD_EMUNDUS_PAYMENT_RECAP_FOR') ?> <b> <?= " " . $campaign->label ?></b></p>
                        <p><?= JText::_('MOD_EMUNDUS_PAYMENT_PRICE') . " : " .  $price . "€" ?></p>
                    </section>
                    <div class="row-fluid">
                        <label for="sender_first_name"><?= JText::_('FLYWIRE_SENDER_FIRST_NAME') ?><b class="asterisk">*</b></label>
                        <input id="sender_first_name" type="text" class="em-w-100" placeholder=""
                               value="<?=  !empty($config['sender_first_name']) ? $config['sender_first_name'] : ''  ?>">
                    </div>

                    <div class="row-fluid">
                        <label for="sender_last_name"><?= JText::_('FLYWIRE_SENDER_LAST_NAME') ?><b class="asterisk">*</b></label>
                        <input id="sender_last_name" type="text" class="em-w-100" placeholder=""
                               value="<?=  !empty($config['sender_last_name']) ? $config['sender_last_name'] : ''  ?>">
                    </div>

                    <div class="row-fluid">
                        <label for="sender_email"><?= JText::_('FLYWIRE_SENDER_EMAIL') ?><b class="asterisk">*</b></label>
                        <input id="sender_email" type="email" class="em-w-100" placeholder="" pattern="^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"
                               value="<?=  !empty($config['sender_email']) ? $config['sender_email'] : ''  ?>">
                    </div>

                    <div class="row-fluid">
                        <label for="sender_phone"><?= JText::_('FLYWIRE_SENDER_PHONE') ?></label>
                        <input
                           id="sender_phone"
                           type="text"
                           class="em-w-100"
                           placeholder=""
                           pattern="^\+?\d+(-\d+)*$"
                           value="<?=  !empty($config['sender_phone']) ? $config['sender_phone'] : ''  ?>"
                        >
                    </div>

                    <div class="row-fluid">
                        <label for="sender_address1"><?= JText::_('FLYWIRE_SENDER_ADDRESS1') ?><b class="asterisk">*</b></label>
                        <input id="sender_address1" type="text" class="em-w-100" placeholder=""
                               value="<?=  !empty($config['sender_address1']) ? $config['sender_address1'] : ''  ?>">
                    </div>

                    <div class="row-fluid">
                        <label for="sender_address2"><?= JText::_('FLYWIRE_SENDER_ADDRESS2') ?></label>
                        <input id="sender_address2" type="text" class="em-w-100" placeholder=""
                               value="<?=  !empty($config['sender_address2']) ? $config['sender_address2'] : ''  ?>">
                    </div>

                    <div class="row-fluid">
                        <label for="sender_city"><?= JText::_('FLYWIRE_SENDER_CITY') ?><b class="asterisk">*</b></label>
                        <input id="sender_city" type="text" class="em-w-100" placeholder=""
                               value="<?=  !empty($config['sender_city']) ? $config['sender_city'] : ''  ?>">
                    </div>

                    <div class="row-fluid">
                        <label for="sender_state"><?= JText::_('FLYWIRE_SENDER_STATE') ?></label>
                        <input id="sender_state" type="text" class="em-w-100" placeholder=""
                               value="<?=  !empty($config['sender_state']) ? $config['sender_state'] : ''  ?>">
                    </div>

                    <div class="row-fluid">

                        <label for="sender_country"><?= JText::_('FLYWIRE_SENDER_COUNTRY') ?><b class="asterisk">*</b></label>
                        <select id="sender_country" class="em-w-100">
                            <?php
                            foreach ($countries as $country) {
                                $label = $lang == 'fr-FR' ? $country->label_fr : $country->label_en;

                                if (!empty($config['sender_country']) && $config['sender_country'] == $country->code_iso_2) {
                                    echo '<option value="' . $country->code_iso_2 . '" selected>' . $label . '</option>';
                                } else {
                                    echo '<option value="' . $country->code_iso_2 . '">' . $label . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="em-w-100 em-flex-row-end em-justify-content-end em-mt-16">
                        <button id="submit-payer-infos" class="em-front-btn em-front-primary-btn em-w-33">
                            <?= JText::_('MOD_EMUNDUS_PAYMENT_SEND_CONF') ?>
                        </button>
                        <button id="modify-payer-infos" class="hidden em-front-btn em-front-secondary-btn em-w-33 em-ml-8">
                            <?= JText::_('MOD_EMUNDUS_PAYMENT_REEDIT_CONF') ?>
                        </button>
                    </div>

                    <div id="open-flywire-div" class="hidden em-w-100 em-flex-row-end em-justify-content-end em-mt-16">
                        <button id="open-flywire" class="em-front-btn em-front-primary-btn em-w-33">
                            <?= JText::_('MOD_EMUNDUS_PAYMENT_OPEN_FLYWIRE') ?>
                        </button>
                    </div>
                </fieldset>
            </form>

        </div>
    </div>
</div>