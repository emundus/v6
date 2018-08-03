<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_cifre_offers
 * @copyright	Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
?>

<div class="em-contact-request-module">

	<?php if (!empty($offers->to)) :?>
		<span class="em-contact-request col-md-12">
			<div class="em-highlight">Offres reçus</div>
			<?php foreach ($offers->to as $offer) :?>
				<div class="col-md-5 em-contact-request-card" id="<?php echo $offer->link_id; ?>">
					<div class="col-md-8 em-bottom-space">
                        <div class="em-contact-request-heading"> Nom de l'offre </div>
						<?php if (!empty($offer->titre)) :?>
							<?php echo '<b>'.$offer->titre.'</b>'; ?>
						<?php else: ?>
							<?php echo '<b>'.JText::_('NO_TITLE').'</b>'; ?>
						<?php endif; ?>
					</div>

					<div class="col-md-8 em-bottom-space">
                        <div class="em-contact-request-contact">
                            <div class="em-contact-request-heading"> Demande reçu de: </div>
                            <div class="em-contact-request-contact-item"><strong>Nom:</strong> <?php echo JFactory::getUser($offer->user_from)->name; ?> </div>
                            <div class="em-contact-request-contact-item"><strong>Email:</strong> <?php echo JFactory::getUser($offer->user_from)->email; ?> </div>
                        </div>
						<?php if ($offer->state == '1') :?>
							<button type="button" class="btn btn-primary" onclick="reply('<?php echo $offer->link_id; ?>')">
								Répondre
							</button>
							<button type="button" class="btn btn-primary" onclick="breakUp('<?php echo $offer->link_id; ?>')">
								Ignorer
							</button>
						<?php elseif ($offer->state == '2') :?>
							<button type="button" class="btn btn-primary" onclick="breakUp('<?php echo $offer->link_id; ?>')">
								Couper contact
							</button>
						<?php endif; ?>
						<?php if (!empty($offer->offer_from)) :?>
                            <div class="em-contact-request-linked-offer"> Offre liée : </div>
                            <div class="em-contact-request-linked-offer-link"><a href="<?php echo JRoute::_(JURI::base()."les-offres/consultez-les-offres/details/299/".$offer->offer_from->search_engine_page); ?>"><?php echo $offer->offer_from->titre; ?></a></div>
						<?php endif; ?>
					</div>
                    <span class="alert alert-danger hidden" id="em-action-text-<?php echo $offer->link_id; ?>"></span>
				</div>
			<?php endforeach; ?>
		</span>
	<?php endif; ?>

	<?php if (!empty($offers->from)) :?>
		<span class="em-contact-request col-md-12">
			<div class="em-highlight">Offres envoyées</div>
			<?php foreach ($offers->from as $offer) :?>
				<div class="col-md-5 em-contact-request-card" id="<?php echo $offer->link_id; ?>">
					<div class="col-md-8 em-bottom-space">
						<?php if (!empty($offer->titre)) :?>
							<?php echo '<b>'.$offer->titre.'</b>'; ?>
						<?php else: ?>
							<?php echo '<b>'.JText::_('NO_TITLE').'</b>'; ?>
						<?php endif; ?>
					</div>

					<div class="col-md-8 em-bottom-space">
                        <div class="em-contact-request-contact">
                            <div class="em-contact-request-heading"> Demande envoyé à: </div>
                            <div class="em-contact-request-contact-item"><strong>Nom:</strong> <?php echo JFactory::getUser($offer->user_to)->name; ?> </div>
                            <div class="em-contact-request-contact-item"><strong>Email:</strong> <?php echo JFactory::getUser($offer->user_to)->email; ?> </div>
                        </div>
						<?php if ($offer->state == '1') :?>
							<button type="button" class="btn btn-primary" onclick="retry('<?php echo $offer->link_id; ?>')">
								Relancer
							</button>
							<button type="button" class="btn btn-primary" onclick="breakUp('<?php echo $offer->link_id; ?>')">
								Annuler
							</button>
						<?php elseif ($offer->state == '2') :?>
							<button type="button" class="btn btn-primary" onclick="breakUp('<?php echo $offer->link_id; ?>')">
								Couper contact
							</button>
						<?php endif; ?>
						<?php if (!empty($offer->offer_from)) :?>
                            <div class="em-contact-request-linked-offer"> Votre offre liée : </div>
                            <div class="em-contact-request-linked-offer-link"><a href="<?php echo JRoute::_(JURI::base()."les-offres/consultez-les-offres/details/299/".$offer->offer_from->search_engine_page); ?>"><?php echo $offer->offer_from->titre; ?></a></div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</span>
	<?php endif; ?>

	<script>
        function reply(id) {

            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'index.php?option=com_emundus&controller=cifre&task=replybyid',
                data: { id : id },
                beforeSend: function () {
                    jQuery('#'+id).html('<button type="button" class="btn btn-default" disabled> ... </button>');
                },
                success: function(result) {
                    if (result.status) {
                        // When we successfully change the status, we simply dynamically change the button.
                        jQuery('#'+id).html('<button type="button" class="btn btn-danger" onclick="breakUp(id)"> Couper contact </button>');
                    } else {
                        var actionText = document.getElementById('em-action-text-'+id);
                        actionText.classList.remove('hidden');
                        actionText.innerHTML = result.msg;
                    }
                },
                error: function(jqXHR) {
                    console.log(jqXHR.responseText);
                }
            });
        }


        function retry(id) {

            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'index.php?option=com_emundus&controller=cifre&task=retrybyid',
                data: { id : id },
                beforeSend: function () {
                    jQuery('#'+id).html('<button type="button" class="btn btn-default" disabled> ... </button>');
                },
                success: function(result) {
                    if (result.status) {
                        // When we successfully change the status, we simply dynamically change the button.
                        jQuery('#'+id).html('<button type="button" class="btn btn-default" disabled > Méssage envoyé </button>');
                    } else {
                        var actionText = document.getElementById('em-action-text-'+id);
                        actionText.classList.remove('hidden');
                        actionText.innerHTML = result.msg;
                    }
                },
                error: function(jqXHR) {
                    console.log(jqXHR.responseText);
                }
            });
        }


        function breakUp(id) {

            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'index.php?option=com_emundus&controller=cifre&task=breakupbyid',
                data: { id : id },
                success: function(result) {
                    if (result.status) {
                        // Dynamically change the button back to the state of not having a link.
                        jQuery('#'+id).html('<button type="button" class="btn btn-default" disabled> Mise en relation annulée </button>');
                    } else {
                        var actionText = document.getElementById('em-action-text-'.id);
                        actionText.classList.remove('hidden');
                        actionText.innerHTML = result.msg;
                    }
                },
                error: function(jqXHR) {
                    console.log(jqXHR.responseText);
                }
            });
        }

	</script>
</div>
