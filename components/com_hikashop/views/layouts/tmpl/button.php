<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$button_type = $this->config->get('action_button_type', 'button');
$attributes =  $this->params->get('attributes');

switch($button_type) {
	case 'a':
?>
	<a <?php echo $this->params->get('attributes'); ?> rel="nofollow" href="<?php echo $this->params->get('fallback_url'); ?>"><span><?php echo $this->params->get('content'); ?></span></a>
<?php
		break;
	case 'button':
	default:
		if(strpos($attributes,'onclick') === false) {
			$attributes .= ' onclick="window.location=this.getAttribute(\'data-href\')"';
		}
?>
	<button type="button" <?php echo $attributes; ?> data-href="<?php echo $this->params->get('fallback_url'); ?>"><span><?php echo $this->params->get('content'); ?></span></button>
<?php
		break;
}
?>
