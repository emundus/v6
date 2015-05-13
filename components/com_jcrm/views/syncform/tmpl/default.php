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
            jQuery('#form-sync').submit(function(event) {
                
            });

            
        });
    });

</script>

<div class="sync-edit front-end-edit">
    <?php if (!empty($this->item->id)): ?>
        <h1>Edit <?php echo $this->item->id; ?></h1>
    <?php else: ?>
        <h1>Add</h1>
    <?php endif; ?>

    <form id="form-sync" action="<?php echo JRoute::_('index.php?option=com_jcrm&task=sync.save'); ?>" method="post" class="form-validate" enctype="multipart/form-data">
                
        <div class="button-div">
            <button type="submit" class="validate"><span><?php echo JText::_('JSUBMIT'); ?></span></button>
            <a href="<?php echo JRoute::_('index.php?option=com_jcrm&task=syncform.cancel'); ?>" title="<?php echo JText::_('JCANCEL'); ?>"><?php echo JText::_('JCANCEL'); ?></a>            
        </div>

        <input type="hidden" name="option" value="com_jcrm" />
        <input type="hidden" name="task" value="syncform.save" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
