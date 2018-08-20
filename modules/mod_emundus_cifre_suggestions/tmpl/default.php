<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_cifre_suggestions
 * @copyright	Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
?>

<div class="em-contact-request-module">

	<?php if (!empty($offers)) :?>
		<span class="em-contact-request col-md-12">
			<div class="em-highlight"><?php echo count($offers) > 1 ?JText::_('MOD_EMUNDUS_CIFRE_OFFERS_RECIEVED_OFFERS'):JText::_('MOD_EMUNDUS_CIFRE_OFFERS_RECIEVED_OFFER'); ?></div>
			<?php foreach ($offers as $offer) :?>

				<div class="col-md-5 em-contact-request-card" id="<?php echo $offer->fnum; ?>">
					<div class="col-md-8 em-bottom-space">
                        <div class="em-contact-request-heading"><?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_OFFER_NAME'); ?></div>
						<?php if (!empty($offer->titre)) :?>
							<?php echo '<b>'.$offer->titre.'</b>'; ?>
						<?php else: ?>
							<?php echo '<b>'.JText::_('NO_TITLE').'</b>'; ?>
						<?php endif; ?>
					</div>

					<div class="col-md-8 em-bottom-space">
                        <div id="em-buttons-<?php echo $offer->fnum; ?>">
                            <!-- TODO: Add a URL to the offer page by getting the ID required in the model. -->
                            <a type="button" class="btn btn-primary" href="<?php echo JRoute::_(JURI::base()."/les-offres/consultez-les-offres/details/299/".$offer->search_engine_page); ?>">
                                <?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_VIEW'); ?>
                            </a>
                        </div>
					</div>
                    <span class="alert alert-danger hidden" id="em-action-text-<?php echo $offer->fnum; ?>"></span>
				</div>
			<?php endforeach; ?>
		</span>
	<?php endif; ?>
</div>
