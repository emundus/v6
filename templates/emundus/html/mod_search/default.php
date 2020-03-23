<?php
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php');?>" method="post">
<div class="search<?php echo $params->get('moduleclass_sfx') ?>">
<?php
$output = '<input name="searchword" id="mod-search-searchword" maxlength="'.$maxlength.'" class="boxcolor'.$moduleclass_sfx.'" type="text" size="'.$width.'" value="'.$text.'"  onblur="if (this.value==\'\') this.value=\''.$text.'\';" onfocus="if (this.value==\''.$text.'\') this.value=\'\';" />';
$button_html="";
if ($button) :
if ($imagebutton) :
$button_html = '<input type="image" value="'.$button_text.'" class="btn btn-default '.$moduleclass_sfx.'" src="'.$img.'" onclick="this.form.searchword.focus();"/>';
else :
$button_html = '<input type="submit" value="'.$button_text.'" class="btn btn-default '.$moduleclass_sfx.'" onclick="this.form.searchword.focus();"/>';
endif;
endif;
switch ($button_pos) :
case 'top' :
$button_html = $button_html.'<br>';
$output = $button_html.$output;
break;
case 'bottom':
$button_html = '<br>'.$button_html;
$output = $output.$button_html;
break;
case 'right' :
$output = $output.$button_html;
break;
case 'left' :
default :
$output = $button_html.$output;
break;
endswitch;
echo '<div>';
echo $output;
echo '<div style="clear:both;">';
echo '</div>';
echo '</div>';
?>
<input type="hidden" name="task" value="search"/>
<input type="hidden" name="option" value="com_search" />
<input type="hidden" name="Itemid" value="<?php echo $mitemid; ?>"/>
</div>
</form>
