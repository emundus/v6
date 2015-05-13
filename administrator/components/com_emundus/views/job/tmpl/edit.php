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

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_emundus/assets/css/emundus.css');
?>
<script type="text/javascript">
    function getScript(url,success) {
        var script = document.createElement('script');
        script.src = url;
        var head = document.getElementsByTagName('head')[0],
        done = false;
        // Attach handlers for all browsers
        script.onload = script.onreadystatechange = function() {
            if (!done && (!this.readyState
                || this.readyState == 'loaded'
                || this.readyState == 'complete')) {
                done = true;
                success();
                script.onload = script.onreadystatechange = null;
                head.removeChild(script);
            }
        };
        head.appendChild(script);
    }
    getScript('//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js',function() {
        js = jQuery.noConflict();
        js(document).ready(function(){
            
					js('input:hidden.etablissement').each(function(){
						var name = js(this).attr('name');
						if(name.indexOf('etablissementhidden')){
							js('#jform_etablissement option[value="'+jQuery(this).val()+'"]').attr('selected',true);
						}
					});

            Joomla.submitbutton = function(task)
            {
                if (task == 'job.cancel') {
                    Joomla.submitform(task, document.getElementById('job-form'));
                }
                else{
                    
                    if (task != 'job.cancel' && document.formvalidator.isValid(document.id('job-form'))) {
                        
                        Joomla.submitform(task, document.getElementById('job-form'));
                    }
                    else {
                        alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
                    }
                }
            }
        });
    });
</script>

<form action="<?php echo JRoute::_('index.php?option=com_emundus&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="job-form" class="form-validate">
    <div class="width-60 fltlft">
        <fieldset class="adminform">
            <legend><?php echo JText::_('COM_EMUNDUS_LEGEND_JOB'); ?></legend>
            <ul class="adminformlist">

                				<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />

				<?php echo $this->form->getInput('date_time'); ?>				<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
				<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />

				<?php if(empty($this->item->user)){ ?>
					<input type="hidden" name="jform[user]" value="<?php echo JFactory::getUser()->id; ?>" />

				<?php } 
				else{ ?>
					<input type="hidden" name="jform[user]" value="<?php echo $this->item->user; ?>" />

				<?php } ?>				<li><?php echo $this->form->getLabel('etablissement'); ?>
				<?php echo $this->form->getInput('etablissement'); ?></li>

			<?php
				foreach((array)$this->item->etablissement as $value): 
					if(!is_array($value)):
						echo '<input type="hidden" class="etablissement" name="jform[etablissementhidden]['.$value.']" value="'.$value.'" />';
					endif;
				endforeach;
			?>				<li><?php echo $this->form->getLabel('service'); ?>
				<?php echo $this->form->getInput('service'); ?></li>
				<li><?php echo $this->form->getLabel('intitule_poste'); ?>
				<?php echo $this->form->getInput('intitule_poste'); ?></li>
				<li><?php echo $this->form->getLabel('domaine'); ?>
				<?php echo $this->form->getInput('domaine'); ?></li>
				<li><?php echo $this->form->getLabel('nb_poste'); ?>
				<?php echo $this->form->getInput('nb_poste'); ?></li>


            </ul>
        </fieldset>
    </div>

    <div class="clr"></div>

<?php if (JFactory::getUser()->authorise('core.admin','emundus')): ?>
	<div class="width-100 fltlft">
		<?php echo JHtml::_('sliders.start', 'permissions-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
		<?php echo JHtml::_('sliders.panel', JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'), 'access-rules'); ?>
		<fieldset class="panelform">
			<?php echo $this->form->getLabel('rules'); ?>
			<?php echo $this->form->getInput('rules'); ?>
		</fieldset>
		<?php echo JHtml::_('sliders.end'); ?>
	</div>
<?php endif; ?>

    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
    <div class="clr"></div>

    <style type="text/css">
        /* Temporary fix for drifting editor fields */
        .adminformlist li {
            clear: both;
        }
    </style>
</form>