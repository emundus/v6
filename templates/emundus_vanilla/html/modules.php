<?php
function modChrome_block( $module, &$params, &$attribs ) {
$app	= JFactory::getApplication();
if($module->module=='mod_menu' && ($app->getTemplate(true)->params->get( $module->position) == Null || $app->getTemplate(true)->params->get( $module->position) == 'block' && $app->getTemplate(true)->params->get( $module->position . 'ms') == 'v_menu'))
{
if ($module->showtitle){
echo '<div class="ttr_verticalmenu'.$params->get( 'moduleclass_sfx' ) .'">';
echo '<div style="height:0px;width:0px;overflow:hidden;-webkit-margin-top-collapse: separate;"></div>';
echo '<div class="ttr_verticalmenu_header">';
$heading='h3';
$temp=$app->getTemplate(true)->params->get('sidebar_menu_heading_tag');
if($temp != Null)
$heading = $temp;
echo '<'.$heading.' class="ttr_verticalmenu_heading">';
echo $module->title;
echo '</'.$heading.'>';
}
else { 
echo '<div class="ttr_verticalmenu'.$params->get( 'moduleclass_sfx' ) .'">';
echo '<div style="height:0px;width:0px;overflow:hidden;-webkit-margin-top-collapse: separate;"></div>';
echo '<div class="ttr_verticalmenu_without_header">';
}
echo '</div>';
echo $module->content; 
echo '</div>';
}
else if($module->module=='mod_menu' && ($app->getTemplate(true)->params->get( $module->position) == Null || $app->getTemplate(true)->params->get( $module->position) == 'block' && $app->getTemplate(true)->params->get( $module->position . 'ms') == 'h_menu'))
{
echo $module->content;
}
else if($module->module!='mod_menu' && ($app->getTemplate(true)->params->get( $module->position) == Null || $app->getTemplate(true)->params->get( $module->position) == 'block' )) {
if ($module->position=='left'):
echo '<div class="ttr_sidebar_left_padding">';
elseif ($module->position=='right'):
echo '<div class="ttr_sidebar_right_padding">';
else:
echo '<div class="ttr_block_parent">';
endif;
echo '<div style="height:0px;width:0px;overflow:hidden;-webkit-margin-top-collapse: separate;"></div>';
if ($module->showtitle){
echo '<div class="ttr_block'.$params->get( 'moduleclass_sfx' ) .'">';
echo '<div style="height:0px;width:0px;overflow:hidden;-webkit-margin-top-collapse: separate;"></div>';
echo '<div class="ttr_block_header">';
$heading='h3';
$temp=$app->getTemplate(true)->params->get('heading_tag_block');
if($temp != Null)
$heading=$temp;
echo '<'.$heading. ' class="ttr_block_heading">';
echo $module->title;
echo '</'.$heading.'>';
echo '</div>';
}
else {
echo '<div class="ttr_block'.$params->get( 'moduleclass_sfx' ) .'">';
echo '<div style="height:0px;width:0px;overflow:hidden;-webkit-margin-top-collapse: separate;"></div>';
echo '<div class="ttr_block_without_header">';
echo '</div>';
}
echo '<div class="ttr_block_content">';
echo $module->content; 
echo '</div>';
echo '</div>';
echo '<div style="height:0px;width:0px;overflow:hidden;-webkit-margin-top-collapse: separate;"></div>';
echo '</div>';
}
}
?>
