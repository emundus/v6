<?php
defined('_JEXEC') or die('Restricted access');
$app = JFactory::getApplication();
$template_path = JPATH_BASE . "/templates/" . $app->getTemplate().'/params.ini';
$ini_array = parse_ini_file($template_path);
$saveoption = $ini_array['saveoption'];
if ($this->layoutName!='default') {
?>
<form method="post" id="userForm" name="enterCouponCode" action="<?php echo JRoute::_('index.php'); ?>">
<?php } ?>
	<div class="coupon-inputbox" >
    <input type="text" name="coupon_code" size="20" maxlength="50" class="form-control" placeholder="<?php echo $this->coupon_text ?>" value="<?php //echo $this->coupon_text; ?>" onblur="if(this.value=='') this.value='<?php echo $this->coupon_text; ?>';" onfocus="if(this.value=='<?php echo $this->coupon_text; ?>') this.value='';" />
    </div>
    <div class="coupon-button">
    <input class="<?php echo $saveoption; ?>" type="submit" name="setcoupon" title="<?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?>" value="<?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?>"/>
    </div>
<?php
if ($this->layoutName!='default') {
?>
    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="view" value="cart" />
    <input type="hidden" name="task" value="setcoupon" />
    <input type="hidden" name="controller" value="cart" />
</form>
<?php } ?>