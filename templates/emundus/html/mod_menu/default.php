<?php
defined('_JEXEC') or die;
$app	= JFactory::getApplication();
$start= (int) $params->get('startLevel');
global $newparams,$justifychk;
$newparams=$module;
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
$class_sfx = htmlspecialchars($params->get('class_sfx'));
$justifychk = false;
?>
<?php if(($app->getTemplate(true)->params->get( $newparams->position) == Null && $newparams->position == 'menu') || ($app->getTemplate(true)->params->get( $newparams->position) == 'block' && $app->getTemplate(true)->params->get( $newparams->position . 'ms') == 'h_menu')):?>
<?php if($module->position != 'menu'){ ?>
<nav class="navbar-default navbar nav-menu-default">
<div class="container-fluid">
<div class="navbar-header">
<button id="nav-expander" data-target=".<?php echo str_replace(' ', '-',$module->position);?>" data-toggle="collapse" class="navbar-toggle" type="button">
<span class="ttr_menu_toggle_button">
<span class="sr-only">
</span>
<span class="icon-bar">
</span>
<span class="icon-bar">
</span>
<span class="icon-bar">
</span>
</span>
<span class="ttr_menu_button_text">
Menu
</span>
</button>
</div>
<div class="menu-center collapse navbar-collapse <?php echo $moduleclass_sfx." "; echo str_replace(' ', '-',$module->position);?>">
<?php } ?>
<ul class="ttr_menu_items nav navbar-nav nav-center<?php  echo $class_sfx; ?> "<?php
$tag = '';
if ($params->get('tag_id')!=NULL) {
$tag = $params->get('tag_id').'';
echo ' id="'.$tag.'"';
}
?>>
<?php
foreach ($list as $i => &$item) :
$class='';
if($item->level==$start){
$class = ' class="ttr_menu_items_parent_link';
if($item->id == $active_id ){
	$class = $class.'_active';
	}
if ($item->type == 'alias') {
$aliasToId = $item->params->get('aliasoptions');
if (count($path) > 0 && $aliasToId == $path[count($path) - 1]) {
$class .= '_active';
}
elseif (in_array($aliasToId, $path)) {
$class .= '_active';
}
}
if($item->deeper){
if($item->type == 'separator')
{
$class = $class.'_arrow dropdown-toggle separator';
}
else
{
$class = $class.'_arrow dropdown-toggle';
}
}
$class=$class.'"';
if($item->id == $active_id){
echo '<li class="ttr_menu_items_parent active dropdown">';
}
else {
echo '<li class="ttr_menu_items_parent dropdown">';
}
}
elseif($item->deeper){
if($item->type == 'separator')
{
$class = ' class="separate subchild dropdown-toggle"';
}
else
{
$class = ' class="subchild dropdown-toggle"';
}
 echo '<li class="dropdown dropdown-submenu">';
}
else{
echo '<li >';
}
switch ($item->type) :
case 'separator':
case 'url':
case 'component':
require JModuleHelper::getLayoutPath('mod_menu', 'default_'.$item->type);
break;
default:
require JModuleHelper::getLayoutPath('mod_menu', 'default_url');
break;
endswitch;
if ($item->deeper) {
if($item->level==$start){
	echo '<hr class="horiz_separator" /><ul role="menu" class="child dropdown-menu">';
	}
else{
echo '<hr class="separator" /><ul role="menu" class="dropdown-menu sub-menu menu-dropdown-styles">';
}
}
elseif ($item->shallower) {
echo '<hr class="separator" /></li>';
echo str_repeat('</ul></li>', $item->level_diff);
	}
else {
if($item->level==$start)
{
echo '<hr class="horiz_separator" /></li>';
}
else
{
echo '<hr class="separator" /></li>';
}
	}
endforeach;
?></ul>
<?php if($module->position != 'menu'){ ?>
</div>
</div>
</nav>
<?php } ?>
<div style="clear: both;"></div>
 <?php elseif(($app->getTemplate(true)->params->get($newparams->position) == Null && ($newparams->position == 'left' || $newparams->position == 'right')) || ($app->getTemplate(true)->params->get( $newparams->position) == 'block' && $app->getTemplate(true)->params->get( $newparams->position . 'ms') == 'v_menu')):?>
