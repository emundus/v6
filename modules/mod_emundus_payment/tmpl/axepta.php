<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */
?>
<div class="view-form">
    <div class="span12" style="margin:0px;width: 100%;">
        <form id="payer-infos" class="fabrikForm">
            <fieldset class="fabrikGroup">
                <p class="em-h3">Régler les frais d'inscriptions</p>
                <section id="recap_payment" class="em-mt-16">
                    <p class="em-font-weight-600"><?= JText::_('MOD_EMUNDUS_PAYMENT_RECAP_FOR') ?> <b> <?= " " . $campaign->label ?></b></p>
                    <p class="em-mt-8"><?= JText::_('MOD_EMUNDUS_PAYMENT_PRICE') . " : " .  $price . "€" ?></p>
                </section>

                <div class="em-w-100 em-flex-row em-flex-row-justify-end em-mt-16">
                    <a id="submit-payer-infos" class="em-front-btn em-front-primary-btn em-w-33" href="<?php echo $payment_url ?>" target="_blank">
                        <?= JText::_('MOD_EMUNDUS_PAYMENT_OPEN_FLYWIRE') ?>
                    </a>
                </div>
            </fieldset>
        </form>
    </div>
</div>
