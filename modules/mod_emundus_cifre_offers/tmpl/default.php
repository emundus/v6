<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_cifre_offers
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

?>

<div class="em-contact-request-module">
    <?php if (empty($offers->to) &&empty($offers->from)) :?>
    <span class="em-contact-request col-md-12">
            <div class="em-highlight"><?php echo JText::_('MOD_EMUNDUS_CIFRE_NO_OFFERS'); ?></div>
    </span>
    <?php endif; ?>
    <?php if (!empty($offers->to)) :?>
        <span class="em-contact-request col-md-12">
            <div class="em-highlight"><?php echo count($offers->to) > 1 ?JText::_('MOD_EMUNDUS_CIFRE_OFFERS_RECIEVED_OFFERS'):JText::_('MOD_EMUNDUS_CIFRE_OFFERS_RECIEVED_OFFER'); ?></div>
            <?php foreach ($offers->to as $offer) :?>
                <div class="col-md-4" id="<?php echo $offer->link_id; ?>">
                    <div class="em-contact-request-card">
                        <div class="em-bottom-space">
                            <div class="em-contact-request-contact">
                                <div class="em-contact-request-heading">
                                    <?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_RECIEVED_FROM'); ?>
                                    <span class="em-contact-request-name"><strong><?php echo JFactory::getUser($offer->user_from)->name; ?></strong></span>
                                    <span class="em-contact-request-profile"><strong> (<?php echo $offer->profile; ?>)</strong></span> 
                                    <?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_OFFER_NAME'); ?> 
                                    <span class="em-contact-request-offer">
                                        <strong>
                                        <?php if (!empty($offer->titre)) :?>
                                            "<a href="<?php echo JRoute::_(JURI::base()."consultez-les-offres/details/299/".$offer->search_engine_page); ?>"><?php echo $offer->titre; ?></a>"
                                        <?php else: ?>
                                            <?php echo '"'.JText::_('NO_TITLE').'"'; ?>
                                        <?php endif; ?>
                                        </strong>
                                    </span>
                                </div>
                                <div class="em-details-link">
                                <a href="/demande/details/314/<?php echo $offer->link_id;?>?format=pdf"><?php echo JText::_('MOD_EMUNDUS_CIFRE_SEE_DETAILS'); ?></a>
                            </div>
                                <div class="em-chat-link" id="em-chat-link-<?php echo $offer->link_id; ?>">
                                    <a href="/index.php?option=com_emundus&view=messages&chatid=<?php echo $offer->user_from ?>"><?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_TALK_TO'); ?></a>
                                </div>
                                <?php if (!empty($offer->offer_from)) :?>
                                    <div class="em-contact-request-linked-offer"><?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_LINKED_OFFER'); ?></div>
                                    <div class="em-contact-request-linked-offer-link"><a href="<?php echo JRoute::_(JURI::base()."consultez-les-offres/details/299/".$offer->offer_from->search_engine_page); ?>"><?php echo $offer->offer_from->titre; ?></a></div>
                                <?php endif; ?>
                            </div>

                            <div id="em-buttons-<?php echo $offer->link_id; ?>">
                                <?php if ($offer->state == '1') :?>
                                    <button type="button" class="btn btn-primary" onclick="reply('<?php echo $offer->link_id; ?>')">
                                        <?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_REPLY'); ?>
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="breakUp('ignore', '<?php echo $offer->link_id; ?>')">
                                        <?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_IGNORE'); ?>
                                    </button>
                                <?php elseif ($offer->state == '2') :?>
                                    <button type="button" class="btn btn-primary" onclick="breakUp('breakup', '<?php echo $offer->link_id; ?>')">
                                        <?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_BREAKUP'); ?>
                                    </button>

                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <span class="alert alert-danger hidden" id="em-action-text-<?php echo $offer->link_id; ?>"></span>
                </div>
            <?php endforeach; ?>
        </span>
    <?php endif; ?>

    <?php if (!empty($offers->from)) :?>
        <span class="em-contact-request col-md-12">
            <div class="em-highlight"><?php echo count($offers->from) > 1 ? JText::_('MOD_EMUNDUS_CIFRE_OFFERS_SENT_OFFERS') : JText::_('MOD_EMUNDUS_CIFRE_OFFERS_SENT_OFFER'); ?></div>
            <?php foreach ($offers->from as $offer) :?>
                <div class="col-md-4" id="<?php echo $offer->link_id; ?>">
                    <div class="em-contact-request-card">
                        <div class="em-bottom-space">
                            <div class="em-contact-request-heading">
                        </div>

                        <div class="em-bottom-space">
                            <div class="em-contact-request-contact">
                                <div class="em-contact-request-heading">
                                    <?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_SENT_TO'); ?> 
                                    <span class="em-contact-request-name"><strong><?php echo JFactory::getUser($offer->user_to)->name; ?></strong></span>  <span class="em-contact-request-profile"><strong>(<?php echo $offer->profile; ?>)</strong></span>
                                    <?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_OFFER_NAME'); ?>
                                    <span class="em-contact-request-offer">
                                        <strong>
                                        <?php if (!empty($offer->titre)) :?>
                                            <?php echo '"'.$offer->titre.'"'; ?>
                                        <?php else: ?>
                                            <?php echo '"'.JText::_('NO_TITLE').'"'; ?>
                                        <?php endif; ?>
                                        </strong>
                                    </span>
                                </div>
                                <?php if (!empty($offer->offer_from)) :?>
                                    <div class="em-contact-request-linked-offer"><?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_YOUR_LINKED_OFFER'); ?></div>
                                    <div class="em-contact-request-linked-offer-link"><a href="<?php echo JRoute::_(JURI::base()."consultez-les-offres/details/299/".$offer->offer_from->search_engine_page); ?>"><?php echo $offer->offer_from->titre; ?></a></div>
                                <?php endif; ?>
                                <div class="em-chat-link" id="em-chat-link-<?php echo $offer->link_id; ?>">
                                        <a href="/index.php?option=com_emundus&view=messages&chatid=<?php echo $offer->user_from ?>"><?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_TALK_TO'); ?></a>
                                    </div>
                            </div>

                            <div id="em-buttons-<?php echo $offer->link_id; ?>">
                                <?php if ($offer->state == '1') :?>
                                    <button type="button" class="btn btn-primary" onclick="retry('<?php echo $offer->link_id; ?>')">
                                        <?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_RETRY'); ?>
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="breakUp('cancel', '<?php echo $offer->link_id; ?>')">
                                        <?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_CANCEL'); ?>
                                    </button>
                                <?php elseif ($offer->state == '2') :?>
                                    <button type="button" class="btn btn-primary" onclick="breakUp('breakup', '<?php echo $offer->link_id; ?>')">
                                        <?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_BREAKUP'); ?>
                                    </button>

                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
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
                beforeSend: () => {
                    jQuery('#em-buttons-'+id).html('<button type="button" class="btn btn-default" disabled> ... </button>');
                },
                success: result => {
                    if (result.status) {
                        // When we successfully change the status, we simply dynamically change the button.
                        jQuery('#em-buttons-'+id).html('<button type="button" class="btn btn-primary" onclick="breakUp(\'breakup\','+id+')"> <?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_BREAKUP'); ?> </button>');
                    } else {
                        var actionText = document.getElementById('em-action-text-'+id);
                        actionText.classList.remove('hidden');
                        actionText.innerHTML = result.msg;
                    }
                },
                error: jqXHR => {
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
                beforeSend: () => {
                    jQuery('#em-buttons-'+id).html('<button type="button" class="btn btn-default" disabled> ... </button>');
                },
                success: result => {
                    if (result.status) {
                        // When we successfully change the status, we simply dynamically change the button.
                        jQuery('#em-buttons-'+id).html('<button type="button" class="btn btn-default" disabled ><?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_MESSAGE_SENT'); ?></button>');
                    } else {
                        var actionText = document.getElementById('em-action-text-'+id);
                        actionText.classList.remove('hidden');
                        actionText.innerHTML = result.msg;
                    }
                },
                error: jqXHR => {
                    console.log(jqXHR.responseText);
                }
            });
        }


        function breakUp(action, id) {

            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'index.php?option=com_emundus&controller=cifre&task=breakupbyid&action='+action,
                data: { id : id },
                beforeSend: () => {
                    jQuery('#em-buttons-'+id).html('<button type="button" class="btn btn-default" disabled> ... </button>');
                },
                success: result => {
                    if (result.status) {
                        // Dynamically change the button back to the state of not having a link.
                        jQuery('#em-buttons-'+id).html('<button type="button" class="btn btn-default" disabled><?php echo JText::_('MOD_EMUNDUS_CIFRE_OFFERS_BROKEN_UP'); ?></button>');
                    } else {
                        var actionText = document.getElementById('em-action-text-'.id);
                        actionText.classList.remove('hidden');
                        actionText.innerHTML = result.msg;
                    }
                },
                error: jqXHR => {
                    console.log(jqXHR.responseText);
                }
            });
        }

    </script>
</div>