<div class="ttr_verticalmenu_content">
<ul class="ttr_vmenu_items nav nav-pills nav-stacked<?php echo $moduleclass_sfx; echo " ".$class_sfx; ?> "<?php
$tag = '';
if ($params->get('tag_id')!=NULL) {
$tag = $params->get('tag_id').'';
echo ' id="'.$tag.'"';
}
?>>
<?php
foreach ($list as $i => &$item) :
$class='';
if($item->level==$start){
$class = ' class="ttr_vmenu_items_parent_link';
if($item->id == $active_id ){
	$class = $class.'_active';
	}
if ($item->type == 'alias') {
$aliasToId = $item->params->get('aliasoptions');
if (count($path) > 0 && $aliasToId == $path[count($path) - 1]) {
$class .= '_active';
}
elseif (in_array($aliasToId, $path)) {
$class .= '_active';
}
}
if($item->deeper){
if($item->type == 'separator')
{
$class = $class.'_arrow dropdown-toggle separator';
}
else
{
$class = $class.'_arrow dropdown-toggle';
}
}
$class=$class.'"';
if($item->id == $active_id){
echo '<li class="ttr_vmenu_items_parent active dropdown">';
}
else {
echo '<li class="ttr_vmenu_items_parent dropdown">';
}
}
elseif($item->deeper){
if($item->type == 'separator')
{
$class = ' class="separate subchild dropdown-toggle"';
}
else
{
$class = ' class="subchild dropdown-toggle"';
}
 echo '<li class="dropdown dropdown-submenu">';
}
else{
echo '<li >';
}
switch ($item->type) :
case 'separator':
case 'url':
case 'component':
require JModuleHelper::getLayoutPath('mod_menu', 'default_'.$item->type);
break;
default:
require JModuleHelper::getLayoutPath('mod_menu', 'default_url');
break;
endswitch;
if ($item->deeper) {
if($item->level==$start){
echo '<hr class="horiz_separator" /><ul role="menu"id="dropdown-menu" class="child dropdown-menu menu-dropdown-styles">';
	}
else{
echo '<hr class="separator" /><ul role="menu"id="dropdown-menu" class="dropdown-menu sub-menu menu-dropdown-styles" >';
}
}
elseif ($item->shallower) {
echo '<hr class="separator" /></li>';
echo str_repeat('</ul></li>', $item->level_diff);
	}
else {
if($item->level==$start){
	echo '<hr class="horiz_separator" /></li>';
	}
else{
echo '<hr class="separator" /></li>';
}
	}
endforeach;
?></ul>
</div>
<?php elseif( $app->getTemplate(true)->params->get( $newparams->position) == 'none' && $app->getTemplate(true)->params->get( $newparams->position . 'ms') == 'h_menu'):?>
<ul>
<?php
foreach ($list as $i => &$item) :
echo '<li style="display:inline;  margin:5px;list-style:none;">';
switch ($item->type) :
case 'separator':
case 'url':
case 'component':
require JModuleHelper::getLayoutPath('mod_menu', 'default_'.$item->type);
break;
default:
require JModuleHelper::getLayoutPath('mod_menu', 'default_url');
break;
endswitch;
if ($item->deeper) {
echo '<ul style="display:inline;margin:5px;">';
}
elseif ($item->shallower) {
echo '</li>';
echo str_repeat('</ul></li>', $item->level_diff);
}
else {
echo '</li>';
}
endforeach;
?></ul>
<?php else:?>
<ul>
<?php foreach ($list as $i => &$item) :
echo '<li style="list-style:none;">';
switch ($item->type) :
case 'separator':
case 'url':
case 'component':
require JModuleHelper::getLayoutPath('mod_menu', 'default_'.$item->type);
break;
default:
require JModuleHelper::getLayoutPath('mod_menu', 'default_url');
break;
endswitch;
if ($item->deeper) {
echo '<ul >';
}
elseif ($item->shallower) {
echo '</li>';
echo str_repeat('</ul></li>', $item->level_diff);
}
else {
echo '</li>';
}
endforeach;
?></ul>
<?php endif;?>
