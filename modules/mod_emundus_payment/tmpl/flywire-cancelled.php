<?php
$current_url = JUri::getInstance()->toString();
$retry_url = str_replace(['status=cancelled', 'status=error'], '', $current_url);
?>

<div class="container-rounded">
    <p><?= JText::_('MOD_EMUNDUS_PAYMENT_FLYWIRE_ERROR') ?></p>
    <p><?= JText::_('MOD_EMUNDUS_PAYMENT_FLYWIRE_TRY_AGAIN_LATER') ?></p>
    <div class="em-flex-row em-flex-space-between em-mt-24">
        <a href="<?= $retry_url ?>" class="em-front-btn em-front-secondary-btn em-m-center em-mr-8">
            <?= JText::_('MOD_EMUNDUS_PAYMENT_FLYWIRE_RETRY') ?>
        </a>
        <a href="<?= JUri::base() ?>" class="em-front-btn em-front-primary-btn em-m-center">
            <?= JText::_('MOD_EMUNDUS_PAYMENT_FLYWIRE_GO_TO_HOMEPAGE') ?>
        </a>
    </div>
</div>
