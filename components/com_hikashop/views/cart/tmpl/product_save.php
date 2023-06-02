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
window.parent.Oby.fireAjax('<?php echo $this->cart->cart_type; ?>.updated', {id: <?php echo $this->cart->cart_id; ?>, type: '<?php echo $this->cart->cart_type; ?>', resp: '', notify: false});
setTimeout(function(){ window.parent.hikashop.closeBox(); }, 300);
</script>
