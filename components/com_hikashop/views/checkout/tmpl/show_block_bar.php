<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_wizardbar">
	<ul>
<?php
	$workflow = $this->checkoutHelper->checkout_workflow;
	foreach($workflow['steps'] as $k => $step) {
		if($step['content'][0]['task'] == 'end' && empty($this->options['display_end']))
			continue;

		$stepClass = ($k == $this->workflow_step) ? 'hikashop_cart_step_current' : ($k < $this->workflow_step ? 'hikashop_cart_step_finished' : '');
		$badgeClass = ($k == $this->workflow_step) ? 'hkbadge-current' : ($k < $this->workflow_step ? 'hkbadge-past' : '');
		if(!empty($step['name'])){
			$key = strtoupper($step['name']);
			$trans = JText::_($key);
			if($trans == $key)
				$name = $step['name'];
			else
				$name = $trans;
		}else{
			$name = JText::_('HIKASHOP_CHECKOUT_'.strtoupper($step['content'][0]['task']));
		}

		if($k < $this->workflow_step) {
			$name = '<a href="'.$this->checkoutHelper->completeLink('&cid='.($k+1).$this->cartIdParam, false, false, false, $this->itemid).'">'.$name.'</a>';
		}
?>
		<li class="<?php echo $stepClass; ?>">
			<span class="hkbadge <?php echo $badgeClass; ?>"><?php echo ($k + 1); ?></span><?php echo $name; ?>
			<span class="hikashop_chevron"></span>
		</li>
<?php
	}
?>
	</ul>
</div>
