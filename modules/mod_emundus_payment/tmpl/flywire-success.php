<?php

switch ($config['flywire_status']) {
	case 'initiated':
		$currentStep = 1;
		break;
	case 'guaranteed':
		$currentStep = 2;
		break;
	case 'delivered':
		$currentStep = 3;
		break;
	case 'cancelled':
		$app->enqueueMessage(JText::_('MOD_EMUNDUS_PAYMENT_FLYWIRE_CANCELLED'));
		$app->redirect('/');
		break;
	default:
		$currentStep = 0;
		break;
}

$contact = $params['contact_support'];
?>
<div class="container-rounded">
    <div class="em-flex-row em-flex-space-between">
        <p><?= JText::_('MOD_EMUNDUS_PAYMENT_FLYWIRE_STEPS') ?></p>
        <a href="<?= JFactory::getUri() ?>">
            <span class="material-icons"
                  title="<?= JText::_('MOD_EMUNDUS_PAYMENT_FLYWIRE_REFRESH_STEPS') ?>">refresh</span>
        </a>
    </div>
    <section id="payment-steps">
        <div id="steps" class="em-flex-row em-flex-space-between">
            <div id="step-1" class="step em-flex-col-center <?= $currentStep > 0 ? 'filled' : '' ?>">
                <span><?= JText::_('MOD_EMUNDUS_PAYMENT_FLYWIRE_STEP1') ?></span>
                <div class="step-circle"></div>
            </div>
            <div id="line-1-2" class="line <?= $currentStep > 1 ? 'filled' : '' ?>"></div>
            <div id="step-2" class="step em-flex-col-center <?= $currentStep > 1 ? 'filled' : '' ?>">
                <span><?= JText::_('MOD_EMUNDUS_PAYMENT_FLYWIRE_STEP2') ?></span>
                <div class="step-circle"></div>
            </div>
            <div id="line-2-3" class="line <?= $currentStep > 2 ? 'filled' : '' ?>"></div>
            <div id="step-3" class="step em-flex-col-center <?= $currentStep > 2 ? 'filled' : '' ?>">
                <span><?= JText::_('MOD_EMUNDUS_PAYMENT_FLYWIRE_STEP3') ?></span>
                <div class="step-circle"></div>
            </div>
        </div>
    </section>
    <p><?= JText::_('MOD_EMUNDUS_PAYMENT_FLYWIRE_STEPS_WRONG') . ', ' ?>
		<?php if (!empty($contact)) : ?>
            <a href="mailto:<?= $contact; ?>"><?= JText::_('MOD_EMUNDUS_PAYMENT_FLYWIRE_STEPS_CONTACT') . '.' ?></a>
		<?php else : ?>
			<?= JText::_('MOD_EMUNDUS_PAYMENT_FLYWIRE_STEPS_CONTACT') . '.' ?>
		<?php endif; ?>
    </p>

    <div id="payment-actions" class="em-w-100 em-mt-16 em-flex-row-end">
        <a href="/"
           class="em-front-btn em-front-primary-btn em-w-33"><?= JText::_('MOD_EMUNDUS_PAYMENT_FLYWIRE_GO_TO_HOMEPAGE') ?></a>
    </div>
</div>