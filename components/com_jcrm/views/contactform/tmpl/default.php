<?php
/**
 * @version     1.0.0
 * @package     com_jcrm
 * @copyright   Copyright (C) 2014. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      Décision Publique <dev@emundus.fr> - http://www.emundus.fr
 */
// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

//Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_jcrm', JPATH_ADMINISTRATOR);
$doc = JFactory::getDocument();
$doc->addStyleSheet(JUri::base() . '/components/com_jcrm/assets/css/form.css');
$doc->addScript(JUri::base() . '/components/com_jcrm/assets/js/form.js');
?>

<script type="text/javascript">

    getScript('//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js', function() {
        jQuery(document).ready(function() {
            jQuery('#form-contact').submit(function(event) {});
        });
    });

</script>

<div class="contact-edit front-end-edit">
    <?php if (!empty($this->item->id)): ?>
        <h1>Edit <?= $this->item->id; ?></h1>
    <?php else: ?>
        <h1>Add</h1>
    <?php endif; ?>

    <form id="form-contact" action="<?= JRoute::_('index.php?option=com_jcrm&task=contact.save'); ?>" method="post" class="form-validate" enctype="multipart/form-data">
        <input type="hidden" name="jform[id]" value="<?= $this->item->id; ?>" />
		<input type="hidden" name="jform[ordering]" value="<?= $this->item->ordering; ?>" />
        <input type="hidden" name="jform[state]" value="<?= $this->item->state; ?>" />
        <input type="hidden" name="jform[checked_out]" value="<?= $this->item->checked_out; ?>" />
        <input type="hidden" name="jform[checked_out_time]" value="<?= $this->item->checked_out_time; ?>" />

        <?php if (empty($this->item->created_by)) { ?>
            <input type="hidden" name="jform[created_by]" value="<?= JFactory::getUser()->id; ?>" />

        <?php } else { ?>
            <input type="hidden" name="jform[created_by]" value="<?= $this->item->created_by; ?>" />
        <?php } ?>
        <li><?= $this->form->getLabel('last_name'); ?><?= $this->form->getInput('last_name'); ?></li>
        <li><?= $this->form->getLabel('first_name'); ?><?= $this->form->getInput('first_name'); ?></li>
        <li><?= $this->form->getLabel('organisation'); ?><?= $this->form->getInput('organisation'); ?></li>
        <li><?= $this->form->getLabel('email'); ?><?= $this->form->getInput('email'); ?></li>
        <li><?= $this->form->getLabel('phone'); ?><?= $this->form->getInput('phone'); ?></li>
        <li><?= $this->form->getLabel('jcard'); ?><?= $this->form->getInput('jcard'); ?></li>

        <div class="width-100 fltlft" <?php if (!JFactory::getUser()->authorise('core.admin','jcrm')): ?> style="display:none;" <?php endif; ?> >
            <?= JHtml::_('sliders.start', 'permissions-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
            <?= JHtml::_('sliders.panel', JText::_('ACL Configuration'), 'access-rules'); ?>
            <fieldset class="panelform">
                <?= $this->form->getLabel('rules'); ?>
                <?= $this->form->getInput('rules'); ?>
            </fieldset>
            <?= JHtml::_('sliders.end'); ?>
        </div>
        <?php if (!JFactory::getUser()->authorise('core.admin','jcrm')): ?>
                <script type="text/javascript">
                    jQuery('#rules select').each(function(){
                       var option_selected = jQuery(this).find(':selected');
                       var input = jQuery('<input>');
                       input.attr('type', 'hidden');
                       input.attr('name', jQuery(this).attr('name'));
                       input.attr('value', option_selected.val());
                       jQuery('form-contact').append(input);
                    });
                </script>
        <?php endif; ?>
        <div class="button-div">
            <button type="submit" class="validate"><span><?= JText::_('JSUBMIT'); ?></span></button>
            <a href="<?= JRoute::_('index.php?option=com_jcrm&task=contactform.cancel'); ?>" title="<?= JText::_('JCANCEL'); ?>"><?= JText::_('JCANCEL'); ?></a>
        </div>

        <input type="hidden" name="option" value="com_jcrm" />
        <input type="hidden" name="task" value="contactform.save" />
        <?= JHtml::_('form.token'); ?>
    </form>
</div>
