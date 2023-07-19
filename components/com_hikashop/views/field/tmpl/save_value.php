<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><script>
    if(window.parent.hikashop.currentFieldParent) {
<?php
switch($this->field->field_type) {
    case 'radio':
    case 'checkbox':
?>
        var sibling = window.parent.hikashop.currentFieldParent.querySelector('input');
        var input = document.createElement('input');
        input.type = "<?php echo $this->field->field_type; ?>";
        input.name = sibling.name;
        input.value = '<?php echo str_replace("'","\'",$this->new->value); ?>';
<?php
        if($this->new->disabled) {
?>
        input.disabled = true;
<?php
        } else {
            if($this->field->field_type == 'radio') {
?>
        var siblings = window.parent.hikashop.currentFieldParent.querySelectorAll('input');
        for(var i = 0; i < siblings.length; i ++) {
            siblings[i].checked = false;
        }
<?php
            }
?>
        input.checked = true;
<?php
        }
?>
        var span = document.createElement('span');
        span.appendChild(document.createTextNode('<?php echo str_replace("'","\'",$this->new->title); ?>'));
        var label = document.createElement('label');
        label.appendChild(input);
        label.appendChild(span);
        label.classList.add('hk<?php echo $this->field->field_type.(empty($this->field->field_options['inline']) ? '' : '-inline'); ?>');
        window.parent.hikashop.currentFieldParent.insertBefore(label, window.parent.hikashop.currentFieldParent.querySelector('.field_add_button'));
<?php
        break;
    case 'singledropdown':
    case 'multipledropdown':
        ?>
        var el = window.parent.hikashop.currentFieldParent.querySelector('select');
        el.options[el.options.length] = new Option('<?php echo str_replace("'","\'",$this->new->title); ?>', '<?php echo str_replace("'","\'",$this->new->value); ?>', <?php echo ($this->new->disabled ? 'false' : 'true'); ?>, <?php echo ($this->new->disabled ? 'false' : 'true'); ?>);
<?php
        if($this->new->disabled) {
?>
            el.options[el.options.length].disabled = true;
<?php
        }
?>
        if(window.parent.jQuery && typeof(jQuery().chosen) == "function") {
            window.parent.jQuery("#"+el.id).chosen('destroy');
            window.parent.jQuery("#"+el.id).chosen({disable_search_threshold:10, search_contains: true});  
        }
<?php
        break;
}
?>
    }
    window.parent.hikashop.closeBox();
</script>
