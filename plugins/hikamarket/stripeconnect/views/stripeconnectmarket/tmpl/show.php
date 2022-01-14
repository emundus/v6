<?php
/**
 * @package    StripeConnect for Joomla! HikaShop
 * @version    1.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2020 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><h1><?php echo JText::_('HIKAM_STRIPECONNECT_TITLE'); ?></h1>
<form action="<?php echo hikamarket::completeLink('stripeconnect');?>" method="post" name="hikamarket_form" id="hikamarket_stripeconnect_form">

	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>"/>
	<input type="hidden" name="task" value="show"/>
	<input type="hidden" name="ctrl" value="stripeconnect"/>
	<?php echo JHTML::_('form.token'); ?>
</form>
