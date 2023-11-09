<?php
/**
 * @version     1.0.0
 * @package     com_emundus
 * @copyright   Copyright (C) 2015. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      emundus <dev@emundus.fr> - http://www.emundus.fr
 */
// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');

JHtml::_('behavior.formvalidation');

//Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_emundus', JPATH_ADMINISTRATOR);
$doc = JFactory::getDocument();
$doc->addStyleSheet('components/com_emundus/assets/css/form.css');
$doc->addScript('components/com_emundus/assets/js/form.js');
?>


<div class="job-edit front-end-edit">
	<?php if (!empty($this->item->id)): ?>
        <h1>Edit <?php echo $this->item->id; ?></h1>
	<?php else: ?>
        <h1>Add</h1>
	<?php endif; ?>

    <form id="form-job" action="<?php echo JRoute::_('index.php?option=com_emundus&task=job.save'); ?>" method="post"
          class="form-validate" enctype="multipart/form-data">
        <input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>"/>

		<?php echo $this->form->getInput('date_time'); ?> <input type="hidden" name="jform[ordering]"
                                                                 value="<?php echo $this->item->ordering; ?>"/>
        <input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>"/>

		<?php if (empty($this->item->user)) { ?>
            <input type="hidden" name="jform[user]" value="<?php echo JFactory::getUser()->id; ?>"/>

		<?php }
		else { ?>
            <input type="hidden" name="jform[user]" value="<?php echo $this->item->user; ?>"/>

		<?php } ?>
        <li><?php echo $this->form->getLabel('etablissement'); ?>
			<?php echo $this->form->getInput('etablissement'); ?></li>

		<?php
		foreach ((array) $this->item->etablissement as $value):
			if (!is_array($value)):
				echo '<input type="hidden" class="etablissement" name="jform[etablissementhidden][' . $value . ']" value="' . $value . '" />';
			endif;
		endforeach;
		?>
        <script type="text/javascript">
            window.onload = function () {
                jQuery('input:hidden.etablissement').each(function () {
                    var name = jQuery(this).attr('name');
                    if (name.indexOf('etablissementhidden')) {
                        jQuery('#jform_etablissement option[value="' + jQuery(this).val() + '"]').attr('selected', true);
                    }
                });
            }
        </script>
        <li><?php echo $this->form->getLabel('service'); ?>
			<?php echo $this->form->getInput('service'); ?></li>
        <li><?php echo $this->form->getLabel('intitule_poste'); ?>
			<?php echo $this->form->getInput('intitule_poste'); ?></li>
        <li><?php echo $this->form->getLabel('domaine'); ?>
			<?php echo $this->form->getInput('domaine'); ?></li>
        <li><?php echo $this->form->getLabel('nb_poste'); ?>
			<?php echo $this->form->getInput('nb_poste'); ?></li>
        <div class="width-100 fltlft" <?php if (!JFactory::getUser()->authorise('core.admin', 'emundus')): ?> style="display:none;" <?php endif; ?> >
			<?php echo JHtml::_('sliders.start', 'permissions-sliders-' . $this->item->id, array('useCookie' => 1)); ?>
			<?php echo JHtml::_('sliders.panel', JText::_('ACL Configuration'), 'access-rules'); ?>
            <fieldset class="panelform">
				<?php echo $this->form->getLabel('rules'); ?>
				<?php echo $this->form->getInput('rules'); ?>
            </fieldset>
			<?php echo JHtml::_('sliders.end'); ?>
        </div>
		<?php if (!JFactory::getUser()->authorise('core.admin', 'emundus')): ?>
            <script type="text/javascript">
                jQuery('#rules select').each(function () {
                    var option_selected = jQuery(this).find(':selected');
                    var input = jQuery('<input>');
                    input.attr('type', 'hidden');
                    input.attr('name', jQuery(this).attr('name'));
                    input.attr('value', option_selected.val());
                    jQuery('form-job').append(input);
                });
            </script>
		<?php endif; ?>
        <div class="button-div">
            <button type="submit" class="validate"><span><?php echo JText::_('JSUBMIT'); ?></span></button>
            <a href="<?php echo JRoute::_('index.php?option=com_emundus&task=jobform.cancel'); ?>"
               title="<?php echo JText::_('JCANCEL'); ?>"><?php echo JText::_('JCANCEL'); ?></a>
        </div>

        <input type="hidden" name="option" value="com_emundus"/>
        <input type="hidden" name="task" value="jobform.save"/>
		<?php echo JHtml::_('form.token'); ?>
    </form>
</div>
