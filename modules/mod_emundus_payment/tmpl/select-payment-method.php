<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

?>
<a href="/" class="flex items-center text-neutral-900 w-fit-content">
    <span class="material-icons-outlined mr-1" aria-hidden="true">navigate_before</span>
    <?= Text::_('MOD_EMUNDUS_PAYMENT_FLYWIRE_GO_TO_HOMEPAGE') ?>
</a>
<div class="rounded bg-white mt-4 p-4">
    <h1 class="text-center"><?= Text::_('MOD_EMUNDUS_PAYMENT_SELECT_PAYMENT_METHOD') ?></h1>
    <section id="payment-methods-selector" class="flex items-center">
        <?php foreach($params['payment_methods']['payment_method'] as $key => $method): ?>
            <a href="<?=  Factory::getURI(); ?>&payment_method=<?= $method ?>">
                <div class="payment-method-option cursor-pointer em-front-btn em-front-secondary-btn">
                    <p class="text-center" style="color:inherit;"><?= Text::_('MOD_EMUNDUS_PAYMENT_METHOD_' . strtoupper($method)) ?></p>
                    <?php if ($params['payment_methods']['payment_highlighted'][$key]): ?>
                        <p class="ml-1 text-center" style="color:inherit;"><i> (<?= Text::_('MOD_EMUNDUS_PAYMENT_HIGHLIGHTED_METHOD') ?>)</i></p>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </section>
</div>
