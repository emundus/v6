<?php 
 defined ('_JEXEC') or die('Restricted access'); 
$app = JFactory::getApplication();
$template_path = JPATH_BASE . "/templates/" . $app->getTemplate().'/params.ini';
$ini_array = parse_ini_file($template_path);
if($_REQUEST['view'] == 'virtuemart' || $_REQUEST['view'] == 'category')
{
	$addtocartbutton = $ini_array['addtocartbuttonindex'];
}
else
{
	$addtocartbutton = $ini_array['addtocartbutton'];
}
 if($viewData['orderable']) { 
echo '<input type="submit" name="addtocart" class="ttr_prodes_Button '.$addtocartbutton.'" value ="'.vmText::_( 'COM_VIRTUEMART_CART_ADD_TO' ).'" title="'.vmText::_( 'COM_VIRTUEMART_CART_ADD_TO' ).'" />';
 } 
 else { 
echo '<span name="addtocart" class="addtocart-button-disabled" title="'.vmText::_( 'COM_VIRTUEMART_ADDTOCART_CHOOSE_VARIANT' ).'">'.vmText::_( 'COM_VIRTUEMART_ADDTOCART_CHOOSE_VARIANT' ).'</span>';
 }
 ?>
