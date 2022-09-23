<a href="/"><?= JText::_('MOD_EMUNDUS_PAYMENT_FLYWIRE_GO_TO_HOMEPAGE') ?></a>
<div class="container-rounded">
    <h1 class="em-text-align-center"><?= JText::_('MOD_EMUNDUS_PAYMENT_SELECT_PAYMENT_METHOD') ?></h1>
    <section id="payment-methods-selector" class="em-flex-row em-w-100">
        <?php foreach($params['payment_methods']['payment_method'] as $key => $method): ?>
            <a href="<?=  JFactory::getURI(); ?>&payment_method=<?= $method ?>">
                <div class="payment-method-option em-pointer em-front-btn em-front-secondary-btn">
                    <p class="em-text-align-center" style="color:inherit;"><?= JText::_('MOD_EMUNDUS_PAYMENT_METHOD_' . strtoupper($method)) ?></p>
                    <?php if ($params['payment_methods']['payment_highlighted'][$key]): ?>
                        <p class="em-ml-4 em-text-align-center" style="color:inherit;"><i> (<?= JText::_('MOD_EMUNDUS_PAYMENT_HIGHLIGHTED_METHOD') ?>)</i></p>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </section>
</div>
