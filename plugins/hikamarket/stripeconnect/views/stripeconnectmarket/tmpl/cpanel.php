<?php
/**
 * @package    StripeConnect for Joomla! HikaShop
 * @version    1.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2020 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(empty($this->stripe_client_id))
	return;

?>
<h2><?php echo JText::_('STRIPE_CONNECT'); ?></h2>
<?php
	if(empty($this->stripe_account)) {;
?>
	<a href="<?php echo $this->connect_url; ?>" class="connect-button"><span><?php echo JText::_('CONNECT_WITH_STRIPE'); ?></span></a>
<?php
	} else {
?>
	<p><?php echo JText::_('YOUR_STRIPE_ACCOUNT_IS_LINKED'); ?></p>
<?php
	}

