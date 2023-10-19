<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php echo JText::sprintf('NEW_COMMENT_NOTIFICATION_SUBJECT',HIKASHOP_LIVE);?><br/>

<?php
if(isset($data->result->vote_type) && $data->result->vote_type == 'vendor'){
	echo JText::_('COMMENT_ITEM_NAME').": ".$data->type->vendor_name;
}else{
	echo JText::_('COMMENT_ITEM_NAME').": ".$data->type->product_name;
}
?>
	<br/>
<?php echo JText::_('HIKA_USERNAME').": ".$data->result->username_comment; ?>
	<br/>
<?php echo JText::_('HIKA_EMAIL').": ".$data->result->email_comment; ?>
	<br/><br/>
<?php echo JText::_('COMMENT_CONTENT').": ".$data->result->comment; ?>
	<br/><br/>
<?php echo JText::_('SEE_COMMENT').": "; ?>
	<br/>
	<a href="<?php echo JRoute::_('administrator/index.php?option=com_hikashop&ctrl=vote&task=edit&cid[]='.$data->result->vote_id,false,true);?>"><?php echo JRoute::_('administrator/index.php?option=com_hikashop&ctrl=vote&task=edit&cid[]='.$data->result->vote_id,false,true);?></a>
